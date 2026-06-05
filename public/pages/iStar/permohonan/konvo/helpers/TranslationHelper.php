<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$lang = $_SESSION['lang'] ?? 'ms';

$file = __DIR__ . "/../../../../../lang/custom/$lang.php";

$translations = require $file;

header('Content-Type: application/javascript; charset=utf-8');

$keys = [
    'add_new',
    'sync_istad',
    'load_data_failed',
    'datatable_search_placeholder',
    'datatable_length_menu',
    'datatable_info',
    'datatable_info_empty',
    'datatable_empty_table',
    'datatable_zero_records',
    'datatable_next',
    'datatable_previous',
    'swal_failed_title',
    'swal_success_title',
    'swal_system_error_title',
    'swal_try_again_later',
    'swal_try_again',
    'swal_delete_record_title',
    'swal_delete_award_title',
    'swal_delete_warning',
    'swal_confirm_delete',
    'swal_cancel',
    'swal_ok',
    'record_delete_success',
    'record_delete_failed',
    'award_add_success',
    'award_save_failed',
    'award_delete_success',
    'award_delete_failed',
    'award_invalid_id',
    'record_update_failed',
    'system_error_try_again',
    'sync_istad_title',
    'sync_istad_text',
    'sync_istad_confirm',
    'sync_success_title',
    'sync_failed'
];

$data = [];

foreach ($keys as $key) {
    $data[$key] = $translations[$key] ?? $key;
}

echo 'window.konvoI18n = ' .
    json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
    . ';';