<?php
// controllers/StrategicDashboardController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

class StrategicDashboardController {
    public string $lang = 'ms';
    public array $profile = [];
    public array $stats = [
        'total' => 0,
        'on_track' => 0,
        'delayed' => 0,
        'critical' => 0,
        'completed' => 0
    ];
    public array $projects = [];
    private PDO $pdo;

    public function __construct() {
        $this->lang = $_SESSION['lang'] ?? 'ms';
        $this->pdo = Database::getInstance('mysql')->getConnection();
        
        $userModel = new User($this->pdo);
        $this->profile = $userModel->getProfile($_SESSION['f_stafID'] ?? '');
    }

    // Get Teras List
    public function getAvailableTeras(): array {
        $sql = "SELECT f_terasID, f_kodTeras, f_namaTeras FROM tbl_monitoring_teras WHERE f_status = 1 ORDER BY f_kodTeras ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Get Overall Statistics (Filtered)
    public function getStats(?int $year = null, ?string $filterTeras = null): array {
        $year = $year ?? (int)date('Y');
        
        // Base SQL
        $sql = "SELECT p.f_projectID FROM tbl_monitoring_project p WHERE p.f_status IN (1, 2)";
        $params = [];
        
        // Apply Teras Filter if present
        if (!empty($filterTeras)) {
            $sql .= " AND p.f_terasID = ?";
            $params[] = $filterTeras;
        }
        
        $projects = $this->pdo->prepare($sql);
        $projects->execute($params);
        $rows = $projects->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // Reset stats
        $this->stats = ['total' => 0, 'on_track' => 0, 'delayed' => 0, 'critical' => 0, 'completed' => 0];
        $this->stats['total'] = count($rows);
        
        foreach ($rows as $p) {
            $health = $this->calculateHealth((int)$p['f_projectID']);
            
            if ($health['code'] === 'completed') $this->stats['completed']++;
            elseif ($health['code'] === 'critical') $this->stats['critical']++;
            elseif ($health['code'] === 'delayed') $this->stats['delayed']++;
            else $this->stats['on_track']++;
        }
        
        return $this->stats;
    }

    // Get All Project Lists Categorized (Filtered)
    public function getAllProjectLists(int $limit = 50, ?string $filterTeras = null): array {
        $sql = "
            SELECT p.f_projectID, p.f_projectName, u.f_nama as ownerName, p.f_endDate
            FROM tbl_monitoring_project p
            LEFT JOIN tbl_m_user u ON p.f_ownerStafID = u.f_stafID
            WHERE p.f_status IN (1, 2)
        ";
        $params = [];

        if (!empty($filterTeras)) {
            $sql .= " AND p.f_terasID = ?";
            $params[] = $filterTeras;
        }

        $sql .= " ORDER BY p.f_updatedt DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $result = [
            'total' => [],
            'completed' => [],
            'on_track' => [],
            'delayed' => [],
            'critical' => []
        ];
        
        foreach ($projects as $p) {
            $health = $this->calculateHealth((int)$p['f_projectID']);
            
            // Common props
            $p['progress'] = $health['percent'];
            $p['status_label'] = $health['label'];
            $p['status_code'] = $health['code'];
            $p['deviation'] = $health['offset'];
            $p['status_class'] = 'bg-'.$health['color'];
            
            // Add to Total
            $result['total'][] = $p;
            
            // Categorize
            if ($health['code'] === 'completed') {
                $result['completed'][] = $p;
            } elseif ($health['code'] === 'critical') {
                $result['critical'][] = $p;
            } elseif ($health['code'] === 'delayed') {
                $result['delayed'][] = $p;
            } else {
                $result['on_track'][] = $p;
            }
        }
        
        // Optional slicing (disabled for now to show all)
        return $result;
    }
    
    // Helper: Reuse logic slightly modified from MonitoringController
    private function calculateHealth(int $projectID): array {
        // 1. Get Activities & Weightages
        $sql = "SELECT f_aktivitiID, f_weightage FROM tbl_monitoring_aktiviti WHERE f_projectID = ? AND f_status = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$projectID]);
        $acts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        if (empty($acts)) {
             return ['percent'=>0, 'label'=> __('status_new') ?: 'Baru', 'color'=>'secondary', 'code'=>'new', 'offset'=>0];
        }
        
        $totalWeight = 0;
        $actualProgress = 0;
        
        foreach ($acts as $a) {
            $w = (float)$a['f_weightage'];
            $totalWeight += $w;
            
            // Get latest progress
            $sqlProg = "SELECT f_percentComplete FROM tbl_monitoring_laporan WHERE f_aktivitiID = ? ORDER BY f_tahun DESC, f_bulan DESC LIMIT 1";
            $stmtProg = $this->pdo->prepare($sqlProg);
            $stmtProg->execute([$a['f_aktivitiID']]);
            $prog = $stmtProg->fetchColumn();
            
            $p = (float)($prog ?: 0);
            $actualProgress += ($p * $w / 100);
        }
        
        // Normalize if weight != 100 (optional protection)
        if ($totalWeight > 0 && $totalWeight != 100) {
            $actualProgress = ($actualProgress / $totalWeight) * 100;
        }
        
        $actualProgress = round($actualProgress, 1);
        
        if ($actualProgress >= 100) {
            return ['percent'=>100, 'label'=> __('status_completed') ?: 'Selesai', 'color'=>'success', 'code'=>'completed', 'offset'=>0];
        }
        
        // Calculate Expected Progress based on Timeline (Simple Linear)
        // ideally we map activities to dates, but project-level linear is a good fallback
        $sqlDates = "SELECT f_startDate, f_endDate FROM tbl_monitoring_project WHERE f_projectID = ?";
        $stmtDates = $this->pdo->prepare($sqlDates);
        $stmtDates->execute([$projectID]);
        $dates = $stmtDates->fetch(PDO::FETCH_ASSOC);
        
        $expected = 0;
        $now = time();
        if ($dates && $dates['f_startDate'] && $dates['f_endDate']) {
            $start = strtotime($dates['f_startDate']);
            $end = strtotime($dates['f_endDate']);
            
            if ($now < $start) $expected = 0;
            elseif ($now > $end) $expected = 100;
            else {
                $totalDays = ($end - $start) / 86400;
                $daysPassed = ($now - $start) / 86400;
                if ($totalDays > 0) {
                    $expected = ($daysPassed / $totalDays) * 100;
                }
            }
        }
        
        $offset = $actualProgress - $expected; // e.g. 40 - 50 = -10 (Behind)
        
        // STRICT CHECK: If project is past due and not 100%, it is CRITICAL regardless of deviation size
        if ($now > $end && $actualProgress < 100) {
             return ['percent'=>$actualProgress, 'label'=> __('status_critical') ?: 'Kritikal', 'color'=>'danger', 'code'=>'critical', 'offset'=>$offset];
        }

        if ($offset < -15) { // Behind by more than 15%
            return ['percent'=>$actualProgress, 'label'=> __('status_critical') ?: 'Kritikal', 'color'=>'danger', 'code'=>'critical', 'offset'=>$offset];
        } elseif ($offset < -5) { // Behind by 5-15%
            return ['percent'=>$actualProgress, 'label'=> __('status_delayed') ?: 'Lewat', 'color'=>'warning', 'code'=>'delayed', 'offset'=>$offset];
        } else {
            return ['percent'=>$actualProgress, 'label'=> __('status_on_track') ?: 'Ikut Jadual', 'color'=>'success', 'code'=>'on_track', 'offset'=>$offset];
        }
    }
}
?>
