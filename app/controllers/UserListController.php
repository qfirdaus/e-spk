<?php
// controllers/UserListController.php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

class UserListController {
  public string $lang = 'ms';
  public array  $profile = [];
  public array  $senaraiUser = [];
  public array  $senaraiStaf = [];

  public int    $page = 1;       // tak guna untuk DataTables client-side
  public int    $perPage = 25;   // tak guna untuk DataTables client-side
  public int    $total = 0;
  public int    $lastPage = 1;
  public ?string $groupFilter = null;
  public string $q = '';

  private PDO $pdo;
  
  // Debug info untuk sync
  public array $syncDebug = [];

  public function __construct() {
    $this->lang = $_SESSION['lang'] ?? 'ms';
    $this->pdo  = Database::getInstance('mysql')->getConnection();

    $userModel  = new User($this->pdo);
    $f_stafID   = $_SESSION['f_stafID'] ?? null;
    $this->profile = $f_stafID ? ($userModel->getProfile($f_stafID) ?: []) : [];

    $themeSetting = json_decode($this->profile['f_themeSetting'] ?? '{}', true) ?: [];
    $_SESSION['theme.menu']   = $themeSetting['sidebarColor'] ?? $_SESSION['theme.menu'] ?? 'light';
    $_SESSION['theme.topbar'] = $themeSetting['topbarColor']  ?? $_SESSION['theme.topbar'] ?? 'light';
    $_SESSION['theme.layout'] = $themeSetting['layoutMode']   ?? $_SESSION['theme.layout'] ?? 'light';

    // ❌ Abaikan filter kumpulan – kita nak semua kumpulan keluar
    $this->groupFilter = null;

    // Carian teks opsyenal (nama/stafID/nopekerja)
    $this->q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

    // NOTE: Removed automatic sync on constructor to avoid running expensive
    // Sybase -> MySQL sync on every page load. Sync should be triggered
    // explicitly via the manual AJAX endpoint which calls
    // `syncUsersFromSybaseManual()` to record audit events only when a
    // user requests it.

    $this->loadUsers();
    // Load staff list for add-user modal as a safe fallback (may be empty if Sybase not available)
    try {
      $this->loadStaffForModal();
    } catch (Throwable $e) {
      // Non-fatal: keep page working even if staff list cannot be loaded
      error_log('[UserListController] loadStaffForModal failed: ' . $e->getMessage());
    }
  }

  /**
   * Load staff list from Sybase for populating add-user modal dropdown.
   * This is a best-effort fallback to populate the page when the AJAX
   * endpoint cannot be reached by client-side code.
   */
  private function loadStaffForModal(): void {
    try {
      $pdo = Database::pdoSybaseActive();
      $sql = "
        SELECT DISTINCT
          LTRIM(RTRIM(s.nopekerja))     AS nopekerja,
          LTRIM(RTRIM(s.idpekerja))     AS idpekerja,
          LTRIM(RTRIM(s.gelar_nama))    AS nama,
          LTRIM(RTRIM(s.jawatansemasa)) AS jawatan,
          LTRIM(RTRIM(s.jabatansemasa)) AS jabatan
        FROM v630staf_service_skim_all s
        WHERE CONVERT(INT, s.kodstatus) = 1
          AND s.nopekerja IS NOT NULL
          AND LTRIM(RTRIM(s.nopekerja)) <> ''
        ORDER BY s.gelar_nama ASC
      ";
      $stmt = $pdo->query($sql);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
      $this->senaraiStaf = $rows;
    } catch (Throwable $e) {
      // Don't throw further; leave senaraiStaf empty
      $this->senaraiStaf = [];
      error_log('[UserListController] loadStaffForModal DB error: ' . $e->getMessage());
    }
  }

