<?php
// controllers/MyProjectsController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../setting/helper/audit_helper.php';

class MyProjectsController {
    public string $lang = 'ms';
    public array $profile = [];
    public array $terasOptions = [];
    public array $projects = [];
    private PDO $pdo;
    private string $currentUserStafID;

    public function __construct() {
        $this->lang = $_SESSION['lang'] ?? 'ms';
        $this->pdo = Database::getInstance('mysql')->getConnection();
        $this->currentUserStafID = $_SESSION['f_stafID'] ?? '';
        
        // Load User Profile
        $userModel = new User($this->pdo);
        $this->profile = $userModel->getProfile($this->currentUserStafID);

        // Load Dropdown Data
        $this->loadTerasOptions();
        $this->loadMyProjects();
    }

    private function loadTerasOptions(): void {
        $sql = "SELECT f_terasID, f_kodTeras, f_namaTeras FROM tbl_monitoring_teras WHERE f_status = 1 ORDER BY f_kodTeras ASC";
        $this->terasOptions = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function loadMyProjects(): void {
        // Load ONLY projects where current user is the owner
        $sql = "
            SELECT 
                p.f_projectID, p.f_projectName, p.f_startDate, p.f_endDate,
                t.f_kodTeras, t.f_namaTeras,
                u.f_nama as ownerName
            FROM tbl_monitoring_project p
            JOIN tbl_monitoring_teras t ON p.f_terasID = t.f_terasID
            LEFT JOIN tbl_m_user u ON p.f_ownerStafID = u.f_stafID
            WHERE p.f_status = 1 
            AND p.f_ownerStafID = :ownerID
            ORDER BY p.f_updatedt DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ownerID' => $this->currentUserStafID]);
        $this->projects = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getProjectDetails(int $id): ?array {
        // Verify ownership before returning data
        $sql = "SELECT * FROM tbl_monitoring_project WHERE f_projectID = ? AND f_status = 1 AND f_ownerStafID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $this->currentUserStafID]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            // Get Activities with edit permission flag
            $sqlAct = "SELECT * FROM tbl_monitoring_aktiviti WHERE f_projectID = ? AND f_status = 1 ORDER BY f_aktivitiID ASC";
            $stmtAct = $this->pdo->prepare($sqlAct);
            $stmtAct->execute([$id]);
            $activities = $stmtAct->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Add canEditDates flag: only editable if never updated (f_updatedt IS NULL)
            foreach ($activities as &$activity) {
                $activity['canEditDates'] = empty($activity['f_updatedt']);
            }
            
            $project['activities'] = $activities;
        }
        return $project ?: null;
    }

    public function updateActivityDates(array $data): array {
        try {
            // Security Check 1: Verify ownership
            $projectID = $data['f_projectID'] ?? null;
            if (!$projectID) {
                return ['success' => false, 'message' => 'ID projek tidak sah'];
            }

            $sqlCheck = "SELECT f_projectID FROM tbl_monitoring_project WHERE f_projectID = ? AND f_ownerStafID = ?";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([$projectID, $this->currentUserStafID]);
            
            if (!$stmtCheck->fetch()) {
                return ['success' => false, 'message' => 'Anda tidak mempunyai kebenaran untuk mengedit projek ini'];
            }

            $this->pdo->beginTransaction();
            
            // Only allow updating activity START and END dates (nothing else!)
            $updatedActivityIds = [];
            if (!empty($data['activities'])) {
                $sqlUpdate = "UPDATE tbl_monitoring_aktiviti SET 
                              f_startDate = :start, 
                              f_endDate = :end,
                              f_updatedt = NOW(),
                              f_updateby = :user
                              WHERE f_aktivitiID = :id AND f_projectID = :pid";
                              
                $stmtUpdate = $this->pdo->prepare($sqlUpdate);
                
                // Check which activities are locked (already edited)
                $sqlCheck = "SELECT f_aktivitiID FROM tbl_monitoring_aktiviti 
                             WHERE f_projectID = :pid AND f_updatedt IS NOT NULL";
                $stmtCheck = $this->pdo->prepare($sqlCheck);
                $stmtCheck->execute([':pid' => $projectID]);
                $lockedActivityIDs = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);

                foreach ($data['activities'] as $act) {
                    $actID = $act['id'] ?? null;
                    if (!$actID) continue; // Skip if no ID
                    
                    // Security Check 2: Prevent editing already-locked activities
                    if (in_array($actID, $lockedActivityIDs)) {
                        // Skip locked activities silently (they shouldn't be submitted anyway)
                        continue;
                    }
                    
                    $stmtUpdate->execute([
                        ':start' => !empty($act['startDate']) ? $act['startDate'] : null,
                        ':end' => !empty($act['endDate']) ? $act['endDate'] : null,
                        ':id' => $actID,
                        ':pid' => $projectID,
                        ':user' => $this->currentUserStafID
                    ]);
                    $updatedActivityIds[] = $actID;
                }
            }

            $this->pdo->commit();

            $this->auditActivityDateUpdate((string)$projectID, $updatedActivityIds);
            return ['success' => true, 'message' => 'Tarikh aktiviti berjaya dikemaskini'];

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            return ['success' => false, 'message' => 'Ralat pangkalan data: ' . $e->getMessage()];
        }
    }

    private function auditActivityDateUpdate(string $projectId, array $activityIds): void {
        if (!function_exists('audit_event')) return;
        $actorLabel = function_exists('audit_format_actor_label') ? audit_format_actor_label($this->profile['f_nama'] ?? null, $this->profile['f_nopekerja'] ?? null) : ($this->profile['f_nama'] ?? null);
        $eventId = audit_event([
            'event_type' => 'UPDATE',
            'severity' => 'INFO',
            'outcome' => 'SUCCESS',
            'target_type' => 'activity',
            'target_id' => $projectId,
            'target_label' => 'Project Activities',
            'message' => audit_format_message('Activity dates UPDATE', $actorLabel),
            'request_id' => $GLOBALS['__AUDIT_REQUEST_ID'] ?? null,
            'session_id' => session_id(),
            'user_id' => $_SESSION['f_stafID'] ?? null,
            'actor_label' => $actorLabel,
            'meta' => [
                'project_id' => $projectId,
                'activity_ids' => $activityIds,
            ]
        ]);
        if ($eventId) {
            $changeSetId = audit_begin_change($eventId, 'activity', $projectId, 'Activity dates UPDATE', [
                'project_id' => $projectId,
                'activity_ids' => $activityIds,
            ]);
            if ($changeSetId) {
                audit_change($changeSetId, 'updated_count', null, count($activityIds), 'int', false);
            }
        }
    }
}
