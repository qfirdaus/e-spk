<?php
// controllers/ProgramSetupController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../setting/helper/audit_helper.php';

class ProgramSetupController {
    // ✅ PUBLIC PROPERTIES (accessible from page)
    public string $lang = 'ms';
    public array $profile = [];
    public array $programList = [];
    public array $terasList = [];
    public array $userList = [];
    public array $systemSettings = [];

    // ✅ PRIVATE PROPERTIES (internal use only)
    private PDO $pdo;

    // ✅ CONSTRUCTOR - Initialize everything
    public function __construct() {
        // Get language from session
        $this->lang = $_SESSION['lang'] ?? 'ms';
        
        // Get database connection
        $this->pdo = Database::getInstance('mysql')->getConnection();

        // Load user profile
        $userModel = new User($this->pdo);
        $f_stafID = $_SESSION['f_stafID'] ?? null;
        $this->profile = $f_stafID ? ($userModel->getProfile($f_stafID) ?: []) : [];

        // Load themes
        $themeSetting = json_decode($this->profile['f_themeSetting'] ?? '{}', true) ?: [];
        $_SESSION['theme.menu']   = $themeSetting['sidebarColor'] ?? $_SESSION['theme.menu'] ?? 'light';
        $_SESSION['theme.topbar'] = $themeSetting['topbarColor'] ?? $_SESSION['theme.topbar'] ?? 'light';
        $_SESSION['theme.layout'] = $themeSetting['layoutMode'] ?? $_SESSION['theme.layout'] ?? 'light';

        // Load data
        $this->loadProgramList();
        $this->loadTerasList();
        $this->loadUserList();
        $this->loadSystemSettings();
    }

    // ✅ LOAD PROGRAM LIST
    private function loadProgramList(): void {
        try {
            $sql = "
                SELECT 
                    f_programID,
                    f_programName,
                    f_tahun,
                    f_description,
                    f_status,
                    f_createddt
                FROM tbl_monitoring_program
                WHERE f_status = 1
                ORDER BY f_tahun DESC, f_createddt DESC
            ";
            $stmt = $this->pdo->query($sql);
            $this->programList = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error loading programs: ' . $e->getMessage());
            $this->programList = [];
        }
    }

    // ✅ LOAD TERAS STRATEGIK LIST
    private function loadTerasList(): void {
        try {
            $sql = "
                SELECT 
                    t.f_terasID,
                    t.f_kodTeras,
                    t.f_namaTeras,
                    t.f_jenis,
                    t.f_status,
                    t.f_ownerStafID,
                    p.f_programName,
                    u.f_nama as ownerName
                FROM tbl_monitoring_teras t
                LEFT JOIN tbl_monitoring_program p ON t.f_programID = p.f_programID
                LEFT JOIN tbl_m_user u ON t.f_ownerStafID = u.f_stafID
                WHERE t.f_status = 1
                ORDER BY t.f_kodTeras ASC
            ";
            $stmt = $this->pdo->query($sql);
            $this->terasList = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error loading teras: ' . $e->getMessage());
            $this->terasList = [];
        }
    }

    // ✅ LOAD USER LIST (for role assignment)
    private function loadUserList(): void {
        try {
            $sql = "
                SELECT 
                    u.f_stafID,
                    u.f_nama,
                    u.f_email,
                    u.f_groupKod,
                    g.f_groupName
                FROM tbl_m_user u
                LEFT JOIN tbl_m_group g ON u.f_groupKod = g.f_groupKod
                WHERE u.f_statusID != 9
                ORDER BY u.f_nama ASC
            ";
            $stmt = $this->pdo->query($sql);
            $this->userList = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error loading users: ' . $e->getMessage());
            $this->userList = [];
        }
    }

    // ✅ LOAD SYSTEM SETTINGS
    private function loadSystemSettings(): void {
        try {
            $sql = "
                SELECT f_key, f_value, f_description
                FROM tbl_monitoring_settings
                ORDER BY f_key ASC
            ";
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Convert to associative array
            foreach ($rows as $row) {
                $this->systemSettings[$row['f_key']] = [
                    'value' => $row['f_value'],
                    'description' => $row['f_description'] ?? ''
                ];
            }
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error loading settings: ' . $e->getMessage());
            $this->systemSettings = [];
        }
    }