  private function loadUsers(): void {
    $where  = ["COALESCE(u.f_statusID,0) <> 9"];
    $params = [];

    // ❌ Tiada tapisan kumpulan
    if ($this->q !== '') {
      $where[] = "(u.f_nama LIKE :q OR u.f_stafID LIKE :q OR u.f_nopekerja LIKE :q)";
      $params[':q'] = "%{$this->q}%";
    }
    $whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

    // Kira total penuh (tanpa LIMIT)
    $sqlCount = "
      SELECT COUNT(*)
      FROM tbl_m_user u
      LEFT JOIN tbl_m_group g
        ON g.f_groupID = u.f_groupID
      $whereSql
    ";
    $stmt = $this->pdo->prepare($sqlCount);
    foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
    $stmt->execute();
    $this->total = (int)$stmt->fetchColumn();
    $this->lastPage = 1;

    // Ambil SEMUA rekod (biar DataTables urus paging client-side)
    $sql = "
      SELECT
        u.f_userID,
        u.f_stafID,
        u.f_nopekerja,
        u.f_nama,
        u.f_namajabatan,
        u.f_jawatan,
        u.f_status,
        u.f_flag,
        u.f_groupID,
        TRIM(u.f_groupKod) AS f_groupKod,
        COALESCE(NULLIF(TRIM(g.f_groupName), ''), TRIM(u.f_groupKod)) AS f_groupName
      FROM tbl_m_user u
      LEFT JOIN tbl_m_group g
        ON g.f_groupID = u.f_groupID
      $whereSql
      ORDER BY u.f_nama ASC
    ";
    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
    $stmt->execute();

    $this->senaraiUser = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Attach additional roles (tbl_ref_access) for UI badges
    if (!empty($this->senaraiUser)) {
      $stafIds = [];
      foreach ($this->senaraiUser as $u) {
        $sid = trim((string)($u['f_stafID'] ?? ''));
        if ($sid !== '') $stafIds[] = $sid;
      }
      $stafIds = array_values(array_unique($stafIds));
      if (!empty($stafIds)) {
        $placeholders = implode(',', array_fill(0, count($stafIds), '?'));
        $sqlExtra = "
          SELECT a.f_stafID, g.f_groupName
          FROM tbl_ref_access a
          JOIN tbl_m_group g ON g.f_groupID = a.f_groupID
          JOIN tbl_m_user u ON u.f_stafID = a.f_stafID
          WHERE a.f_status = 1
            AND a.f_stafID IN ($placeholders)
            AND a.f_groupID <> u.f_groupID
          ORDER BY g.f_groupName ASC
        ";
        $stmtX = $this->pdo->prepare($sqlExtra);
        $stmtX->execute($stafIds);
        $rows = $stmtX->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $map = [];
        foreach ($rows as $r) {
          $sid = (string)($r['f_stafID'] ?? '');
          $rname = (string)($r['f_groupName'] ?? '');
          if ($sid === '' || $rname === '') continue;
          $map[$sid][] = $rname;
        }
        foreach ($this->senaraiUser as &$u) {
          $sid = trim((string)($u['f_stafID'] ?? ''));
          $extra = $map[$sid] ?? [];
          $u['extra_roles'] = $extra;
          $u['extra_roles_count'] = count($extra);
        }
        unset($u);
      }
    }
  }

