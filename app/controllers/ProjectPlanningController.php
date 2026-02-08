<?php
// controllers/ProjectPlanningController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../setting/helper/audit_helper.php';

class ProjectPlanningController {
    public string $lang = 'ms';
    public array $profile = [];
    public array $terasOptions = [];
    public array $ownerOptions = [];
    public array $projects = [];
    private PDO $pdo;

    public function __construct() {
        $this->lang = $_SESSION['lang'] ?? 'ms';
        $this->pdo = Database::getInstance('mysql')->getConnection();
        
        // Load User Profile
        $userModel = new User($this->pdo);
        $this->profile = $userModel->getProfile($_SESSION['f_stafID'] ?? '');

        // Load Dropdown Data
        $this->loadTerasOptions();
        $this->loadOwnerOptions();
        $this->loadProjects();
    }

    private function loadTerasOptions(): void {
        $sql = "SELECT f_terasID, f_kodTeras, f_namaTeras FROM tbl_monitoring_teras WHERE f_status = 1 ORDER BY f_kodTeras ASC";
        $this->terasOptions = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function loadOwnerOptions(): void {
        // Find users with roles suitable for Project Owner (e.g., ADM-SA, PIC, etc.)
        // For now, loading all active users
        $sql = "SELECT f_stafID, f_nama, f_groupKod FROM tbl_m_user WHERE f_statusID != 9 ORDER BY f_nama ASC";
        $this->ownerOptions = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function loadProjects(): void {
        $userGroupKod = $this->profile['f_groupKod'] ?? '';
        $userStafID = $_SESSION['f_stafID'] ?? '';
        $isAdmin = in_array($userGroupKod, ['ADM-SA', 'ADM-HR']);
        
        $sql = "
            SELECT 
                p.f_projectID, p.f_projectName, p.f_startDate, p.f_endDate,
                t.f_kodTeras, t.f_namaTeras, t.f_ownerStafID as terasOwnerStafID,
                u.f_nama as ownerName
            FROM tbl_monitoring_project p
            JOIN tbl_monitoring_teras t ON p.f_terasID = t.f_terasID
            LEFT JOIN tbl_m_user u ON p.f_ownerStafID = u.f_stafID
            WHERE p.f_status = 1
        ";
        
        // Non-admins can only see projects in teras they own
        if (!$isAdmin) {
            $sql .= " AND t.f_ownerStafID = :userStafID";
        }
        
        $sql .= " ORDER BY p.f_updatedt DESC";
        
        if (!$isAdmin) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':userStafID' => $userStafID]);
            $this->projects = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } else {
            $this->projects = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }

    public function getProjectDetails(int $id): ?array {
        $sql = "SELECT * FROM tbl_monitoring_project WHERE f_projectID = ? AND f_status = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            // Get Activities
            $sqlAct = "SELECT * FROM tbl_monitoring_aktiviti WHERE f_projectID = ? AND f_status = 1 ORDER BY f_aktivitiID ASC";
            $stmtAct = $this->pdo->prepare($sqlAct);
            $stmtAct->execute([$id]);
            $project['activities'] = $stmtAct->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        return $project ?: null;
    }

