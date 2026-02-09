<?php
// controllers/ProjectReportingController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../setting/helper/audit_helper.php';

class ProjectReportingController {
    public string $lang = 'ms';
    public array $profile = [];
    public array $userProjects = [];
    private PDO $pdo;

    public function __construct() {
        $this->lang = $_SESSION['lang'] ?? 'ms';
        $this->pdo = Database::getInstance('mysql')->getConnection();
        
        // Load User Profile
        $userModel = new User($this->pdo);
        $this->profile = $userModel->getProfile($_SESSION['f_stafID'] ?? '');

        // Load Projects for this user
        $this->loadUserProjects();
    }

    private function loadUserProjects(): void {
        $stafID = $_SESSION['f_stafID'] ?? '';
        $isAdmin = function_exists('prestasi_user_active_role_in') && prestasi_user_active_role_in(
            $this->profile,
            $this->pdo,
            [defined('PRESTASI_ROLE_ID_ADM_SA') ? (int)PRESTASI_ROLE_ID_ADM_SA : 0, defined('PRESTASI_ROLE_ID_ADM_HR') ? (int)PRESTASI_ROLE_ID_ADM_HR : 0],
            [defined('PRESTASI_ROLE_ADM_SA') ? (string)PRESTASI_ROLE_ADM_SA : 'ADM-SA', defined('PRESTASI_ROLE_ADM_HR') ? (string)PRESTASI_ROLE_ADM_HR : 'ADM-HR']
        );

        // If Admin, show all. If Owner, show assigned.
        // Join teras and user to provide fields used by the view (f_kodTeras, ownerName)
        if ($isAdmin) {
            $sql = "SELECT p.f_projectID, p.f_projectName, p.f_ownerStafID, p.f_startDate, p.f_endDate, p.f_status, t.f_kodTeras, u.f_nama as ownerName
                    FROM tbl_monitoring_project p
                    JOIN tbl_monitoring_teras t ON p.f_terasID = t.f_terasID
                    LEFT JOIN tbl_m_user u ON p.f_ownerStafID = u.f_stafID
                    WHERE p.f_status = 1";
            $params = [];
        } else {
            $sql = "SELECT p.f_projectID, p.f_projectName, p.f_ownerStafID, p.f_startDate, p.f_endDate, p.f_status, t.f_kodTeras, u.f_nama as ownerName
                    FROM tbl_monitoring_project p
                    JOIN tbl_monitoring_teras t ON p.f_terasID = t.f_terasID
                    LEFT JOIN tbl_m_user u ON p.f_ownerStafID = u.f_stafID
                    WHERE p.f_status = 1 AND p.f_ownerStafID = ?";
            $params = [$stafID];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Calculate progress & status similar to ProjectMonitoringController so view has consistent fields
        foreach ($projects as &$p) {
            $calc = $this->calculateProjectProgress((int)$p['f_projectID'], $p['f_endDate'] ?? null);
            $p['progress'] = $calc['percent'];
            $p['status_label'] = $calc['label'];
            $p['status_color'] = $calc['color'];
        }

        $this->userProjects = $projects;
    }

    // Helper: Logic for Health Calculation (copied from ProjectMonitoringController)
    private function calculateProjectProgress(int $projectID, ?string $endDate): array {
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
            $weight = (float)($r['f_weightage'] ?? 0);
            $pct = (float)($r['current_percent'] ?? 0);
            $totalScore += ($weight * ($pct / 100));
        }

        $totalScore = round($totalScore, 1);

        $today = date('Y-m-d');
        if ($endDate && $today > $endDate && $totalScore < 100) {
             return ['percent' => $totalScore, 'label' => __('status_expired') ?: 'Lewat (Tamat Tempoh)', 'color' => 'danger', 'code' => 'delayed'];
        }

        if ($totalScore < 40) {
            return ['percent' => $totalScore, 'label' => __('status_critical') ?: 'Berisiko Tinggi', 'color' => 'danger', 'code' => 'critical'];
        } elseif ($totalScore < 80) {
            return ['percent' => $totalScore, 'label' => __('status_delayed') ?: 'Lewat', 'color' => 'warning', 'code' => 'delayed'];
        } else {
            return ['percent' => $totalScore, 'label' => __('status_on_track') ?: 'Ikut Jadual', 'color' => 'success', 'code' => 'on_track'];
        }
    }

    public function getReportData(int $projectID, int $month, int $year): array {
        // 1. Get Project Info
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_monitoring_project WHERE f_projectID = ?");
        $stmt->execute([$projectID]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) return ['error' => 'Projek tidak dijumpai'];

        // 2. Get Activities with joined Report Data for specific month/year
        // 2. Get Activities with joined Report Data for specific month/year
        // Also fetch the LATEST PREVIOUS percentage if current is null
        $dateParam = sprintf('%04d-%02d-01', $year, $month);
        
        $sql = "
            SELECT 
                a.f_aktivitiID, a.f_namaAktiviti, a.f_kpi, a.f_target, a.f_weightage,
                l.f_laporanID, l.f_percentComplete, l.f_statusKemajuan, l.f_catatan, l.f_dokumen,
                (
                     SELECT prev.f_percentComplete 
                     FROM tbl_monitoring_laporan prev 
                     WHERE prev.f_aktivitiID = a.f_aktivitiID 
                     AND (prev.f_tahun < ? OR (prev.f_tahun = ? AND prev.f_bulan < ?))
                     ORDER BY prev.f_tahun DESC, prev.f_bulan DESC
                     LIMIT 1
                ) as last_percent
            FROM tbl_monitoring_aktiviti a
            LEFT JOIN tbl_monitoring_laporan l 
                ON a.f_aktivitiID = l.f_aktivitiID 
                AND l.f_bulan = ? 
                AND l.f_tahun = ?
            WHERE a.f_projectID = ? AND a.f_status = 1
            ORDER BY a.f_aktivitiID ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        // Params: Year, Year, Month (for prev check), Month, Year, ProjectID
        $stmt->execute([$year, $year, $month, $month, $year, $projectID]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'project' => $project,
            'activities' => $activities
        ];
    }

