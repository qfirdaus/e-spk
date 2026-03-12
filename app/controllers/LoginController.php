<?php
// controllers/LoginController.php
declare(strict_types=1);

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Config.php';

// Helper audit (audit_event, audit_request_bind_user, dll) di-autoload melalui init.php

class LoginController
{
    private const STUDENT_GROUP_ID = 27; //Pemohon - pastikan wujud dalam tbl_m_group untuk consistency dengan sistem sedia ada.
    private const STUDENT_AVATAR_BASE_URL = 'https://kemasukan.upnm.edu.my/tawaran/pelajar/student_image/';

    private User $userModel;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance('mysql')->getConnection();
        $this->userModel = new User($this->pdo);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Sahkan pengguna berdasarkan f_stafID + (optional) kata laluan
     *  If $password is null/empty, treat as SSO-authenticated flow (no local password check).
     */
    public function authenticate(string $f_stafID, ?string $password = null): bool
    {
        // Normalisasi input
        $f_stafID = trim($f_stafID);
        if ($f_stafID === '') {
            $this->auditLoginFail($f_stafID, 'empty_input');
            return false;
        }

        // Cari user
        $user = $this->userModel->findByStafID($f_stafID);
        if (!$user) {
            // Fallback sementara sebelum SSO:
            // Benarkan login pelajar menggunakan matrik + IC (nokp) dari view v210.
            if ($this->authenticateStudentLocal($f_stafID, $password)) {
                return true;
            }
            $this->auditLoginFail($f_stafID, 'user_not_found');
            return false;
        }

        // Jika password disediakan, semak password seperti biasa.
        // Jika tidak disediakan (SSO flow), skip password verification.
        if ($password !== null && $password !== '') {
            if (!password_verify($password, $user['f_password'])) {
                $this->auditLoginFail($f_stafID, 'wrong_password', $user);
                return false;
            }
        }

        // 🔒 Semak f_flag - jika 0, sekat akses
        $f_flag = (int)($user['f_flag'] ?? 1); // Default 1 jika NULL
        if ($f_flag !== 1) {
            $this->auditLoginFail($f_stafID, 'access_blocked', $user);
            // Throw exception dengan specific message untuk access blocked
            throw new \RuntimeException('ACCESS_BLOCKED');
        }

        // 🔒 Kuatkan sesi
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        session_regenerate_id(true);

        // Dapatkan user_id dari f_nopekerja (no staf) untuk audit
        $nopekerja = $user['f_nopekerja'] ?? null;
        
        // ✅ FIX: Jika f_nopekerja tidak lengkap (cth: "530" bukan "0530-09"), guna f_stafID sebagai fallback
        // f_stafID biasanya dalam format lengkap "0530-09" yang betul untuk audit
        $f_stafID = $user['f_stafID'] ?? null;
        if ($nopekerja && !preg_match('/^\d{4}-\d{2}$/', $nopekerja) && $f_stafID && preg_match('/^\d{4}-\d{2}$/', $f_stafID)) {
            // f_nopekerja tidak lengkap, guna f_stafID yang lengkap
            $nopekerja = $f_stafID;
        }
        
        // Derive short numeric user_id from possible formats like "0530-09" or "530"
        $userId = null;
        if ($nopekerja && preg_match('/^(\\d+)/', $nopekerja, $m)) {
            // cast leading digits to int ("0530" -> 530)
            $userId = (int)$m[1];
        }

        // 👤 Simpan maklumat asas user (minimal & selamat)
        $_SESSION['f_stafID']    = $f_stafID;
        // ✅ FIX: Simpan f_nopekerja yang lengkap (atau f_stafID jika f_nopekerja tidak lengkap)
        $_SESSION['f_nopekerja'] = $nopekerja;
        $_SESSION['f_nama']      = $user['f_nama'] ?? ($user['f_nickname'] ?? '');
        $_SESSION['f_nickname']  = $user['f_nickname'] ?? '';
        $_SESSION['f_groupID']   = (int)($user['f_groupID'] ?? 0);
        $_SESSION['f_groupKod']  = $user['f_groupKod'] ?? '';

        // Tambah payload standard untuk kegunaan umum
        // Resolve persistent numeric user id for backward compatibility.
        $resolvedUserId = $this->resolveUserId($user);
        if ($resolvedUserId === 0 && $userId !== null) {
            // If DB row does not contain a numeric PK, fall back to derived numeric from staff no.
            $resolvedUserId = (int)$userId;
        }
        // Expose top-level f_userID for other code that expects it
        $_SESSION['f_userID'] = $resolvedUserId;

        $_SESSION['user'] = [
            'f_userID'     => $resolvedUserId,
            'f_nopekerja'  => $nopekerja,
            'f_nama'       => $_SESSION['f_nama'],
            'f_nickname'   => $_SESSION['f_nickname'],
            'f_groupID'    => $_SESSION['f_groupID'],
            'f_groupKod'   => $_SESSION['f_groupKod'],
            'f_groupName'  => $user['f_groupName'] ?? null,
        ];

        // 🎯 Add-ons: theme/lang daripada profile — balut
        try {
            $profile = $this->userModel->getProfile($f_stafID) ?? [];
            if (!empty($profile['f_themeSetting'])) {
                $theme = json_decode($profile['f_themeSetting'], true);
                if (is_array($theme)) {
                    $_SESSION['theme.menu']   = $theme['sidebarColor'] ?? ($_SESSION['theme.menu'] ?? 'light');
                    $_SESSION['theme.topbar'] = $theme['topbarColor']  ?? ($_SESSION['theme.topbar'] ?? 'light');
                    $_SESSION['theme.layout'] = $theme['layoutMode']   ?? ($_SESSION['theme.layout'] ?? 'light');
                }
            }
            if (!empty($profile['f_lang']) && in_array($profile['f_lang'], ['ms','en','zh','ta'], true)) {
                $_SESSION['lang'] = $profile['f_lang'];
            }
        } catch (\Throwable $e) {
            error_log('AUTH PROFILE/THEME WARN: ' . $e->getMessage());
        }

        // 🗄️ SYBASE_ACTIVE_BASE — balut
        try {
            $config = new Config($this->pdo);
            $activeBase = $config->getSybaseActiveBase(null);
            if ($activeBase) $_SESSION['SYBASE_ACTIVE_BASE'] = $activeBase;
        } catch (\Throwable $e) {
            error_log('AUTH CONFIG WARN: ' . $e->getMessage());
        }

        // 🕒 Last login — balut
        try {
            if (method_exists($this->userModel, 'touchLastLogin')) {
                $this->userModel->touchLastLogin($f_stafID);
            }
        } catch (\Throwable $e) {
            error_log('AUTH LASTLOGIN WARN: ' . $e->getMessage());
        }

        // 🧾 AUDIT: session + event LOGIN SUCCESS
        $this->auditLoginSuccess($user, $userId);

        // 🔗 PAKSA BIND audit_request → user_id + route 'auth/login'
        try {
            $rid = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null; // set dalam init.php
            if ($userId && function_exists('audit_request_bind_user')) {
                audit_request_bind_user($userId, $rid);
            }
            if (function_exists('audit_request_set_route')) {
                audit_request_set_route('auth/login', $rid);
            }
            // Hard fallback (kalau helper unavailable)
            if ($rid && $userId) {
                $stmt = $this->pdo->prepare("UPDATE audit_request SET user_id = :uid, route = COALESCE(NULLIF(route,''),'auth/login') WHERE request_id = :rid");
                $stmt->execute([':uid' => $userId, ':rid' => $rid]);
            }
        } catch (\Throwable $e) {
            error_log('[LoginController] Audit bind error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Login sementara pelajar (pra-SSO):
     * - username: matrik
     * - password: IC (nokp)
     * - syarat: statuskategori = 'AKTIF' dalam view v210
     */
    private function authenticateStudentLocal(string $matrik, ?string $password): bool
    {
        $matrik = trim($matrik);
        if ($matrik === '' || $password === null || $password === '') {
            return false;
        }

        // Matrik lazimnya numerik; jika bukan numerik, jangan treat sebagai student flow.
        if (!preg_match('/^\d+$/', $matrik)) {
            return false;
        }

        try {
            $pdoStudent = Database::getInstance('sybase_asisdb')->getConnection();
            $sql = "SELECT TOP 1
                        matrik,
                        fakulti,
                        program,
                        nama,
                        nokp,
                        email,
                        notel_terkini,
                        statuskategori
                    FROM v210
                    WHERE matrik = :matrik
                      AND statuskategori = 'AKTIF'";
            $stmt = $pdoStudent->prepare($sql);
            $stmt->bindValue(':matrik', (int)$matrik, PDO::PARAM_INT);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if (!$student) {
                $this->auditLoginFail($matrik, 'student_not_found_or_inactive');
                return false;
            }

            // Semak IC (nokp) secara normalized: buang ruang/simbol, compare digits sahaja.
            $inputIc = $this->normalizeDigits((string)$password);
            $dbIc    = $this->normalizeDigits((string)($student['nokp'] ?? ''));
            if ($inputIc === '' || $dbIc === '' || !hash_equals($dbIc, $inputIc)) {
                $this->auditLoginFail($matrik, 'student_wrong_ic');
                return false;
            }

            // 🔒 Kuatkan sesi
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            session_regenerate_id(true);

            $nama = trim((string)($student['nama'] ?? '')) ?: ('Pelajar ' . $matrik);
            $fakulti = trim((string)($student['fakulti'] ?? ''));
            $program = trim((string)($student['program'] ?? ''));
            $email = trim((string)($student['email'] ?? ''));
            $tel = trim((string)($student['notel_terkini'] ?? ''));
            $studentAvatarUrl = $this->getStudentAvatarUrl($matrik);
            $studentGroupId = self::STUDENT_GROUP_ID;
            $studentGroupKod = 'APPLICANT';
            $studentGroupName = 'Pemohon';

            // Ambil metadata group dari MySQL jika wujud (tbl_m_group.f_groupID=27)
            try {
                $g = $this->pdo->prepare("SELECT f_groupKod, f_groupName FROM tbl_m_group WHERE f_groupID = :gid LIMIT 1");
                $g->execute([':gid' => $studentGroupId]);
                $grow = $g->fetch(PDO::FETCH_ASSOC) ?: [];
                if (!empty($grow['f_groupKod'])) $studentGroupKod = (string)$grow['f_groupKod'];
                if (!empty($grow['f_groupName'])) $studentGroupName = (string)$grow['f_groupName'];
            } catch (\Throwable $e) {
                error_log('[LoginController] student group lookup warn: ' . $e->getMessage());
            }

            // Simpan session minimum yang serasi dengan flow sedia ada.
            $_SESSION['auth_type']   = 'student';
            $_SESSION['f_stafID']    = $matrik; // compatibility untuk require_login()
            $_SESSION['f_nopekerja'] = $matrik;
            $_SESSION['f_nama']      = $nama;
            $_SESSION['f_nickname']  = $nama;
            $_SESSION['f_groupID']   = $studentGroupId;
            $_SESSION['f_groupKod']  = $studentGroupKod;
            $_SESSION['f_userID']    = 0;
            $_SESSION['user_name']   = $nama;
            $_SESSION['f_jabatan']   = $fakulti;
            $_SESSION['f_jawatan']   = $program;
            $_SESSION['avatar_url']  = $studentAvatarUrl;
            $_SESSION['group_default_id'] = $studentGroupId;
            $_SESSION['group_active_id']  = $studentGroupId;
            $_SESSION['role'] = $studentGroupKod;

            $_SESSION['user'] = [
                'f_userID'     => 0,
                'f_nopekerja'  => $matrik,
                'f_nama'       => $nama,
                'f_nickname'   => $nama,
                'f_groupID'    => $studentGroupId,
                'f_groupKod'   => $studentGroupKod,
                'f_groupName'  => $studentGroupName,
                'f_jabatan'    => $fakulti,
                'f_jawatan'    => $program,
                'jabatan'      => $fakulti,
                'jawatan'      => $program,
                'avatar_url'   => $studentAvatarUrl,
            ];

            $_SESSION['student_profile'] = [
                'matrik'         => $matrik,
                'fakulti'        => $fakulti,
                'program'        => $program,
                'nama'           => $nama,
                'email'          => $email,
                'notel_terkini'  => $tel,
                'statuskategori' => (string)($student['statuskategori'] ?? ''),
                'avatar_url'     => $studentAvatarUrl,
            ];

            // Theme default untuk elak undefined key pada view tertentu.
            $_SESSION['theme.menu']   = $_SESSION['theme.menu'] ?? 'light';
            $_SESSION['theme.topbar'] = $_SESSION['theme.topbar'] ?? 'light';
            $_SESSION['theme.layout'] = $_SESSION['theme.layout'] ?? 'light';

            $this->auditStudentSessionStart($matrik);
            $this->auditStudentLoginSuccess($student);
            return true;
        } catch (\Throwable $e) {
            // DB/driver issue untuk student connection -> bubble up sebagai error sistem login.
            throw $e;
        }
    }

    private function normalizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function getStudentAvatarUrl(string $matrik): string
    {
        $clean = preg_replace('/\D+/', '', $matrik) ?? '';
        if ($clean === '') {
            return base_url('assets/images/no-image.jpg');
        }
        return self::STUDENT_AVATAR_BASE_URL . rawurlencode($clean) . '.jpg';
    }

    /* ===========================
       AUDIT HELPERS (private)
       =========================== */

    /** Ambil user PK secara fleksibel daripada row $user */
    private function resolveUserId(array $user): int
    {
        foreach (['f_userID','user_id','id','uid','pk_user','id_user'] as $k) {
            if (isset($user[$k]) && is_numeric($user[$k])) {
                return (int)$user[$k];
            }
        }
        return 0; // tak jumpa
    }

    private function auditLoginFail(string $f_stafID, string $reason, ?array $user = null): void
    {
        try {
            if (!function_exists('audit_event')) return;

            $ipText = $_SERVER['HTTP_CF_CONNECTING_IP']
                ?? (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : null)
                ?? ($_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')));

            // Resolve request id
            $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;

            // Determine user_id and actor label
            $userId = null;
            $actorLabel = $f_stafID ?: 'Unknown';
            if (is_array($user) && !empty($user)) {
                $userId = $this->resolveUserId($user) ?: null;
                $name = trim((string)($user['f_nama'] ?? $user['f_nickname'] ?? '')) ?: null;
                $nostaf = trim((string)($user['f_nopekerja'] ?? $user['f_stafID'] ?? $f_stafID));
                if (function_exists('audit_format_actor_label')) {
                    $actorLabel = audit_format_actor_label($name, $nostaf);
                } else {
                    $actorLabel = $name ? ($name . ' (' . $nostaf . ')') : $nostaf;
                }
            } else {
                // Try to resolve nicer label via helper if available
                $labelFromHelper = null;
                if (function_exists('audit_format_actor_label')) {
                    $labelFromHelper = audit_format_actor_label(null, $f_stafID);
                }
                if ($labelFromHelper) $actorLabel = $labelFromHelper;
            }

            // ✅ FIX: Message dalam bahasa Inggeris with actor label
            $failMessage = function_exists('audit_format_message') ? audit_format_message('Login attempt failed', $actorLabel) : ('Login attempt failed by ' . $actorLabel);

            audit_event([
                'event_type'  => 'LOGIN',
                'severity'    => 'SECURITY',
                'outcome'     => 'FAIL',
                'target_type' => 'auth',
                'target_id'   => 'login',
                'message'     => $failMessage,
                'request_id'  => $requestId,
                'session_id'  => session_id(),
                'user_id'     => $userId,
                'actor_label' => $actorLabel,
                'ip'          => $ipText,
                'meta'        => [
                    'attempted_f_stafID' => $f_stafID,
                    'reason'             => $reason,
                    'ip_text'            => $ipText,
                    'user_agent'         => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'session_id'         => session_id(),
                    'request_id'         => $requestId,
                    'resolved_name'      => is_array($user) ? ($user['f_nama'] ?? $user['f_nickname'] ?? null) : null,
                    'resolved_nopekerja' => is_array($user) ? ($user['f_nopekerja'] ?? $user['f_stafID'] ?? null) : null,
                ],
            ]);
        } catch (\Throwable $e) {
            // diam
        }
    }

    private function auditLoginSuccess(array $user, ?int $userId): void
    {
        $nopek = (string)($user['f_nopekerja'] ?? '');
        $nama  = $user['f_nama'] ?? ($user['f_nickname'] ?? null);
        $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;

        // 1) Rekod audit_session
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO audit_session
                (session_id, user_id, user_nopekerja, started_at, ip_address, user_agent)
                VALUES (:sid, :uid, :no, NOW(6), :ip, :ua)
            ");
            $ipBin = null;
            if (class_exists('AuditLogger') && method_exists('AuditLogger','clientIp')) {
                $ipBin = AuditLogger::ipToBinary(AuditLogger::clientIp());
            }
            $stmt->execute([
                ':sid' => session_id(),
                ':uid' => $userId,
                ':no'  => $nopek,
                ':ip'  => $ipBin,
                ':ua'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('[LoginController] audit_session error: ' . $e->getMessage());
        }

        // 2) Event LOGIN SUCCESS
        try {
            if (!function_exists('audit_event')) return;
            
            // ✅ FIX: Gunakan nostaf dari session (yang sudah disimpan dengan betul) BUKAN dari $user array
            // Session sudah disimpan dengan betul di baris 64: $_SESSION['f_nopekerja'] = $nopekerja;
            // Gunakan nilai dari session untuk ensure konsisten dengan nilai yang digunakan dalam sistem
            $nostafFromSession = $_SESSION['f_nopekerja'] ?? $_SESSION['user']['f_nopekerja'] ?? null;
            $nostaf = $nostafFromSession ? (string)$nostafFromSession : (string)($user['f_nopekerja'] ?? '');
            
            // ✅ FIX: Format actor_label dengan nostaf full: "[nama] (nostaf)"
            $actorLabel = null;
            if (function_exists('audit_format_actor_label')) {
                $actorLabel = audit_format_actor_label($nama, $nostaf);
            } else {
                // Fallback: guna nama sahaja jika helper tidak available
                $actorLabel = $nama;
            }
            
            // ✅ FIX: Message dalam bahasa Inggeris dengan format: "[action] by [actor_label]"
            $message = audit_format_message('User login', $actorLabel);
            
            audit_event([
                'event_type'  => 'LOGIN',
                'severity'    => 'INFO',
                'outcome'     => 'SUCCESS',
                'target_type' => 'auth',
                'target_id'   => 'login',
                'message'     => $message,
                'request_id'  => $requestId,
                'session_id'  => session_id(),
                'user_id'     => $userId,
                'actor_label' => $actorLabel,
                'meta'        => [
                    'f_stafID'   => $user['f_stafID'] ?? null,
                    'f_nopekerja' => $user['f_nopekerja'] ?? null,
                    'group'       => $user['f_groupKod'] ?? null,
                    'session_id'  => session_id(),
                    'request_id'  => $requestId,
                    'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('[LoginController] audit_event error: ' . $e->getMessage());
        }
    }

    private function auditStudentLoginSuccess(array $student): void
    {
        try {
            if (!function_exists('audit_event')) return;

            $requestId = $GLOBALS['__AUDIT_REQUEST_ID'] ?? null;
            $matrik = (string)($student['matrik'] ?? '');
            $nama   = (string)($student['nama'] ?? '');
            $auditUserId = is_numeric($matrik) ? (int)$matrik : null;
            $actorLabel = function_exists('audit_format_actor_label')
                ? audit_format_actor_label($nama, $matrik)
                : trim($nama . ' (' . $matrik . ')');

            $message = function_exists('audit_format_message')
                ? audit_format_message('Student login', $actorLabel)
                : ('Student login by ' . $actorLabel);

            audit_event([
                'event_type'  => 'LOGIN',
                'severity'    => 'INFO',
                'outcome'     => 'SUCCESS',
                'target_type' => 'auth',
                'target_id'   => 'login_student_local',
                'message'     => $message,
                'request_id'  => $requestId,
                'session_id'  => session_id(),
                'user_id'     => $auditUserId,
                'actor_label' => $actorLabel,
                'meta'        => [
                    'auth_type'      => 'student_local',
                    'matrik'         => $matrik,
                    'statuskategori' => $student['statuskategori'] ?? null,
                    'email'          => $student['email'] ?? null,
                    'ip'             => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('[LoginController] student audit_event error: ' . $e->getMessage());
        }
    }

    private function auditStudentSessionStart(string $matrik): void
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO audit_session
                (session_id, user_id, user_nopekerja, started_at, ip_address, user_agent)
                VALUES (:sid, :uid, :no, NOW(6), :ip, :ua)
            ");

            $uid = is_numeric($matrik) ? (int)$matrik : null;
            $ipBin = null;
            if (class_exists('AuditLogger') && method_exists('AuditLogger','clientIp')) {
                $ipBin = AuditLogger::ipToBinary(AuditLogger::clientIp());
            }

            $stmt->execute([
                ':sid' => session_id(),
                ':uid' => $uid,
                ':no'  => $matrik,
                ':ip'  => $ipBin,
                ':ua'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('[LoginController] student audit_session error: ' . $e->getMessage());
        }
    }
}
