<?php
// ajax/user-list-student-options.php
// Search active students from v210 for Add Pelajar modal (Select2 remote source)
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

while (ob_get_level() > 0) {
    @ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../includes/init.php';
    require_login();
    require_once __DIR__ . '/_helpers.php';
    require_once __DIR__ . '/../includes/functions-db.php';
    require_once __DIR__ . '/../classes/Database.php';

    $pdo = Database::getInstance('mysql')->getConnection();
    ensureAjaxGroupManagePermission($pdo);

    if (!isValidCsrfToken((string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => (string)__('userGroup_csrf_invalid'),
            'results' => [],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!is_student_mode_enabled()) {
        http_response_code(403);
        echo json_encode([
            'error' => true,
            'message' => (string)__('studentSearch_mode_disabled'),
            'results' => [],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!checkRateLimit('student_options', 40, 60)) {
        http_response_code(429);
        echo json_encode([
            'error' => true,
            'message' => 'Terlalu banyak permintaan. Sila cuba lagi selepas beberapa saat.',
            'results' => [],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $q = trim((string)($_POST['q'] ?? ''));
    $page = max(1, (int)($_POST['page'] ?? 1));
    $perPage = 20;
    $maxPage = 5;
    if ($page > $maxPage) {
        $page = $maxPage;
    }

    if (mb_strlen($q) < 2) {
        echo json_encode([
            'error' => false,
            'results' => [],
            'pagination' => ['more' => false],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $existingIdentifiers = [];
    try {
        $existingSql = "SELECT DISTINCT f_stafID FROM tbl_m_user WHERE TRIM(COALESCE(f_categoryUser, '')) = 'PELAJAR' AND f_stafID IS NOT NULL AND f_stafID <> ''";
        $existingStmt = $pdo->query($existingSql);
        $existingRows = $existingStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        foreach ($existingRows as $identifier) {
            $identifier = trim((string)$identifier);
            if ($identifier !== '') {
                $existingIdentifiers[$identifier] = true;
            }
        }
    } catch (Throwable $e) {
        error_log('[user-list-student-options] Error loading existing student identifiers: ' . $e->getMessage());
    }

    $pdoSybase = Database::pdoSybaseStudent();
    if (!$pdoSybase) {
        throw new RuntimeException('Student database connection is not available.');
    }

    $where = ["upper(convert(varchar(20), statuskategori)) = 'AKTIF'"];
    $params = [];

    $where[] = "(
        upper(convert(varchar(50), matrik)) LIKE :q
        OR upper(convert(varchar(255), nama)) LIKE :q
    )";
    $params[':q'] = '%' . strtoupper($q) . '%';

    $whereSql = 'WHERE ' . implode(' AND ', $where);

    // Avoid COUNT(*) on v210. On some ASE environments the full count plus
    // repeated TOP scans can trigger process termination for this request.
    $limit = ($perPage * $page) + 1;
    $sql = "
        SELECT TOP {$limit}
            matrik,
            nama,
            program,
            fakulti,
            tahap_pengajian,
            statuskategori
        FROM v210
        {$whereSql}
        ORDER BY nama ASC
    ";

    $stmt = $pdoSybase->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $allResults = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $startIndex = ($page - 1) * $perPage;
    $hasMore = count($allResults) > ($perPage * $page);
    $results = array_slice($allResults, $startIndex, $perPage);

    $formattedResults = [];
    foreach ($results as $row) {
        $matrik = trim((string)($row['matrik'] ?? ''));
        $nama = trim((string)($row['nama'] ?? ''));
        $fakulti = trim((string)($row['fakulti'] ?? ''));
        $program = trim((string)($row['program'] ?? ''));
        $tahap = trim((string)($row['tahap_pengajian'] ?? ''));
        $statuskategori = trim((string)($row['statuskategori'] ?? ''));

        if ($matrik === '') {
            continue;
        }

        $display = $nama !== '' ? $nama : $matrik;
        $display .= ' (' . $matrik . ')';

        $isDisabled = isset($existingIdentifiers[$matrik]);
        if ($isDisabled) {
            $display .= ' [' . (string)__('userList_student_already_exists') . ']';
        }

        $formattedResults[] = [
            'id' => $matrik,
            'text' => $display,
            'matrik' => $matrik,
            'nama' => $nama,
            'program' => $program,
            'fakulti' => $fakulti,
            'tahap_pengajian' => $tahap,
            'statuskategori' => $statuskategori,
            'disabled' => $isDisabled,
        ];
    }

    echo json_encode([
        'error' => false,
        'results' => $formattedResults,
        'pagination' => ['more' => $hasMore],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[user-list-student-options] Error: ' . $e->getMessage() . ' | q=' . json_encode($q ?? '', JSON_UNESCAPED_UNICODE) . ' | page=' . json_encode($page ?? 1));
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => (string)__('studentSearch_system_error'),
        'results' => [],
    ], JSON_UNESCAPED_UNICODE);
}