    public function saveProject(array $data): array {
        try {
            // Permission Check: Only admins and teras owners can save projects
            $userGroupKod = $this->profile['f_groupKod'] ?? '';
            $userStafID = $_SESSION['f_stafID'] ?? '';
            $isAdmin = in_array($userGroupKod, ['ADM-SA', 'ADM-HR']);
            
            if (!$isAdmin) {
                // Check if user owns the teras
                $terasID = $data['f_terasID'] ?? null;
                if ($terasID) {
                    $checkSql = "SELECT f_ownerStafID FROM tbl_monitoring_teras WHERE f_terasID = ?";
                    $stmt = $this->pdo->prepare($checkSql);
                    $stmt->execute([$terasID]);
                    $terasOwner = $stmt->fetchColumn();
                    
                    if ($terasOwner !== $userStafID) {
                        return ['success' => false, 'message' => 'Anda tidak mempunyai kebenaran untuk mengedit projek ini'];
                    }
                } else {
                    return ['success' => false, 'message' => 'ID Teras tidak sah'];
                }
            }
            
            $this->pdo->beginTransaction();

            $oldProject = null;
            if (!empty($data['f_projectID'])) {
                $stmtOld = $this->pdo->prepare("SELECT * FROM tbl_monitoring_project WHERE f_projectID = ?");
                $stmtOld->execute([$data['f_projectID']]);
                $oldProject = $stmtOld->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            
            // 1. Save Project Header
            $createdActivityIds = [];
            $updatedActivityIds = [];
            $deletedActivityIds = [];

            if (empty($data['f_projectID'])) {
                $sql = "INSERT INTO tbl_monitoring_project 
                        (f_tiersID, f_projectName, f_ownerStafID, f_startDate, f_endDate, f_createdby, f_terasID)
                        VALUES (:terasID, :name, :owner, :start, :end, :user, :terasID)"; // Typo in param binding fixed below
                
                $sql = "INSERT INTO tbl_monitoring_project 
                        (f_terasID, f_projectName, f_ownerStafID, f_startDate, f_endDate, f_createdby)
                        VALUES (:terasID, :name, :owner, :start, :end, :user)";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':terasID' => $data['f_terasID'],
                    ':name' => $data['f_projectName'],
                    ':owner' => $data['f_ownerStafID'],
                    ':start' => $data['f_startDate'],
                    ':end' => $data['f_endDate'],
                    ':user' => $_SESSION['f_stafID']
                ]);
                $projectID = $this->pdo->lastInsertId();
            } else {
                $projectID = $data['f_projectID'];
                $sql = "UPDATE tbl_monitoring_project SET
                        f_terasID = :terasID,
                        f_projectName = :name,
                        f_ownerStafID = :owner,
                        f_startDate = :start,
                        f_endDate = :end,
                        f_updatedt = NOW(),
                        f_updateby = :user
                        WHERE f_projectID = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':terasID' => $data['f_terasID'],
                    ':name' => $data['f_projectName'],
                    ':owner' => $data['f_ownerStafID'],
                    ':start' => $data['f_startDate'],
                    ':end' => $data['f_endDate'],
                    ':user' => $_SESSION['f_stafID'],
                    ':id' => $projectID
                ]);
            }
            
            // 2. Process Activities (for BOTH new and existing projects)
            if (!empty($data['activities'])) {
                // Get existing activity IDs
                $existing = $this->pdo->query("SELECT f_aktivitiID FROM tbl_monitoring_aktiviti WHERE f_projectID = $projectID")->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $submittedIDs = [];
                
                $sqlInsert = "INSERT INTO tbl_monitoring_aktiviti 
                           (f_projectID, f_namaAktiviti, f_kpi, f_target, f_weightage, f_startDate, f_endDate, f_status, f_createdby)
                           VALUES (:pid, :name, :kpi, :target, :weight, :start, :end, 1, :user)";
                
                $sqlUpdate = "UPDATE tbl_monitoring_aktiviti SET 
                              f_namaAktiviti = :name, f_kpi = :kpi, f_target = :target, f_weightage = :weight, 
                              f_startDate = :start, f_endDate = :end
                              WHERE f_aktivitiID = :id";
                              
                $stmtInsert = $this->pdo->prepare($sqlInsert);
                $stmtUpdate = $this->pdo->prepare($sqlUpdate);

                foreach ($data['activities'] as $act) {
                    $actID = $act['id'] ?? null;
                    
                    // Common params
                    $commonParams = [
                        ':name' => $act['name'],
                        ':kpi' => $act['kpi'],
                        ':target' => $act['target'],
                        ':weight' => $act['weight'],
                        ':start' => !empty($act['startDate']) ? $act['startDate'] : null,
                        ':end' => !empty($act['endDate']) ? $act['endDate'] : null,
                    ];

                    if ($actID && in_array($actID, $existing)) {
                        // Update existing
                        $updateParams = array_merge($commonParams, [':id' => $actID]);
                        $stmtUpdate->execute($updateParams);
                        $submittedIDs[] = $actID;
                        $updatedActivityIds[] = $actID;
                    } else {
                        // Insert new
                        $insertParams = array_merge($commonParams, [
                            ':pid' => $projectID,
                            ':user' => $_SESSION['f_stafID']
                        ]);
                        $stmtInsert->execute($insertParams);
                        $newActId = $this->pdo->lastInsertId();
                        $submittedIDs[] = $newActId;
                        $createdActivityIds[] = $newActId;
                    }
                }
                
                // 3. Delete Removed Activities
                $toDelete = array_diff($existing, $submittedIDs);
                if (!empty($toDelete)) {
                    $inQuery = implode(',', array_fill(0, count($toDelete), '?'));
                    $this->pdo->prepare("DELETE FROM tbl_monitoring_aktiviti WHERE f_aktivitiID IN ($inQuery)")->execute(array_values($toDelete));
                    $deletedActivityIds = array_values($toDelete);
                }
            }

            $this->pdo->commit();

            $this->auditProjectChange(
                empty($data['f_projectID']) ? 'CREATE' : 'UPDATE',
                (string)$projectID,
                $oldProject,
                [
                    'f_terasID' => $data['f_terasID'] ?? null,
                    'f_projectName' => $data['f_projectName'] ?? '',
                    'f_ownerStafID' => $data['f_ownerStafID'] ?? null,
                    'f_startDate' => $data['f_startDate'] ?? null,
                    'f_endDate' => $data['f_endDate'] ?? null,
                ],
                [
                    'activities_created' => $createdActivityIds,
                    'activities_updated' => $updatedActivityIds,
                    'activities_deleted' => $deletedActivityIds,
                ]
            );

            return ['success' => true, 'message' => 'Projek berjaya disimpan'];

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            return ['success' => false, 'message' => 'Ralat pangkalan data: ' . $e->getMessage()];
        }
    }

    private function auditProjectChange(string $eventType, string $projectId, ?array $old, array $new, array $meta = []): void {
        if (!function_exists('audit_event')) return;
        $actorLabel = function_exists('audit_format_actor_label') ? audit_format_actor_label($this->profile['f_nama'] ?? null, $this->profile['f_nopekerja'] ?? null) : ($this->profile['f_nama'] ?? null);
        $eventId = audit_event([
            'event_type' => $eventType,
            'severity' => 'INFO',
            'outcome' => 'SUCCESS',
            'target_type' => 'project',
            'target_id' => $projectId,
            'target_label' => $new['f_projectName'] ?? ($old['f_projectName'] ?? ('Project '.$projectId)),
            'message' => audit_format_message('Project '.$eventType, $actorLabel),
            'request_id' => $GLOBALS['__AUDIT_REQUEST_ID'] ?? null,
            'session_id' => session_id(),
            'user_id' => $_SESSION['f_stafID'] ?? null,
            'actor_label' => $actorLabel,
            'meta' => $meta
        ]);
        if ($eventId) {
            $changeSetId = audit_begin_change($eventId, 'project', $projectId, 'Project '.$eventType, $meta);
            if ($changeSetId) {
                foreach ($new as $k => $v) {
                    $oldVal = $old[$k] ?? null;
                    if ($oldVal !== $v) {
                        audit_change($changeSetId, $k, $oldVal, $v, 'string', false);
                    }
                }
            }
        }
    }
}