    // ✅ PUBLIC METHOD: Save Program Data
    public function saveProgram(array $data): array {
        try {
            $programID = $data['f_programID'] ?? null;
            $isUpdate = !empty($programID);

            $old = null;
            if ($isUpdate) {
                $stmtOld = $this->pdo->prepare("SELECT * FROM tbl_monitoring_program WHERE f_programID = ?");
                $stmtOld->execute([$programID]);
                $old = $stmtOld->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            if ($isUpdate) {
                // Update existing program
                $sql = "
                    UPDATE tbl_monitoring_program SET
                        f_programName = :programName,
                        f_tahun = :tahun,
                        f_description = :description,
                        f_updatedt = NOW(),
                        f_updateby = :updateby
                    WHERE f_programID = :programID
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':programName' => $data['f_programName'] ?? '',
                    ':tahun' => $data['f_tahun'] ?? date('Y'),
                    ':description' => $data['f_description'] ?? '',
                    ':updateby' => $_SESSION['f_stafID'] ?? null,
                    ':programID' => $programID
                ]);

                $this->auditProgramChange('UPDATE', (string)$programID, $old, [
                    'f_programName' => $data['f_programName'] ?? '',
                    'f_tahun' => $data['f_tahun'] ?? date('Y'),
                    'f_description' => $data['f_description'] ?? '',
                ]);
                
                return ['success' => true, 'message' => 'Program berjaya dikemaskini'];
            } else {
                // Insert new program
                $sql = "
                    INSERT INTO tbl_monitoring_program 
                    (f_programName, f_tahun, f_description, f_createdby, f_status)
                    VALUES (:programName, :tahun, :description, :createdby, 1)
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':programName' => $data['f_programName'] ?? '',
                    ':tahun' => $data['f_tahun'] ?? date('Y'),
                    ':description' => $data['f_description'] ?? '',
                    ':createdby' => $_SESSION['f_stafID'] ?? null
                ]);

                $newId = (string)$this->pdo->lastInsertId();
                $this->auditProgramChange('CREATE', $newId, null, [
                    'f_programName' => $data['f_programName'] ?? '',
                    'f_tahun' => $data['f_tahun'] ?? date('Y'),
                    'f_description' => $data['f_description'] ?? '',
                ]);
                
