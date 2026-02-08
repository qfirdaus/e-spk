<?php
// controllers/ProjectMonitoringController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

class ProjectMonitoringController {
    public string $lang = 'ms';
    public array $profile = [];
    public array $stats = [
        'total' => 0,
        'on_track' => 0,
        'delayed' => 0,
        'critical' => 0
    ];
    public array $projects = [];
    private PDO $pdo;

    public function __construct() {
        $this->lang = $_SESSION['lang'] ?? 'ms';
        $this->pdo = Database::getInstance('mysql')->getConnection();
        
        $userModel = new User($this->pdo);
        $this->profile = $userModel->getProfile($_SESSION['f_stafID'] ?? '');
    }

    // Get List of Teras for Filter
    public function getActiveTerasList(): array {
        $sql = "SELECT DISTINCT t.f_kodTeras, t.f_namaTeras
                FROM tbl_monitoring_project p
                JOIN tbl_monitoring_teras t ON p.f_terasID = t.f_terasID
                WHERE p.f_status = 1
                ORDER BY t.f_kodTeras ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Load Dashboard Data with optional Filter
    public function loadDashboardData(?string $filterTeras = null): void {
        // 1. Fetch Active Projects (Filtered)
        $sql = "
            SELECT 
                p.f_projectID, p.f_projectName, p.f_status, p.f_endDate,
                t.f_kodTeras,
                u.f_nama as ownerName,
                (
                    SELECT COUNT(*) FROM tbl_monitoring_aktiviti a 
                    WHERE a.f_projectID = p.f_projectID AND a.f_status = 1
                ) as activityCount
            FROM tbl_monitoring_project p
            JOIN tbl_monitoring_teras t ON p.f_terasID = t.f_terasID
            LEFT JOIN tbl_m_user u ON p.f_ownerStafID = u.f_stafID
            WHERE p.f_status = 1
        ";

        $params = [];
        if (!empty($filterTeras)) {
            $sql .= " AND t.f_kodTeras = ? ";
            $params[] = $filterTeras;
        }
        
        $sql .= " ORDER BY p.f_updatedt DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // 2. Calculate Progress & Health for each project
        foreach ($projects as &$p) {
            $calc = $this->calculateProjectProgress((int)$p['f_projectID'], $p['f_endDate'] ?? null);
            $p['progress'] = $calc['percent'];
            $p['status_label'] = $calc['label']; // On Track, Delayed, Critical
            $p['status_color'] = $calc['color']; // success, warning, danger
            
            // Calculate Countdown
            $p['countdown'] = $this->calculateCountdown($p['f_endDate'] ?? null, $p['progress']);
            
            // Stats Aggregation
            $this->stats['total']++;
            if ($calc['code'] === 'critical') $this->stats['critical']++;
            elseif ($calc['code'] === 'delayed') $this->stats['delayed']++;
            else $this->stats['on_track']++;
        }
        
        $this->projects = $projects;
    }

    // Calculate details for a specific project (Matrix View)
    public function getProjectMatrix(int $projectID, string $viewMode = 'monthly'): ?array {
        // Get Header
        $sql = "
            SELECT p.*, t.f_kodTeras, t.f_namaTeras, u.f_nama as ownerName
            FROM tbl_monitoring_project p
            JOIN tbl_monitoring_teras t ON p.f_terasID = t.f_terasID
            LEFT JOIN tbl_m_user u ON p.f_ownerStafID = u.f_stafID
            WHERE p.f_projectID = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectID]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) return null;
        
        // Get Activities
        $sqlAct = "SELECT * FROM tbl_monitoring_aktiviti WHERE f_projectID = ? AND f_status = 1 ORDER BY f_aktivitiID ASC";
        $stmtAct = $this->pdo->prepare($sqlAct);
        $stmtAct->execute([$projectID]);
        $activities = $stmtAct->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Fetch All Reports (Optimized)
        $sqlRep = "
            SELECT r.* 
            FROM tbl_monitoring_laporan r
            JOIN tbl_monitoring_aktiviti a ON r.f_aktivitiID = a.f_aktivitiID
            WHERE a.f_projectID = ?
            ORDER BY r.f_tahun ASC, r.f_bulan ASC
        ";
        $stmtRep = $this->pdo->prepare($sqlRep);
        $stmtRep->execute([$projectID]);
        $allReports = $stmtRep->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Generate Date Columns
        $start = new DateTime($project['f_startDate']);
        $end = new DateTime($project['f_endDate']);
        // Extend end to end of year to look nice? No, stick to project duration.
        // Actually, for quarterly, we might need to align to Q1/Q2/Q3/Q4.
        
