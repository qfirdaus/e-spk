<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login('../index.php');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect('pages/dashboard.php');
}

$allowedForms = [
    'data_peribadi' => 'Maklumat Peribadi',
    'data_alamat' => 'Maklumat Alamat',
    'data_pekerjaan' => 'Maklumat Pekerjaan',
    'data_kesihatan' => 'Maklumat Kesihatan',
    'data_akaun' => 'Maklumat Akaun',
    'data_akademik' => 'Maklumat Akademik',
    'data_kokurikulum' => 'Maklumat Kokurikulum',
    'keluarga_adik_beradik' => 'Maklumat Adik-Beradik',
    'keluarga_bapa' => 'Maklumat Bapa',
    'keluarga_ibu' => 'Maklumat Ibu',
    'keluarga_penjaga' => 'Maklumat Penjaga',
    'permohonan_bantuan_kes_khas' => 'Permohonan Bantuan Kes Khas',
    'permohonan_bantuan_kes_khas_perakuan' => 'Perakuan Bantuan Kes Khas',
    'permohonan_pengesahan_gl' => 'Permohonan Pengesahan GL',
    'permohonan_pengesahan_gl_perakuan' => 'Perakuan Pengesahan GL',
    'permohonan_pengesahan_pelajar_penerima' => 'Penerima Pengesahan Pelajar',
    'permohonan_pengesahan_pelajar_perakuan' => 'Perakuan Pengesahan Pelajar',
    'permohonan_pengesahan_insurans_penerima' => 'Penerima Pengesahan Insurans',
    'permohonan_pengesahan_insurans_perakuan' => 'Perakuan Pengesahan Insurans',
    'permohonan_pengesahan_insurans_peribadi' => 'Maklumat Peribadi Insurans',
    'permohonan_pengesahan_insurans_dokumen' => 'Dokumen Pengesahan Insurans',
    'istar_hari_inovasi_peribadi' => 'iStar Hari Inovasi - Maklumat Peribadi',
    'istar_hari_inovasi_penglibatan_program' => 'iStar Hari Inovasi - Penglibatan Program',
    'istar_hari_inovasi_jawatan_disandang' => 'iStar Hari Inovasi - Jawatan Disandang',
    'istar_hari_inovasi_anugerah_pengiktirafan' => 'iStar Hari Inovasi - Anugerah Pengiktirafan',
    'istar_hari_inovasi_perakuan' => 'iStar Hari Inovasi - Perakuan',
    'istar_hari_inovasi_buku_degree_perakuan' => 'iStar Hari Inovasi - Buku Ijazah Sarjana Muda',
    'istar_hari_inovasi_diploma_terbaik_perakuan' => 'iStar Hari Inovasi - Diploma Terbaik',
    'istar_hari_inovasi_buku_diploma_perakuan' => 'iStar Hari Inovasi - Buku Diploma',
    'istar_konvo_peribadi' => 'iStar Konvo - Maklumat Peribadi',
    'istar_konvo_penglibatan_program' => 'iStar Konvo - Penglibatan Program',
    'istar_konvo_jawatan_disandang' => 'iStar Konvo - Jawatan Disandang',
    'istar_konvo_anugerah_pengiktirafan' => 'iStar Konvo - Anugerah Pengiktirafan',
    'istar_konvo_perakuan' => 'iStar Konvo - Perakuan',
    'istar_konvo_buku_degree_perakuan' => 'iStar Konvo - Buku Ijazah Sarjana Muda',
    'istar_konvo_diploma_terbaik_perakuan' => 'iStar Konvo - Diploma Terbaik',
    'istar_konvo_buku_diploma_perakuan' => 'iStar Konvo - Buku Diploma',
    'istar_konvo_tokoh_keusahawanan_perakuan' => 'iStar Konvo - Tokoh Keusahawanan',
    'istar_konvo_khas_bem_perakuan' => 'iStar Konvo - Khas BEM',
];

