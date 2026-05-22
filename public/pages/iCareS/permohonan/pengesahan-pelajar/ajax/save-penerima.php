<?php
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['f_stafID'];

$data = [
    'nama_penerima' => $_POST['nama_penerima'] ?? '',
    'alamat1'       => $_POST['alamat1'] ?? '',
    'alamat2'       => $_POST['alamat2'] ?? '',
    'poskod'        => $_POST['poskod'] ?? '',
    'bandar'        => $_POST['bandar'] ?? '',
    'negeri'        => $_POST['negeri'] ?? '',
    'negara'        => $_POST['negara'] ?? '',
];

$dir = __DIR__ . '/../temp';

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$file = $dir . '/' . $user_id . '_penerima.json';

file_put_contents(
    $file,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode(['status' => 'success']);