        $dateCols = [];
        $curr = clone $start;
        // Align start to beginning of month
        $curr->modify('first day of this month');

        if ($viewMode === 'quarterly') {
             // Align to Start of Quarter (e.g. if Feb, go back to Jan)
             //$currMonth = (int)$curr->format('n');
             // ... logic to align to Q start ...
             // simpler: Just iterate months and group?
             // Let's iterate by 3 months.
             
             while ($curr <= $end) {
                 $y = $curr->format('Y');
                 $m = (int)$curr->format('n');
                 $q = ceil($m / 3);
                 
                 $label = "Q{$q} {$y}";
                 $key = "Q{$q}-{$y}";
                 
                 // Avoid duplicate quarters (e.g. if project starts late in Q1)
                 if (empty($dateCols) || end($dateCols)['key'] !== $key) {
                     $dateCols[] = [
                         'label' => $label,
                         'key' => $key,
                         'type' => 'quarter',
                         'y' => $y,
                         'q' => $q,
                         // Ranges for finding reports
                         'months' => [($q-1)*3 + 1, ($q-1)*3 + 2, ($q-1)*3 + 3]
                     ];
                 }
                 $curr->modify('+1 month');
             }
        } else {
            // Monthly
            while ($curr <= $end) {
                $dateCols[] = [
                    'label' => $curr->format('M Y'),
                    'key' => $curr->format('n-Y'),
                    'type' => 'month',
                    'm' => $curr->format('n'),
                    'y' => $curr->format('Y')
                ];
                $curr->modify('+1 month');
            }
        }

        // Process Data: Link report to activity and Map by View Key
        foreach ($activities as &$act) {
            $act['history'] = []; // Key => Percent
            
            // Filter reports for this activity
            $actReports = array_filter($allReports, function($r) use ($act) {
                return $r['f_aktivitiID'] == $act['f_aktivitiID'];
            });
            
            // Assign back for View usage (e.g. Last Note)
            $act['reports'] = $actReports;

            // Populate History based on View Mode
            foreach ($dateCols as $col) {
                $val = '-';
                if ($viewMode === 'monthly') {
                    // Find specific month
                    foreach ($actReports as $r) {
                        if ($r['f_bulan'] == $col['m'] && $r['f_tahun'] == $col['y']) {
                            $val = $r['f_percentComplete'];
                            break;
                        }
                    }
                } else {
                    // Quarterly: Find LATEST report in that quarter
                    $latestRep = null;
                    foreach ($actReports as $r) {
                        if ($r['f_tahun'] == $col['y'] && in_array($r['f_bulan'], $col['months'])) {
                            // Since reports are sorted by date ASC, subsequent hits overwrite previous (simulating identifying latest)
                            $latestRep = $r; 
                        }
                    }
                    if ($latestRep) $val = $latestRep['f_percentComplete'];
                }
                $act['history'][$col['key']] = $val;
            }
        }

        // Ensure at least one column
        if (empty($dateCols)) {
             $dateCols[] = ['label' => 'No Timeline', 'key' => 'none'];
        }