                return ['success' => true, 'message' => 'Program berjaya ditambah'];
            }
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error saving program: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan program: ' . $e->getMessage()];
        }
    }

    // ✅ PUBLIC METHOD: Save Teras
    public function saveTeras(array $data): array {
        try {
            $terasID = $data['f_terasID'] ?? null;
            $isUpdate = !empty($terasID);

            $old = null;
            if ($isUpdate) {
                $stmtOld = $this->pdo->prepare("SELECT * FROM tbl_monitoring_teras WHERE f_terasID = ?");
                $stmtOld->execute([$terasID]);
                $old = $stmtOld->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            if ($isUpdate) {
                $sql = "
                    UPDATE tbl_monitoring_teras SET
                        f_kodTeras = :kodTeras,
                        f_namaTeras = :namaTeras,
                        f_jenis = :jenis,
                        f_description = :description,
                        f_programID = :programID,
                        f_ownerStafID = :ownerStafID
                    WHERE f_terasID = :terasID
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':kodTeras' => $data['f_kodTeras'] ?? '',
                    ':namaTeras' => $data['f_namaTeras'] ?? '',
                    ':jenis' => $data['f_jenis'] ?? 'Teras',
                    ':description' => $data['f_description'] ?? '',
                    ':programID' => $data['f_programID'] ?? null,
                    ':ownerStafID' => !empty($data['f_ownerStafID']) ? $data['f_ownerStafID'] : null,
                    ':terasID' => $terasID
                ]);

                $this->auditTerasChange('UPDATE', (string)$terasID, $old, [
                    'f_kodTeras' => $data['f_kodTeras'] ?? '',
                    'f_namaTeras' => $data['f_namaTeras'] ?? '',
                    'f_jenis' => $data['f_jenis'] ?? 'Teras',
                    'f_description' => $data['f_description'] ?? '',
                    'f_programID' => $data['f_programID'] ?? null,
                    'f_ownerStafID' => !empty($data['f_ownerStafID']) ? $data['f_ownerStafID'] : null,
                ]);
                
                return ['success' => true, 'message' => 'Teras berjaya dikemaskini'];
            } else {
                $sql = "
                    INSERT INTO tbl_monitoring_teras 
                    (f_kodTeras, f_namaTeras, f_jenis, f_description, f_programID, f_ownerStafID, f_createdby, f_status)
                    VALUES (:kodTeras, :namaTeras, :jenis, :description, :programID, :ownerStafID, :createdby, 1)
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':kodTeras' => $data['f_kodTeras'] ?? '',
                    ':namaTeras' => $data['f_namaTeras'] ?? '',
                    ':jenis' => $data['f_jenis'] ?? 'Teras',
                    ':description' => $data['f_description'] ?? '',
                    ':programID' => $data['f_programID'] ?? null,
                    ':ownerStafID' => !empty($data['f_ownerStafID']) ? $data['f_ownerStafID'] : null,
                    ':createdby' => $_SESSION['f_stafID'] ?? null
                ]);

                $newId = (string)$this->pdo->lastInsertId();
                $this->auditTerasChange('CREATE', $newId, null, [
                    'f_kodTeras' => $data['f_kodTeras'] ?? '',
                    'f_namaTeras' => $data['f_namaTeras'] ?? '',
                    'f_jenis' => $data['f_jenis'] ?? 'Teras',
                    'f_description' => $data['f_description'] ?? '',
                    'f_programID' => $data['f_programID'] ?? null,
                    'f_ownerStafID' => !empty($data['f_ownerStafID']) ? $data['f_ownerStafID'] : null,
                ]);
                
                return ['success' => true, 'message' => 'Teras berjaya ditambah'];
            }
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error saving teras: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan teras: ' . $e->getMessage()];
        }
    }

    // ✅ PUBLIC METHOD: Update System Settings
    public function updateSettings(array $data): array {
        try {
            $oldMap = [];
            if ($data) {
                $keys = array_keys($data);
                $in = implode(',', array_fill(0, count($keys), '?'));
                $stmtOld = $this->pdo->prepare("SELECT f_key, f_value FROM tbl_monitoring_settings WHERE f_key IN ($in)");
                $stmtOld->execute($keys);
                foreach ($stmtOld->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $oldMap[$r['f_key']] = $r['f_value'];
                }
            }
            foreach ($data as $key => $value) {
                $sql = "
                    INSERT INTO tbl_monitoring_settings (f_key, f_value, f_updatedt, f_updateby)
                    VALUES (:key, :value, NOW(), :updateby)
                    ON DUPLICATE KEY UPDATE 
                        f_value = :value,
                        f_updatedt = NOW(),
                        f_updateby = :updateby
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':key' => $key,
                    ':value' => $value,
                    ':updateby' => $_SESSION['f_stafID'] ?? null
                ]);
            }

            $this->auditSettingsChange($oldMap, $data);
            
            return ['success' => true, 'message' => 'Tetapan sistem berjaya dikemaskini'];
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error updating settings: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal kemaskini tetapan: ' . $e->getMessage()];
        }
    }

    // ✅ PUBLIC METHOD: Delete Teras
    public function deleteTeras(int $terasID): array {
        try {
            $stmtOld = $this->pdo->prepare("SELECT * FROM tbl_monitoring_teras WHERE f_terasID = ?");
            $stmtOld->execute([$terasID]);
            $old = $stmtOld->fetch(PDO::FETCH_ASSOC) ?: null;

            $sql = "UPDATE tbl_monitoring_teras SET f_status = 9 WHERE f_terasID = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$terasID]);

            $this->auditTerasChange('DELETE', (string)$terasID, $old, ['f_status' => 9]);
            
            return ['success' => true, 'message' => 'Teras berjaya dipadam'];
        } catch (Throwable $e) {
            error_log('[ProgramSetupController] Error deleting teras: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memadam teras: ' . $e->getMessage()];
        }
    }

    private function auditProgramChange(string $eventType, string $programId, ?array $old, array $new): void {
        if (!function_exists('audit_event')) return;
        $actorLabel = function_exists('audit_format_actor_label') ? audit_format_actor_label($this->profile['f_nama'] ?? null, $this->profile['f_nopekerja'] ?? null) : ($this->profile['f_nama'] ?? null);
        $eventId = audit_event([
            'event_type' => $eventType,
            'severity' => 'INFO',
            'outcome' => 'SUCCESS',
            'target_type' => 'program',
            'target_id' => $programId,
            'target_label' => $new['f_programName'] ?? ($old['f_programName'] ?? ('Program '.$programId)),
            'message' => audit_format_message('Program '.$eventType, $actorLabel),
            'request_id' => $GLOBALS['__AUDIT_REQUEST_ID'] ?? null,
            'session_id' => session_id(),
            'user_id' => $_SESSION['f_stafID'] ?? null,
            'actor_label' => $actorLabel,
            'meta' => ['program_id' => $programId]
        ]);
        if ($eventId) {
            $changeSetId = audit_begin_change($eventId, 'program', $programId, 'Program '.$eventType, ['program_id' => $programId]);
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

    private function auditTerasChange(string $eventType, string $terasId, ?array $old, array $new): void {
        if (!function_exists('audit_event')) return;
        $actorLabel = function_exists('audit_format_actor_label') ? audit_format_actor_label($this->profile['f_nama'] ?? null, $this->profile['f_nopekerja'] ?? null) : ($this->profile['f_nama'] ?? null);
        $eventId = audit_event([
            'event_type' => $eventType,
            'severity' => 'INFO',
            'outcome' => 'SUCCESS',
            'target_type' => 'teras',
            'target_id' => $terasId,
            'target_label' => $new['f_namaTeras'] ?? ($old['f_namaTeras'] ?? ('Teras '.$terasId)),
            'message' => audit_format_message('Teras '.$eventType, $actorLabel),
            'request_id' => $GLOBALS['__AUDIT_REQUEST_ID'] ?? null,
            'session_id' => session_id(),
            'user_id' => $_SESSION['f_stafID'] ?? null,
            'actor_label' => $actorLabel,
            'meta' => ['teras_id' => $terasId]
        ]);
        if ($eventId) {
            $changeSetId = audit_begin_change($eventId, 'teras', $terasId, 'Teras '.$eventType, ['teras_id' => $terasId]);
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

    private function auditSettingsChange(array $oldMap, array $newMap): void {
        if (!function_exists('audit_event')) return;
        $actorLabel = function_exists('audit_format_actor_label') ? audit_format_actor_label($this->profile['f_nama'] ?? null, $this->profile['f_nopekerja'] ?? null) : ($this->profile['f_nama'] ?? null);
        $eventId = audit_event([
            'event_type' => 'UPDATE',
            'severity' => 'INFO',
            'outcome' => 'SUCCESS',
            'target_type' => 'settings',
            'target_id' => 'monitoring_settings',
            'target_label' => 'Monitoring Settings',
            'message' => audit_format_message('Settings UPDATE', $actorLabel),
            'request_id' => $GLOBALS['__AUDIT_REQUEST_ID'] ?? null,
            'session_id' => session_id(),
            'user_id' => $_SESSION['f_stafID'] ?? null,
            'actor_label' => $actorLabel,
            'meta' => ['keys' => array_keys($newMap)]
        ]);
        if ($eventId) {
            $changeSetId = audit_begin_change($eventId, 'settings', 'monitoring_settings', 'Settings UPDATE', ['keys' => array_keys($newMap)]);
            if ($changeSetId) {
                foreach ($newMap as $k => $v) {
                    $oldVal = $oldMap[$k] ?? null;
                    if ($oldVal !== $v) {
                        audit_change($changeSetId, $k, $oldVal, $v, 'string', false);
                    }
                }
            }
        }
    }
}
