<?php
// ajax/user-list-rows.php
// Return structured user rows for AJAX reload.
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

try {
    ob_start();
    require_once __DIR__ . '/../includes/init.php';
    $initOutput = ob_get_clean();
    require_once __DIR__ . '/_helpers.php';
    logAjaxUnexpectedOutput('user-list-rows:init.php', $initOutput);

    if (empty($_SESSION['f_stafID'])) {
        jsonErrorResponse((string)(__('unauthorized_access') ?: 'Sila log masuk terlebih dahulu.'), 401);
    }

    $_GET['manual_sync'] = true;

    ob_start();
    require_once __DIR__ . '/../controllers/UserListController.php';
    require_once __DIR__ . '/../classes/User.php';
    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../setting/constants/prestasi_constants.php';
    $requireOutput = ob_get_clean();
    logAjaxUnexpectedOutput('user-list-rows:requires', $requireOutput);

    ob_start();
    $controller = new UserListController();
    $controllerOutput = ob_get_clean();
    logAjaxUnexpectedOutput('user-list-rows:controller', $controllerOutput);
    
    $senaraiUser = $controller->senaraiUser ?? [];
    
    // User model untuk getAvatarUrl dan permission check
    $pdo = Database::getInstance('mysql')->getConnection();
    ensureAjaxGroupManagePermission($pdo);
    $userModel = new User($pdo);
    
    // Helper function format_stafid (h() already exists in html_helper.php)
    if (!function_exists('format_stafid')) {
        function format_stafid(?string $id): string {
            $id = trim((string)$id);
            $raw = str_replace('-', '', $id);
            if ($raw !== '' && ctype_digit($raw) && strlen($raw) === 6) {
                return substr($raw,0,4) . '-' . substr($raw,4,2);
            }
            return $id;
        }
    }

    if (!function_exists('normalize_identity_value')) {
        function normalize_identity_value(?string $value): string {
            return normalizeIdentityValue($value);
        }
    }

    if (!function_exists('is_current_logged_in_user_target')) {
        function is_current_logged_in_user_target(
            int $targetUserId,
            string $targetStafId,
            string $targetNoPekerja,
            int $currentUserId,
            string $currentUserStafIdNormalized,
            string $currentUserNoPekerjaNormalized
        ): bool {
            if ($currentUserId > 0 && $targetUserId > 0 && $targetUserId === $currentUserId) {
                return true;
            }

            $normalizedTargetStafId = normalize_identity_value($targetStafId);
            if ($currentUserStafIdNormalized !== '' && $normalizedTargetStafId !== '' && $normalizedTargetStafId === $currentUserStafIdNormalized) {
                return true;
            }

            $normalizedTargetNoPekerja = normalize_identity_value($targetNoPekerja);
            if ($currentUserNoPekerjaNormalized !== '' && $normalizedTargetNoPekerja !== '' && $normalizedTargetNoPekerja === $currentUserNoPekerjaNormalized) {
                return true;
            }

            return false;
        }
    }

    // Get current user's group for permission control
    $currentLoginID = $_SESSION['f_loginID'] ?? '';
    $currentStafID = $_SESSION['f_stafID'] ?? '';
    $currentProfile = $currentLoginID !== ''
        ? ($userModel->getProfileByLoginID((string)$currentLoginID) ?: [])
        : ($userModel->getProfile((string)$currentStafID) ?: []);
    $isADM_SA = $currentProfile && function_exists('is_user_super_admin') && is_user_super_admin($currentProfile, $pdo);
    $currentUserId = (int)($currentProfile['f_userID'] ?? 0);
    $currentUserStafIdNormalized = normalize_identity_value((string)($currentProfile['f_stafID'] ?? $currentStafID));
    $currentUserNoPekerjaNormalized = normalize_identity_value((string)($currentProfile['f_nopekerja'] ?? ''));

    if (!function_exists('user_list_scope_category')) {
        function user_list_scope_category(string $scope): string {
            return match (strtolower(trim($scope))) {
                'student', 'pelajar' => 'PELAJAR',
                'public', 'umum' => 'UMUM',
                default => 'STAF',
            };
        }
    }

    if (!function_exists('user_list_load_users_by_category')) {
        function user_list_load_users_by_category(PDO $pdo, string $category): array {
            $sql = "
                SELECT
                    u.f_userID,
                    u.f_loginID,
                    u.f_stafID,
                    u.f_nickname,
                    u.f_email,
                    u.f_handphone,
                    u.f_nokp,
                    u.f_nopekerja,
                    u.f_nama,
                    u.f_categoryUser,
                    u.f_namajabatan,
                    u.f_jawatan,
                    u.f_status,
                    u.f_flag,
                    COALESCE(u.f_isAutoProvisioned, 0) AS f_isAutoProvisioned,
                    TRIM(COALESCE(u.f_identitySource, '')) AS f_identitySource,
                    u.f_groupID,
                    TRIM(u.f_groupKod) AS f_groupKod,
                    COALESCE(NULLIF(TRIM(g.f_groupName), ''), TRIM(u.f_groupKod)) AS f_groupName
                FROM tbl_m_user u
                LEFT JOIN tbl_m_group g
                    ON g.f_groupID = u.f_groupID
                WHERE COALESCE(u.f_statusID,0) <> 9
                  AND TRIM(COALESCE(u.f_categoryUser, '')) = :category
                ORDER BY u.f_nama ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':category' => $category]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (!$rows) {
                return [];
            }

            $userIds = [];
            $stafIds = [];
            foreach ($rows as $u) {
                $uid = (int)($u['f_userID'] ?? 0);
                if ($uid > 0) {
                    $userIds[] = $uid;
                    continue;
                }
                $sid = trim((string)($u['f_stafID'] ?? ''));
                if ($sid !== '') {
                    $stafIds[] = $sid;
                }
            }

            $mapByUserId = [];
            $userIds = array_values(array_unique($userIds));
            if ($userIds) {
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $sqlExtraByUser = "
                    SELECT a.f_userID, g.f_groupName
                    FROM tbl_ref_access a
                    JOIN tbl_m_group g ON g.f_groupID = a.f_groupID
                    JOIN tbl_m_user u ON u.f_userID = a.f_userID
                    WHERE a.f_status = 1
                      AND a.f_userID IN ($placeholders)
                      AND a.f_groupID <> u.f_groupID
                      AND TRIM(COALESCE(g.f_categoryUser, '')) = ?
                    ORDER BY g.f_groupName ASC
                ";
                $stmtExtra = $pdo->prepare($sqlExtraByUser);
                $params = $userIds;
                $params[] = $category;
                $stmtExtra->execute($params);
                foreach (($stmtExtra->fetchAll(PDO::FETCH_ASSOC) ?: []) as $r) {
                    $uid = (int)($r['f_userID'] ?? 0);
                    $name = trim((string)($r['f_groupName'] ?? ''));
                    if ($uid > 0 && $name !== '') {
                        $mapByUserId[$uid][] = $name;
                    }
                }
            }

            $mapByStafId = [];
            $stafIds = array_values(array_unique($stafIds));
            if ($stafIds) {
                $placeholders = implode(',', array_fill(0, count($stafIds), '?'));
                $sqlExtra = "
                    SELECT a.f_stafID, g.f_groupName
                    FROM tbl_ref_access a
                    JOIN tbl_m_group g ON g.f_groupID = a.f_groupID
                    JOIN tbl_m_user u ON u.f_stafID = a.f_stafID
                    WHERE a.f_status = 1
                      AND a.f_userID IS NULL
                      AND a.f_stafID IN ($placeholders)
                      AND a.f_groupID <> u.f_groupID
                      AND TRIM(COALESCE(g.f_categoryUser, '')) = ?
                    ORDER BY g.f_groupName ASC
                ";
                $stmtExtra = $pdo->prepare($sqlExtra);
                $params = $stafIds;
                $params[] = $category;
                $stmtExtra->execute($params);
                foreach (($stmtExtra->fetchAll(PDO::FETCH_ASSOC) ?: []) as $r) {
                    $sid = trim((string)($r['f_stafID'] ?? ''));
                    $name = trim((string)($r['f_groupName'] ?? ''));
                    if ($sid !== '' && $name !== '') {
                        $mapByStafId[$sid][] = $name;
                    }
                }
            }

            foreach ($rows as &$u) {
                $uid = (int)($u['f_userID'] ?? 0);
                $sid = trim((string)($u['f_stafID'] ?? ''));
                $extra = $uid > 0 ? ($mapByUserId[$uid] ?? []) : ($mapByStafId[$sid] ?? []);
                $u['extra_roles'] = $extra;
                $u['extra_roles_count'] = count($extra);
            }
            unset($u);

            return $rows;
        }
    }

    $scope = strtolower(trim((string)($_GET['scope'] ?? 'staff')));
    $category = user_list_scope_category($scope);
    if ($category !== 'STAF') {
        if ($category === 'PELAJAR' && function_exists('is_student_mode_enabled') && !is_student_mode_enabled()) {
            jsonErrorResponse((string)__('studentSearch_mode_disabled'), 403);
        }
        $senaraiUser = user_list_load_users_by_category($pdo, $category);
    }

    // Group style map (data-driven): centralized helper
    $groupUiMaps = ['by_id' => [], 'by_code' => []];
    try {
        $groupUiMaps = prestasi_group_ui_load_maps($pdo);
    } catch (Throwable $e) {
        error_log('[user-list-rows] Group style map load failed: ' . $e->getMessage());
    }
    
    // Generate structured rows
    $rows = [];
    if (!empty($senaraiUser)) {
        foreach ($senaraiUser as $u) {
            $userID  = (int)($u['f_userID'] ?? 0);
            $nama    = (string)($u['f_nama'] ?? '');
            $loginID = trim((string)($u['f_loginID'] ?? ''));
            $stafID  = format_stafid((string)($u['f_stafID'] ?? ''));
            $nickname = trim((string)($u['f_nickname'] ?? ''));
            $email = trim((string)($u['f_email'] ?? ''));
            $phone = trim((string)($u['f_handphone'] ?? ''));
            $nokp = trim((string)($u['f_nokp'] ?? ''));
            $jabatan = (string)($u['f_namajabatan'] ?? '');
            $jawatan = (string)($u['f_jawatan'] ?? '');
            $gId     = (int)($u['f_groupID'] ?? 0);
            $gKod    = (string)($u['f_groupKod'] ?? '');
            $gName   = (string)($u['f_groupName'] ?? $gKod);
            $extraRoles = $u['extra_roles'] ?? [];
            if (!is_array($extraRoles)) $extraRoles = [];
            $extraCount = (int)($u['extra_roles_count'] ?? count($extraRoles));
            $f_flag  = (int)($u['f_flag'] ?? 1);
            $f_nopekerja = (string)($u['f_nopekerja'] ?? '');
            $avatarUrl = $userModel->resolveAvatarUrl($u);
            $isAutoProvisioned = (int)($u['f_isAutoProvisioned'] ?? 0) === 1;
            $identitySource = strtoupper(trim((string)($u['f_identitySource'] ?? '')));
            $isCurrentLoggedInUser = is_current_logged_in_user_target(
                $userID,
                $stafID,
                $f_nopekerja,
                $currentUserId,
                $currentUserStafIdNormalized,
                $currentUserNoPekerjaNormalized
            );
            $isProtectedAccount = isProtectedStaffAccount($stafID);
            $canManageProtectedSelf = canSelfManageProtectedStaffAccount($stafID);
            $canEditGroup = $isADM_SA && (!$isProtectedAccount || $canManageProtectedSelf);
            $canDeleteUser = $isADM_SA && !$isCurrentLoggedInUser && !$isProtectedAccount;
            
            $style = prestasi_group_ui_resolve($groupUiMaps, $gId, $gKod);
            $badgeClass = (string)($style['badgeClass'] ?? 'bg-secondary');
            $rowClass = (string)($style['rowClass'] ?? '');
            $rowColor = (string)($style['rowColor'] ?? '');

            $rows[] = [
                'f_userID' => $userID,
                'f_nama' => $nama,
                'f_loginID' => $loginID,
                'f_stafID' => $stafID,
                'f_nickname' => $nickname,
                'f_email' => $email,
                'f_handphone' => $phone,
                'f_nokp' => $nokp,
                'f_categoryUser' => (string)($u['f_categoryUser'] ?? ''),
                'f_isAutoProvisioned' => $isAutoProvisioned ? 1 : 0,
                'f_identitySource' => $identitySource,
                'f_namajabatan' => $jabatan,
                'f_jawatan' => $jawatan,
                'f_groupID' => $gId,
                'f_groupKod' => $gKod,
                'f_groupName' => $gName,
                'f_badge_class' => $badgeClass,
                'f_row_class' => $rowClass,
                'f_row_color' => $rowColor,
                'extra_roles' => $extraRoles,
                'extra_roles_count' => $extraCount,
                'f_flag' => $f_flag,
                'f_nopekerja' => $f_nopekerja,
                'avatarUrl' => $avatarUrl,
                'is_current_logged_in_user' => $isCurrentLoggedInUser,
                'is_protected_account' => $isProtectedAccount,
                'can_edit_group' => $canEditGroup,
                'can_delete_user' => $canDeleteUser
            ];
        }
    }
    
    jsonSuccessResponse([
        'rows' => $rows,
        'count' => count($senaraiUser)
    ]);
    
} catch (Throwable $e) {
    error_log("[user-list-rows] Fatal: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    jsonErrorResponse('Ralat server. Sila hubungi pentadbir sistem.', 500);
}