        return [
            'project' => $project,
            'activities' => $activities,
            'columns' => $dateCols
        ];
    }

    // Helper: Logic for Health Calculation
    public function calculateProjectProgress(int $projectID, ?string $endDate): array {
        // Fetch all activities and their LATEST progress
        $sql = "
            SELECT a.f_weightage, 
                (
                    SELECT f_percentComplete 
                    FROM tbl_monitoring_laporan l 
                    WHERE l.f_aktivitiID = a.f_aktivitiID 
                    ORDER BY f_tahun DESC, f_bulan DESC LIMIT 1
                ) as current_percent
            FROM tbl_monitoring_aktiviti a
            WHERE a.f_projectID = ? AND a.f_status = 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectID]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalScore = 0;
        foreach ($rows as $r) {
            $weight = (float)$r['f_weightage'];
            $pct = (float)($r['current_percent'] ?? 0);
            $totalScore += ($weight * ($pct / 100));
        }
        
        // Round to 1 decimal
        $totalScore = round($totalScore, 1);
        
        // If project is complete
        if ($totalScore >= 100) {
            return ['percent' => 100, 'label' => __('status_completed') ?: 'Selesai', 'color' => 'success', 'code' => 'completed'];
        }
        
        // Get project start and end dates
        $sqlDates = "SELECT f_startDate, f_endDate FROM tbl_monitoring_project WHERE f_projectID = ?";
        $stmtDates = $this->pdo->prepare($sqlDates);
        $stmtDates->execute([$projectID]);
        $dates = $stmtDates->fetch(PDO::FETCH_ASSOC);
        
        $expectedProgress = 0;
        $now = time();
        
        if ($dates && $dates['f_startDate'] && $dates['f_endDate']) {
            $start = strtotime($dates['f_startDate']);
            $end = strtotime($dates['f_endDate']);
            
            // STRICT CHECK: If past due date and not 100%, it's expired/late
            if ($now > $end && $totalScore < 100) {
                return ['percent' => $totalScore, 'label' => __('status_expired') ?: 'Lewat (Tamat Tempoh)', 'color' => 'danger', 'code' => 'delayed'];
            }
            
            // Calculate expected progress based on linear timeline
            if ($now < $start) {
                $expectedProgress = 0;
            } elseif ($now > $end) {
                $expectedProgress = 100;
            } else {
                $totalDays = ($end - $start) / 86400;
                $daysPassed = ($now - $start) / 86400;
                if ($totalDays > 0) {
                    $expectedProgress = ($daysPassed / $totalDays) * 100;
                }
            }
        } else {
            // Fallback: if no dates, use old logic based on percentage only
            if ($totalScore < 40) {
                return ['percent' => $totalScore, 'label' => __('status_critical') ?: 'Berisiko Tinggi', 'color' => 'danger', 'code' => 'critical'];
            } elseif ($totalScore < 80) {
                return ['percent' => $totalScore, 'label' => __('status_delayed') ?: 'Lewat', 'color' => 'warning', 'code' => 'delayed'];
            } else {
                return ['percent' => $totalScore, 'label' => __('status_on_track') ?: 'Ikut Jadual', 'color' => 'success', 'code' => 'on_track'];
            }
        }
        
        // Calculate offset (actual - expected)
        $offset = $totalScore - $expectedProgress;
        
        // Determine status based on how far behind/ahead
        if ($offset < -15) {
            // Behind by more than 15%
            return ['percent' => $totalScore, 'label' => __('status_critical') ?: 'Kritikal', 'color' => 'danger', 'code' => 'critical'];
        } elseif ($offset < -5) {
            // Behind by 5-15%
            return ['percent' => $totalScore, 'label' => __('status_delayed') ?: 'Lewat', 'color' => 'warning', 'code' => 'delayed'];
        } else {
            // On track or ahead
            return ['percent' => $totalScore, 'label' => __('status_on_track') ?: 'Ikut Jadual', 'color' => 'success', 'code' => 'on_track'];
        }
    }

    // Helper: Calculate Countdown
    private function calculateCountdown(?string $endDate, float $progress): array {
        if (!$endDate) {
            return [
                'days' => 0,
                'label' => '-',
                'badge_color' => 'secondary',
                'icon' => 'bi-dash-circle'
            ];
        }

        $today = new DateTime();
        $end = new DateTime($endDate);
        $interval = $today->diff($end);
        $days = (int)$interval->format('%R%a'); // Signed days difference

        // Project is completed (100%)
        if ($progress >= 100) {
            if ($days > 0) {
                // Completed early
                return [
                    'days' => abs($days),
                    'label' => sprintf(__('countdown_completed_early') ?: 'Selesai %d hari awal', abs($days)),
                    'badge_color' => 'success',
                    'icon' => 'bi-check-circle-fill'
                ];
            } elseif ($days === 0) {
                // Completed on time
                return [
                    'days' => 0,
                    'label' => __('countdown_completed_ontime') ?: 'Selesai tepat waktu',
                    'badge_color' => 'success',
                    'icon' => 'bi-check-circle'
                ];
            } else {
                // Completed late
                return [
                    'days' => abs($days),
                    'label' => sprintf(__('countdown_completed_late') ?: 'Selesai %d hari lewat', abs($days)),
                    'badge_color' => 'info',
                    'icon' => 'bi-info-circle-fill'
                ];
            }
        }

        // Project is not completed yet
        if ($days > 0) {
            // Days remaining
            if ($days <= 7) {
                // Urgent: less than 7 days
                return [
                    'days' => $days,
                    'label' => sprintf(__('countdown_days_left') ?: '%d hari lagi', $days),
                    'badge_color' => 'warning',
                    'icon' => 'bi-clock-fill'
                ];
            } else {
                // Normal: more than 7 days
                return [
                    'days' => $days,
                    'label' => sprintf(__('countdown_days_left') ?: '%d hari lagi', $days),
                    'badge_color' => 'primary',
                    'icon' => 'bi-calendar-check'
                ];
            }
        } else {
            // Overdue
            return [
                'days' => abs($days),
                'label' => sprintf(__('countdown_days_overdue') ?: '%d hari lewat', abs($days)),
                'badge_color' => 'danger',
                'icon' => 'bi-exclamation-triangle-fill'
            ];
        }
    }
}
