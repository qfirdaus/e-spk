<?php
// ajax/group-list.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';
require_login();
header('Content-Type: application/json; charset=utf-8');

try {
    // Konsisten dengan modul-list.php
    $db = Database::getInstance('mysql')->getConnection();

    // Jika ada lajur penanda aktif (contoh f_flag), boleh aktifkan WHERE di bawah:
    $where = '1=1';
    // $where = 'COALESCE(f_flag,1)=1'; // uncomment jika jadual ada f_flag

    $sql = "
      SELECT
        f_groupID   AS id,
        f_groupKod  AS kod,
        f_groupName AS nama,
        TRIM(COALESCE(f_color, '')) AS color
      FROM tbl_m_group
      WHERE $where
      ORDER BY f_groupKod ASC, f_groupName ASC, f_groupID ASC
    ";

    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // (Opsyenal) pastikan id integer
    foreach ($rows as &$r) {
        if (isset($r['id'])) $r['id'] = (int)$r['id'];
    }

    echo json_encode(['error' => false, 'groups' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(
        ['error' => true, 'message' => 'Ralat server: ' . $e->getMessage()],
        JSON_UNESCAPED_UNICODE
    );
}