  /**
   * Sync data staf dari Sybase (v630staf_service_skim_all) ke MySQL (tbl_m_user)
   * Hanya UPDATE record yang sudah wujud, tidak INSERT
   */
  private function syncUsersFromSybase(): void {
    $this->syncDebug = ['started' => date('Y-m-d H:i:s')];
    
    try {
      error_log("[UserListController] Starting sync from Sybase...");
      $this->syncDebug['step'] = 'Connecting to Sybase...';
      
      // Connect ke Sybase aktif
      $pdoSybase = Database::pdoSybaseActive();
      error_log("[UserListController] Sybase connection successful");
      $this->syncDebug['step'] = 'Sybase connected';
      
      // Test query untuk verify connection works
      try {
        $testStmt = $pdoSybase->query("SELECT 1 as test");
        $testResult = $testStmt->fetch(PDO::FETCH_ASSOC);
        error_log("[UserListController] Sybase test query successful: " . json_encode($testResult));
      } catch (Throwable $e) {
        error_log("[UserListController] Sybase test query failed: " . $e->getMessage());
        throw $e;
      }
      
      // Query view untuk staf aktif sahaja (kodstatus = 1)
      // Note: Sybase requires explicit conversion - convert kodstatus to INT or compare with string
      $sql = "
        SELECT 
          nopekerja,
          idpekerja,
          gelar_nama,
          nama,
          nokp,
          email,
          handphone,
          kdjwtsemasa,
          jawatansemasa,
          kdjenis,
          jenis,
          kdjbtnsemasa,
          jabatansemasa,
          kumpjwt,
          kodstatus,
          status
        FROM v630staf_service_skim_all
        WHERE CONVERT(INT, kodstatus) = 1
      ";
      
      error_log("[UserListController] Executing query: " . substr($sql, 0, 100) . "...");
      
      $stmt = $pdoSybase->prepare($sql);
      $stmt->execute();
      $sybaseUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $sybaseCount = count($sybaseUsers);
      error_log("[UserListController] Fetched {$sybaseCount} active staff from Sybase");
      $this->syncDebug['sybase_count'] = $sybaseCount;
      
      // Log sample data untuk debugging
      if (!empty($sybaseUsers)) {
        $sample = $sybaseUsers[0];
        error_log("[UserListController] Sample data: nopekerja=" . ($sample['nopekerja'] ?? 'NULL') . ", nama=" . ($sample['gelar_nama'] ?? 'NULL'));
        $this->syncDebug['sample_nopekerja'] = $sample['nopekerja'] ?? 'NULL';
      }
      
      if (empty($sybaseUsers)) {
        error_log("[UserListController] No data from Sybase to sync");
        $this->syncDebug['error'] = 'No data from Sybase';
        return; // Tiada data untuk sync
      }
      
      // Prepare UPDATE statement untuk MySQL
      // Ambil staf ID yang login untuk f_updateby
      $loggedInStafID = $_SESSION['f_stafID'] ?? null;
      $remarks = 'Sync from Sybase (v630staf_service_skim_all) on page load';
      
      $updateSql = "
        UPDATE tbl_m_user SET
          f_nopekerja = :idpekerja,
          f_nama = :gelar_nama,
          f_nickname = :nama,
          f_nokp = :nokp,
          f_email = :email,
          f_handphone = :handphone,
          f_jawatanKod = :kdjwtsemasa,
          f_jawatan = :jawatansemasa,
          f_jenisID = :kdjenis,
          f_jenis = :jenis,
          f_jabatanKod = :kdjbtnsemasa,
          f_namajabatan = :jabatansemasa,
          f_kumpjawatan = :kumpjwt,
          f_statusID = :kodstatus,
          f_status = :status,
          f_updatedt = NOW(),
          f_updateby = :updateby,
          f_remarks = :remarks
        WHERE f_stafID = :nopekerja
      ";
      
      // Helper: normalize stafID untuk matching (remove dashes, trim)
      $normalizeStafID = function($id) {
        return str_replace('-', '', trim((string)$id));
      };
      
      // Ambil semua f_stafID yang wujud dalam MySQL (optimize: sekali query sahaja)
      // Store both original and normalized for matching
      $existingStafIDs = [];
      $existingStafIDsNormalized = [];
      $checkAllSql = "SELECT f_stafID FROM tbl_m_user";
      $checkAllStmt = $this->pdo->query($checkAllSql);
      while ($row = $checkAllStmt->fetch(PDO::FETCH_ASSOC)) {
        $original = trim((string)($row['f_stafID'] ?? ''));
        $normalized = $normalizeStafID($original);
        $existingStafIDs[$original] = $original; // Store original for UPDATE WHERE clause
        $existingStafIDsNormalized[$normalized] = $original; // Store normalized for matching
      }
      
      $mysqlCount = count($existingStafIDs);
      error_log("[UserListController] Found {$mysqlCount} existing users in MySQL");
      $this->syncDebug['mysql_count'] = $mysqlCount;
      
      $updateStmt = $this->pdo->prepare($updateSql);
      $updatedCount = 0;
      $skippedCount = 0;
      $errorCount = 0;
      
      // Update setiap record yang match
      foreach ($sybaseUsers as $sybaseUser) {
        $nopekerja = trim((string)($sybaseUser['nopekerja'] ?? ''));
        
        if (empty($nopekerja)) {
          $skippedCount++;
          continue; // Skip jika nopekerja kosong
        }
        
        // Normalize untuk matching (remove dashes)
        $nopekerjaNormalized = $normalizeStafID($nopekerja);
        
        // Check jika record wujud dalam MySQL (guna normalized lookup)
        if (!isset($existingStafIDsNormalized[$nopekerjaNormalized])) {
          $skippedCount++;
          continue; // Skip jika tidak wujud (tidak INSERT)
        }
        
        // Get original f_stafID from MySQL untuk UPDATE WHERE clause
        $mysqlStafID = $existingStafIDsNormalized[$nopekerjaNormalized];
        
        // Update record (gunakan original MySQL f_stafID untuk WHERE clause)
        try {
          $result = $updateStmt->execute([
            ':nopekerja' => $mysqlStafID, // Use original MySQL f_stafID for WHERE clause
            ':idpekerja' => $sybaseUser['idpekerja'] ?? null, // idpekerja -> f_nopekerja
            ':gelar_nama' => $sybaseUser['gelar_nama'] ?? null,
            ':nama' => $sybaseUser['nama'] ?? null,
            ':nokp' => $sybaseUser['nokp'] ?? null,
            ':email' => $sybaseUser['email'] ?? null,
            ':handphone' => $sybaseUser['handphone'] ?? null,
            ':kdjwtsemasa' => $sybaseUser['kdjwtsemasa'] ?? null,
            ':jawatansemasa' => $sybaseUser['jawatansemasa'] ?? null,
            ':kdjenis' => !empty($sybaseUser['kdjenis']) ? (int)$sybaseUser['kdjenis'] : null,
            ':jenis' => $sybaseUser['jenis'] ?? null,
            ':kdjbtnsemasa' => $sybaseUser['kdjbtnsemasa'] ?? null,
            ':jabatansemasa' => $sybaseUser['jabatansemasa'] ?? null,
            ':kumpjwt' => $sybaseUser['kumpjwt'] ?? null,
            ':kodstatus' => !empty($sybaseUser['kodstatus']) ? (int)$sybaseUser['kodstatus'] : null,
            ':status' => $sybaseUser['status'] ?? null,
            ':updateby' => $loggedInStafID,
            ':remarks' => $remarks,
          ]);
          
          if ($result) {
            $updatedCount++;
          } else {
            $errorCount++;
            error_log("[UserListController] Update failed for nopekerja: {$nopekerja}");
          }
        } catch (PDOException $e) {
          $errorCount++;
          error_log("[UserListController] Update error for nopekerja {$nopekerja}: " . $e->getMessage());
        }
      }
      
      // Log sync result dengan detail
      error_log("[UserListController] Sync completed - Updated: {$updatedCount}, Skipped: {$skippedCount}, Errors: {$errorCount}");
      
      $this->syncDebug['completed'] = date('Y-m-d H:i:s');
      $this->syncDebug['updated'] = $updatedCount;
      $this->syncDebug['skipped'] = $skippedCount;
      $this->syncDebug['errors'] = $errorCount;
      $this->syncDebug['status'] = 'success';
      
      // ✅ Audit: Log user sync operation (summary for bulk operation)
      try {
        if (function_exists('audit_event')) {
          $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;
          $sessionId = session_id() ?: null;
          
          // Derive numeric user_id for audit (prefer f_userID then parse staff no; DB fallback)
          $userId = null;
          if (!empty($_SESSION['user']['f_userID']) && is_numeric($_SESSION['user']['f_userID'])) {
            $userId = (int)$_SESSION['user']['f_userID'];
          } elseif (!empty($_SESSION['f_userID']) && is_numeric($_SESSION['f_userID'])) {
            $userId = (int)$_SESSION['f_userID'];
          } else {
            $cand = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? $_SESSION['f_stafID'] ?? null;
            if ($cand) {
              if (is_numeric($cand)) $userId = (int)$cand;
              elseif (preg_match('/^(\d+)/', (string)$cand, $m)) $userId = (int)$m[1];
            }
            if ($userId === null && !empty($_SESSION['f_stafID'])) {
              try {
                $up = (new User($this->pdo))->getProfile($_SESSION['f_stafID']);
                if (!empty($up['f_nopekerja'])) {
                  $c = $up['f_nopekerja'];
                  if (is_numeric($c)) $userId = (int)$c;
                  elseif (preg_match('/^(\d+)/', (string)$c, $m2)) $userId = (int)$m2[1];
                }
              } catch (Throwable $e) {
                error_log('[UserListController] user_id derivation DB lookup failed: ' . $e->getMessage());
              }
            }
          }
          
          // Format actor_label
          $nama = $_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null;
          $nostaf = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? null;
          $actorLabel = null;
          if (function_exists('audit_format_actor_label')) {
            $actorLabel = audit_format_actor_label($nama, $nostaf);
          } else {
            $actorLabel = $nama;
          }
          
          // Format message
          $message = audit_format_message('User sync from Sybase completed (auto)', $actorLabel);
          
          audit_event([
            'event_type'  => 'UPDATE',
            'severity'    => 'INFO',
            'outcome'     => ($errorCount > 0) ? 'PARTIAL' : 'SUCCESS',
            'target_type' => 'user_sync',
            'target_id'   => 'bulk_sync',
            'target_label' => 'User Sync (Auto)',
            'message'     => $message,
            'request_id'  => $requestId,
            'session_id'  => $sessionId,
            'user_id'     => $userId,
            'actor_label' => $actorLabel,
            'meta'        => [
              'sync_type' => 'auto',
              'source' => 'v630staf_service_skim_all',
              'updated_count' => $updatedCount,
              'skipped_count' => $skippedCount,
              'error_count' => $errorCount,
              'total_from_sybase' => $sybaseCount,
              'total_in_mysql' => $mysqlCount
            ]
          ]);
        }
      } catch (\Throwable $auditError) {
        error_log('[UserListController::syncUsersFromSybase] Audit error: ' . $auditError->getMessage());
        // Don't block sync if audit fails
      }
      
    } catch (Throwable $e) {
      // Graceful error handling: jika Sybase tidak available, skip sahaja
      // Jangan block page load jika sync gagal
      $errorMsg = $e->getMessage();
      error_log("[UserListController] Sync failed: " . $errorMsg);
      error_log("[UserListController] Stack trace: " . $e->getTraceAsString());
      
      $this->syncDebug['error'] = $errorMsg;
      $this->syncDebug['status'] = 'failed';
      $this->syncDebug['failed_at'] = date('Y-m-d H:i:s');
      
      // Continue execution - page tetap boleh load
    }
  }

