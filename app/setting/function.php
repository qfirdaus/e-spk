<?php
// ==================================================
// ✅ Fungsi & Helper Global untuk e-Prestasi
// ==================================================

// Mula sesi jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sekat akses jika tiada sesi login (melainkan CLI atau AJAX)
$current = basename($_SERVER['SCRIPT_NAME']);
// Jika page mahu benarkan anonymous AJAX (contoh: `ALLOW_ANON_AJAX` defined), skip redirect
if (!defined('ALLOW_ANON_AJAX') || !ALLOW_ANON_AJAX) {
    if (!in_array($current, ['index.php', 'login.php', 'logout.php'])) {
        if (empty($_SESSION['f_stafID'])) {
            header("Location: ../index.php");
            exit;
        }
    }
}