$formKey = trim((string)($_POST['icares_form'] ?? ''));
$formLabel = $allowedForms[$formKey] ?? '';

$returnTo = trim((string)($_POST['return_to'] ?? ''));
if ($returnTo === '') {
    $returnTo = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
}

$redirectTo = 'pages/dashboard.php';
if ($returnTo !== '') {
    $parts = parse_url($returnTo);
    $path = (string)($parts['path'] ?? '');
    $query = isset($parts['query']) ? ('?' . (string)$parts['query']) : '';
    $fragment = isset($parts['fragment']) ? ('#' . (string)$parts['fragment']) : '';

    if ($path !== '') {
        $basePath = base_path();
        if ($basePath !== '/' && str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath));
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'pages/iCareS/') || str_starts_with($path, 'pages/iStar/')) {
            $redirectTo = $path . $query . $fragment;
        }
    }
}

$csrfToken = (string)($_POST['csrf_token'] ?? '');
$sessionToken = (string)($_SESSION['csrf_token'] ?? '');
if ($csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    set_alert([
        'title' => 'icares_update_error_title',
        'text' => 'icares_update_invalid_csrf',
        'icon' => 'error',
        'confirm' => true,
    ]);
    redirect($redirectTo);
}

if ($formKey === '' || !isset($allowedForms[$formKey])) {
    set_alert([
        'title' => 'icares_update_error_title',
        'text' => 'icares_update_invalid_form',
        'icon' => 'error',
        'confirm' => true,
    ]);
    redirect($redirectTo);
}

$normalizeValue = static function ($value) use (&$normalizeValue) {
    if (is_array($value)) {
        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[(string)$key] = $normalizeValue($item);
        }
        return $normalized;
    }

    if (is_scalar($value) || $value === null) {
        return trim((string)$value);
    }

    return '';
};

$excludedPostKeys = ['csrf_token', 'return_to', 'icares_form'];
$values = [];
foreach ($_POST as $key => $value) {
    $key = (string)$key;
    if (in_array($key, $excludedPostKeys, true)) {
        continue;
    }
    $values[$key] = $normalizeValue($value);
}

$files = [];
foreach ($_FILES as $key => $file) {
    if (!is_array($file)) {
        continue;
    }
    $files[(string)$key] = [
        'name' => $normalizeValue($file['name'] ?? ''),
        'type' => $normalizeValue($file['type'] ?? ''),
        'size' => $normalizeValue($file['size'] ?? ''),
        'error' => $normalizeValue($file['error'] ?? ''),
    ];
}

if (!isset($_SESSION['icares_form_drafts']) || !is_array($_SESSION['icares_form_drafts'])) {
    $_SESSION['icares_form_drafts'] = [];
}

$_SESSION['icares_form_drafts'][$formKey] = [
    'form' => $formKey,
    'form_label' => $formLabel,
    'updated_at' => date('c'),
    'values' => $values,
    'files' => $files,
];

$_SESSION['icares_last_submission'] = [
    'form' => $formKey,
    'form_label' => $formLabel,
    'posted_at' => date('c'),
    'post_keys' => array_values(array_keys($values)),
    'file_keys' => array_keys($_FILES),
];

set_alert([
    'title' => 'icares_update_received_title',
    'text' => 'icares_update_received_text',
    'icon' => 'info',
    'confirm' => true,
    'is_key' => false,
    'title_is_key' => false,
    'text_is_key' => false,
    'confirmText_is_key' => true,
]);

$_SESSION['alert']['title'] = tr('icares_update_received_title', 'Permintaan Diterima');
$_SESSION['alert']['text'] = tr(
    'icares_update_received_text',
    'Maklumat disimpan sementara dalam sesi semasa dan dipaparkan semula pada borang ini.'
);
$_SESSION['alert']['text'] = $formLabel . ': ' . $_SESSION['alert']['text'];

redirect($redirectTo);