  /**
   * Manual sync method untuk AJAX call
   * Returns array with success status and details
   */
  public function syncUsersFromSybaseManual(): array {
    $this->syncDebug = ['started' => date('Y-m-d H:i:s')];
    
    try {
      error_log("[UserListController] Starting manual sync from Sybase...");
      $this->syncDebug['step'] = 'Connecting to Sybase...';
      
      // Connect ke Sybase aktif
      $pdoSybase = Database::pdoSybaseActive();
      error_log("[UserListController] Sybase connection successful");
      $this->syncDebug['step'] = 'Sybase connected';
      
      // Query view untuk staf aktif sahaja (kodstatus = 1)
      $sql = "
        SELECT 
          nopekerja,
          idpekerja,
          gelar_nama,
          nama,
          nokp,
          email,
          handphone,
          kdjwtsemasa,
          jawatansemasa,
          kdjenis,
          jenis,
          kdjbtnsemasa,
          jabatansemasa,
          kumpjwt,
          kodstatus,
          status
        FROM v630staf_service_skim_all
        WHERE CONVERT(INT, kodstatus) = 1
      ";
      
      $stmt = $pdoSybase->prepare($sql);
      $stmt->execute();
      $sybaseUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $sybaseCount = count($sybaseUsers);
      error_log("[UserListController] Fetched {$sybaseCount} active staff from Sybase");
      $this->syncDebug['sybase_count'] = $sybaseCount;
      
      if (empty($sybaseUsers)) {
        return [
          'success' => true,
          'message' => 'Tiada data dari Sybase untuk disync.',
          'updated' => 0,
          'skipped' => 0,
          'errors' => 0,
          'total' => 0
        ];
      }
      
      // Prepare UPDATE statement untuk MySQL
      $loggedInStafID = $_SESSION['f_stafID'] ?? null;
      $remarks = 'Manual sync from Sybase (v630staf_service_skim_all)';
      
      $updateSql = "
        UPDATE tbl_m_user SET
          f_nopekerja = :idpekerja,
          f_nama = :gelar_nama,
          f_nickname = :nama,
          f_nokp = :nokp,
          f_email = :email,
          f_handphone = :handphone,
          f_jawatanKod = :kdjwtsemasa,
          f_jawatan = :jawatansemasa,
          f_jenisID = :kdjenis,
          f_jenis = :jenis,
          f_jabatanKod = :kdjbtnsemasa,
          f_namajabatan = :jabatansemasa,
          f_kumpjawatan = :kumpjwt,
          f_statusID = :kodstatus,
          f_status = :status,
          f_updatedt = NOW(),
          f_updateby = :updateby,
          f_remarks = :remarks
        WHERE f_stafID = :nopekerja
      ";
      
      // Helper: normalize stafID untuk matching
      $normalizeStafID = function($id) {
        return str_replace('-', '', trim((string)$id));
      };
      
      // Ambil semua f_stafID yang wujud dalam MySQL
      $existingStafIDs = [];
      $existingStafIDsNormalized = [];
      $checkAllSql = "SELECT f_stafID FROM tbl_m_user";
      $checkAllStmt = $this->pdo->query($checkAllSql);
      while ($row = $checkAllStmt->fetch(PDO::FETCH_ASSOC)) {
        $original = trim((string)($row['f_stafID'] ?? ''));
        $normalized = $normalizeStafID($original);
        $existingStafIDs[$original] = $original;
        $existingStafIDsNormalized[$normalized] = $original;
      }
      
      $updateStmt = $this->pdo->prepare($updateSql);
      $updatedCount = 0;
      $skippedCount = 0;
      $errorCount = 0;
      
      // Update setiap record yang match
      foreach ($sybaseUsers as $sybaseUser) {
        $nopekerja = trim((string)($sybaseUser['nopekerja'] ?? ''));
        
        if (empty($nopekerja)) {
          $skippedCount++;
          continue;
        }
        
        $nopekerjaNormalized = $normalizeStafID($nopekerja);
        
        if (!isset($existingStafIDsNormalized[$nopekerjaNormalized])) {
          $skippedCount++;
          continue;
        }
        
        $mysqlStafID = $existingStafIDsNormalized[$nopekerjaNormalized];
        
        try {
          $result = $updateStmt->execute([
            ':nopekerja' => $mysqlStafID,
            ':idpekerja' => $sybaseUser['idpekerja'] ?? null,
            ':gelar_nama' => $sybaseUser['gelar_nama'] ?? null,
            ':nama' => $sybaseUser['nama'] ?? null,
            ':nokp' => $sybaseUser['nokp'] ?? null,
            ':email' => $sybaseUser['email'] ?? null,
            ':handphone' => $sybaseUser['handphone'] ?? null,
            ':kdjwtsemasa' => $sybaseUser['kdjwtsemasa'] ?? null,
            ':jawatansemasa' => $sybaseUser['jawatansemasa'] ?? null,
            ':kdjenis' => !empty($sybaseUser['kdjenis']) ? (int)$sybaseUser['kdjenis'] : null,
            ':jenis' => $sybaseUser['jenis'] ?? null,
            ':kdjbtnsemasa' => $sybaseUser['kdjbtnsemasa'] ?? null,
            ':jabatansemasa' => $sybaseUser['jabatansemasa'] ?? null,
            ':kumpjwt' => $sybaseUser['kumpjwt'] ?? null,
            ':kodstatus' => !empty($sybaseUser['kodstatus']) ? (int)$sybaseUser['kodstatus'] : null,
            ':status' => $sybaseUser['status'] ?? null,
            ':updateby' => $loggedInStafID,
            ':remarks' => $remarks,
          ]);
          
          if ($result) {
            $updatedCount++;
          } else {
            $errorCount++;
          }
        } catch (PDOException $e) {
          $errorCount++;
          error_log("[UserListController] Update error for nopekerja {$nopekerja}: " . $e->getMessage());
        }
      }
      
      $this->syncDebug['completed'] = date('Y-m-d H:i:s');
      $this->syncDebug['updated'] = $updatedCount;
      $this->syncDebug['skipped'] = $skippedCount;
      $this->syncDebug['errors'] = $errorCount;
      $this->syncDebug['status'] = 'success';
      
      // ✅ Audit: Log user sync operation (summary for bulk operation)
      try {
        if (function_exists('audit_event')) {
          $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;
          $sessionId = session_id() ?: null;
          
          // Derive numeric user_id for audit (prefer f_userID then parse staff no; DB fallback)
          $userId = null;
          if (!empty($_SESSION['user']['f_userID']) && is_numeric($_SESSION['user']['f_userID'])) {
            $userId = (int)$_SESSION['user']['f_userID'];
          } elseif (!empty($_SESSION['f_userID']) && is_numeric($_SESSION['f_userID'])) {
            $userId = (int)$_SESSION['f_userID'];
          } else {
            $cand = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? $_SESSION['f_stafID'] ?? null;
            if ($cand) {
              if (is_numeric($cand)) $userId = (int)$cand;
              elseif (preg_match('/^(\d+)/', (string)$cand, $m)) $userId = (int)$m[1];
            }
            if ($userId === null && !empty($_SESSION['f_stafID'])) {
              try {
                $up = (new User($this->pdo))->getProfile($_SESSION['f_stafID']);
                if (!empty($up['f_nopekerja'])) {
                  $c = $up['f_nopekerja'];
                  if (is_numeric($c)) $userId = (int)$c;
                  elseif (preg_match('/^(\d+)/', (string)$c, $m2)) $userId = (int)$m2[1];
                }
              } catch (Throwable $e) {
                error_log('[UserListController] user_id derivation DB lookup failed: ' . $e->getMessage());
              }
            }
          }
          
          // Format actor_label
          $nama = $_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null;
          $nostaf = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? null;
          $actorLabel = null;
          if (function_exists('audit_format_actor_label')) {
            $actorLabel = audit_format_actor_label($nama, $nostaf);
          } else {
            $actorLabel = $nama;
          }
          
          // Get MySQL count
          $mysqlCount = count($existingStafIDs);
          
          // Format message
          $message = audit_format_message('User sync from Sybase completed (manual)', $actorLabel);
          
          audit_event([
            'event_type'  => 'UPDATE',
            'severity'    => 'INFO',
            'outcome'     => ($errorCount > 0) ? 'PARTIAL' : 'SUCCESS',
            'target_type' => 'user_sync',
            'target_id'   => 'bulk_sync',
            'target_label' => 'User Sync (Manual)',
            'message'     => $message,
            'request_id'  => $requestId,
            'session_id'  => $sessionId,
            'user_id'     => $userId,
            'actor_label' => $actorLabel,
            'meta'        => [
              'sync_type' => 'manual',
              'source' => 'v630staf_service_skim_all',
              'updated_count' => $updatedCount,
              'skipped_count' => $skippedCount,
              'error_count' => $errorCount,
              'total_from_sybase' => $sybaseCount,
              'total_in_mysql' => $mysqlCount
            ]
          ]);
        }
      } catch (\Throwable $auditError) {
        error_log('[UserListController::syncUsersFromSybaseManual] Audit error: ' . $auditError->getMessage());
        // Don't block sync if audit fails
      }
      
      return [
        'success' => true,
        'message' => "Sync berjaya. {$updatedCount} rekod dikemas kini, {$skippedCount} rekod dilangkau, {$errorCount} ralat.",
        'updated' => $updatedCount,
        'skipped' => $skippedCount,
        'errors' => $errorCount,
        'total' => $sybaseCount
      ];
      
    } catch (Throwable $e) {
      $errorMsg = $e->getMessage();
      error_log("[UserListController] Manual sync failed: " . $errorMsg);
      
      return [
        'success' => false,
        'message' => 'Gagal sync data dari Sybase: ' . $errorMsg,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'total' => 0
      ];
    }
  }