    public function saveReport(array $data, array $files = []): array {
        try {
            $this->pdo->beginTransaction();

            $month = (int)$data['month'];
            $year = (int)$data['year'];
            $reports = $data['reports']; // Array of {aktivitiID, percent, catatan}

            $stmtInsert = $this->pdo->prepare("
                INSERT INTO tbl_monitoring_laporan 
                (f_aktivitiID, f_bulan, f_tahun, f_percentComplete, f_catatan, f_submittedby, f_submitteddt)
                VALUES (:aid, :m, :y, :pct, :cat, :user, NOW())
                ON DUPLICATE KEY UPDATE 
                f_percentComplete = :pct2, 
                f_catatan = :cat2, 
                f_submitteddt = NOW()
            ");

            // Needed because table doesn't have unique constraint on (aktiviti, bulan, tahun) yet?
            // Actually it should. Let's assume logic handles ID checking or we rely on duplicate logic if Unique Index exists.
            // **Correction**: The schema created earlier `create_monitoring_tables.sql` has `idx_period` but NOT a UNIQUE constraint.
            // So `ON DUPLICATE KEY` won't work unless I query first or add unique constraint. 
            // For safety, I will Check for Existence first.

            $stmtCheck = $this->pdo->prepare("SELECT f_laporanID FROM tbl_monitoring_laporan WHERE f_aktivitiID = ? AND f_bulan = ? AND f_tahun = ?");
            $stmtUpdate = $this->pdo->prepare("UPDATE tbl_monitoring_laporan SET f_percentComplete = ?, f_catatan = ?, f_submitteddt = NOW() WHERE f_laporanID = ?");
            $stmtNew = $this->pdo->prepare("INSERT INTO tbl_monitoring_laporan (f_aktivitiID, f_bulan, f_tahun, f_percentComplete, f_catatan, f_submittedby, f_submitteddt) VALUES (?, ?, ?, ?, ?, ?, NOW())");

            $created = [];
            $updated = [];

            foreach ($reports as $r) {
                $aid = $r['aktivitiID'];
                $pct = $r['percent'];
                $cat = $r['catata'];

                // Check exist
                $stmtCheck->execute([$aid, $month, $year]);
                $exists = $stmtCheck->fetchColumn();

                if ($exists) {
                    $stmtUpdate->execute([$pct, $cat, $exists]);
                    $updated[] = $aid;
                } else {
                    $stmtNew->execute([$aid, $month, $year, $pct, $cat, $_SESSION['f_stafID']]);
                    $created[] = $aid;
                }
            }

            $this->pdo->commit();

            $this->auditReportSave($month, $year, $created, $updated);
            return ['success' => true, 'message' => 'Laporan berjaya disimpan'];

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            return ['success' => false, 'message' => 'Ralat: ' . $e->getMessage()];
        }
    }

    private function auditReportSave(int $month, int $year, array $created, array $updated): void {
        if (!function_exists('audit_event')) return;
        $actorLabel = function_exists('audit_format_actor_label') ? audit_format_actor_label($this->profile['f_nama'] ?? null, $this->profile['f_nopekerja'] ?? null) : ($this->profile['f_nama'] ?? null);
        $eventId = audit_event([
            'event_type' => 'UPDATE',
            'severity' => 'INFO',
            'outcome' => 'SUCCESS',
            'target_type' => 'report',
            'target_id' => sprintf('%04d-%02d', $year, $month),
            'target_label' => 'Monitoring Report',
            'message' => audit_format_message('Report SAVE', $actorLabel),
            'request_id' => $GLOBALS['__AUDIT_REQUEST_ID'] ?? null,
            'session_id' => session_id(),
            'user_id' => $_SESSION['f_stafID'] ?? null,
            'actor_label' => $actorLabel,
            'meta' => [
                'month' => $month,
                'year' => $year,
                'created_activity_ids' => $created,
                'updated_activity_ids' => $updated,
            ]
        ]);
        if ($eventId) {
            $changeSetId = audit_begin_change($eventId, 'report', sprintf('%04d-%02d', $year, $month), 'Report SAVE', [
                'month' => $month,
                'year' => $year,
            ]);
            if ($changeSetId) {
                audit_change($changeSetId, 'created_count', null, count($created), 'int', false);
                audit_change($changeSetId, 'updated_count', null, count($updated), 'int', false);
            }
        }
    }
}