  public function importStudentsFromSybase(): array {
    $this->syncDebug = ['started' => date('Y-m-d H:i:s')];
    
    try {
      error_log("[UserListController] Starting manual import from Sybase...");
      $this->syncDebug['step'] = 'Connecting to Sybase...';
      
      // Connect ke Sybase aktif
      $pdoSybase = Database::pdoSybaseAsis();
      error_log("[UserListController] Sybase connection successful");
      $this->syncDebug['step'] = 'Sybase connected';
      
      // Query view untuk sudent aktif sahaja (statuskategori = AKTIF)
      // tidak auto sekat pelajar yang sudah tamat pengajian sebab kita nak import semua sekali.
      $sql = "
        SELECT 
          matrik,
          nama,
          nokp,
          notentera,
          email,
          alfateh,
          case when notel_terkini is not null then notel_terkini else hpno end as handphone,
          fakulti,
          statuskategori
        FROM v210
        WHERE statuskategori = 'AKTIF'
      ";
      
      $stmt = $pdoSybase->prepare($sql);
      $stmt->execute();
      $sybaseUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $sybaseCount = count($sybaseUsers);
      error_log("[UserListController] Fetched {$sybaseCount} active students from Sybase");
      $this->syncDebug['sybase_count'] = $sybaseCount;
      
      if (empty($sybaseUsers)) {
        return [
          'success' => true,
          'message' => 'Tiada data dari Sybase untuk diimport.',
          'updated' => 0,
          'skipped' => 0,
          'errors' => 0,
          'total' => 0
        ];
      }
      
      // Prepare UPDATE statement untuk MySQL
      // = : hanya untuk placeholder, bukan untuk value 
      $loggedInStafID = $_SESSION['f_stafID'] ?? null;
      $remarks = 'Manual import student from Sybase (v210)';

      // Set charset supaya UTF-8 safe
      $this->pdo->exec("SET NAMES utf8mb4");
      $this->pdo->exec("SET CHARACTER SET utf8mb4");

      $insertSql = "
      INSERT INTO tbl_m_user (
        f_stafID,
        f_nopekerja,
        f_groupID,
        f_groupKod,
        f_nama,
        f_nickname,
        f_nokp,
        f_email,
        f_handphone,
        f_jawatanKod,
        f_jawatan,
        f_jenisID,
        f_jenis,
        f_jabatanKod,
        f_namajabatan,
        f_kumpjawatan,
        f_statusID,
        f_status,
        f_password,
        f_flag,
        f_insertdt,
        f_updateby,
        f_remarks
      ) VALUES %s
        ON DUPLICATE KEY UPDATE
        f_nama = VALUES(f_nama),
        f_nokp = VALUES(f_nokp),
        f_email = VALUES(f_email),
        f_handphone = VALUES(f_handphone),
        f_status = VALUES(f_status),
        f_updatedt = NOW(),
        f_updateby = VALUES(f_updateby)

      "; //ON DUPLICATE KEY UPDATE : column untuk dikemaskini sekiranya id dah ada
      
      // Helper: normalize stafID untuk matching
      $normalizeStafID = function($id) {
        return str_replace('-', '', trim((string)$id));
      };
      
      // Ambil semua f_stafID yang wujud dalam MySQL
      $existingStafIDs = [];
      $existingStafIDsNormalized = [];
      $checkAllSql = "SELECT f_stafID FROM tbl_m_user";
      $checkAllStmt = $this->pdo->query($checkAllSql);
      while ($row = $checkAllStmt->fetch(PDO::FETCH_ASSOC)) {
        $original = trim((string)($row['f_stafID'] ?? ''));
        $normalized = $normalizeStafID($original);
        $existingStafIDs[$original] = $original;
        $existingStafIDsNormalized[$normalized] = $original;
      }

      $insertedCount = 0;
      $skippedCount = 0;
      $errorCount = 0;
      
      // Update setiap record yang match nopekerja (nomatrik)
      $values = [];
      $params = [];

      foreach ($sybaseUsers as $sybaseUser) {
        $matrik = trim((string)($sybaseUser['matrik'] ?? ''));

        if (empty($matrik)) {
          $skippedCount++;
          continue;
        }

        // password dari NOKP
        $nokp = $sybaseUser['nokp'] ?? null;
        $passwordHashes = [];
        foreach ($sybaseUsers as $user) {
            $nokp = $user['nokp'] ?? '';
            $passwordHashes[$user['matrik']] = !empty($nokp) ? hash('sha256', $nokp) : null;
        }

        $values[] = "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $params[] = $matrik;
        $params[] = $matrik;
        $params[] = 27; // groupID untuk pemohon
        $params[] = 'APPLICANT'; // groupKod untuk pemohon
        $params[] = $sybaseUser['nama'] ?? null;
        $params[] = $sybaseUser['nama'] ?? null;
        $params[] = $sybaseUser['nokp'] ?? null;
        $params[] = $sybaseUser['alfateh'] ?? null;
        $params[] = $sybaseUser['handphone'] ?? null;
        $params[] = null;
        $params[] = null;
        $params[] = 0;
        $params[] = 'Pelajar';
        $params[] = 0;
        $params[] = $sybaseUser['fakulti'] ?? null;
        $params[] = null;
        $params[] = 1;
        $params[] = $sybaseUser['statuskategori'] ?? null;
        $params[] = $passwordHashes[$matrik];
        $params[] = 1; // dibenarkan access
        $params[] = date('Y-m-d H:i:s'); 
        $params[] = $loggedInStafID;
        $params[] = $remarks;
      }

      // insert bulk record student dari sybase.
      if (!empty($values)) {
        $totalRows = count($values);
        $this->pdo->beginTransaction();

        try {
            $chunkSize = 200; // 200 row per batch, elakkan memory issue dan timeout untuk jumlah besar
            for ($i = 0; $i < $totalRows; $i += $chunkSize) {
                $chunkValues = array_slice($values, $i, $chunkSize);
                $chunkParams = array_slice($params, $i * 23, count($chunkValues) * 23); // 23 = jumlah column

                $sqlChunk = sprintf($insertSql, implode(',', $chunkValues));
                $stmt = $this->pdo->prepare($sqlChunk);
                $stmt->execute($chunkParams);

                error_log("[UserListController] Inserted rows " . ($i+1) . " to " . ($i + count($chunkValues)));
            }

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("[UserListController] Bulk insert error: " . $e->getMessage()); //. print_r($chunkParams, true) check params if needed
        }
      }

      $this->syncDebug['completed'] = date('Y-m-d H:i:s');
      $this->syncDebug['inserted'] = $insertedCount;
      $this->syncDebug['skipped'] = $skippedCount;
      $this->syncDebug['errors'] = $errorCount;
      $this->syncDebug['status'] = 'success';
      
      // ✅ Audit: Log user sync operation (summary for bulk operation)
      // try {
      //   if (function_exists('audit_event')) {
      //     $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;
      //     $sessionId = session_id() ?: null;
          
      //     // Derive numeric user_id for audit (prefer f_userID then parse staff no; DB fallback)
      //     $userId = null;
      //     if (!empty($_SESSION['user']['f_userID']) && is_numeric($_SESSION['user']['f_userID'])) {
      //       $userId = (int)$_SESSION['user']['f_userID'];
      //     } elseif (!empty($_SESSION['f_userID']) && is_numeric($_SESSION['f_userID'])) {
      //       $userId = (int)$_SESSION['f_userID'];
      //     } else {
      //       $cand = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? $_SESSION['f_stafID'] ?? null;
      //       if ($cand) {
      //         if (is_numeric($cand)) $userId = (int)$cand;
      //         elseif (preg_match('/^(\d+)/', (string)$cand, $m)) $userId = (int)$m[1];
      //       }
      //       if ($userId === null && !empty($_SESSION['f_stafID'])) {
      //         try {
      //           $up = (new User($this->pdo))->getProfile($_SESSION['f_stafID']);
      //           if (!empty($up['f_nopekerja'])) {
      //             $c = $up['f_nopekerja'];
      //             if (is_numeric($c)) $userId = (int)$c;
      //             elseif (preg_match('/^(\d+)/', (string)$c, $m2)) $userId = (int)$m2[1];
      //           }
      //         } catch (Throwable $e) {
      //           error_log('[UserListController] user_id derivation DB lookup failed: ' . $e->getMessage());
      //         }
      //       }
      //     }
          
      //     // Format actor_label
      //     $nama = $_SESSION['user']['f_nama'] ?? $_SESSION['f_nama'] ?? null;
      //     $nostaf = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? null;
      //     $actorLabel = null;
      //     if (function_exists('audit_format_actor_label')) {
      //       $actorLabel = audit_format_actor_label($nama, $nostaf);
      //     } else {
      //       $actorLabel = $nama;
      //     }
          
      //     // Get MySQL count
      //     $mysqlCount = count($existingStafIDs);
          
      //     // Format message
      //     $message = audit_format_message('User sync from Sybase completed (manual)', $actorLabel);
          
      //     audit_event([
      //       'event_type'  => 'UPDATE',
      //       'severity'    => 'INFO',
      //       'outcome'     => ($errorCount > 0) ? 'PARTIAL' : 'SUCCESS',
      //       'target_type' => 'user_sync',
      //       'target_id'   => 'bulk_sync',
      //       'target_label' => 'User Sync (Manual)',
      //       'message'     => $message,
      //       'request_id'  => $requestId,
      //       'session_id'  => $sessionId,
      //       'user_id'     => $userId,
      //       'actor_label' => $actorLabel,
      //       'meta'        => [
      //         'sync_type' => 'manual',
      //         'source' => 'v630staf_service_skim_all',
      //         'updated_count' => $updatedCount,
      //         'skipped_count' => $skippedCount,
      //         'error_count' => $errorCount,
      //         'total_from_sybase' => $sybaseCount,
      //         'total_in_mysql' => $mysqlCount
      //       ]
      //     ]);
      //   }
      // } catch (\Throwable $auditError) {
      //   error_log('[UserListController::syncUsersFromSybaseManual] Audit error: ' . $auditError->getMessage());
      //   // Don't block sync if audit fails
      // }
      
      return [
        'success' => true,
        'message' => "Import berjaya. {$insertedCount} rekod ditambah, {$skippedCount} rekod dilangkau, {$errorCount} ralat.",
        'updated' => $insertedCount,
        'skipped' => $skippedCount,
        'errors' => $errorCount,
        'total' => $sybaseCount
      ];
      
    } catch (Throwable $e) {
      $errorMsg = $e->getMessage();
      error_log("[UserListController] Student Import failed: " . $errorMsg);
      
      return [
        'success' => false,
        'message' => 'Gagal import data pelajar: ' . $errorMsg,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'total' => 0
      ];
    }
  }  
}
