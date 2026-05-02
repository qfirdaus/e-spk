<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login('../index.php');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect('pages/dashboard.php');
}

$allowedForms = [
    'data_peribadi' => 'icares_form_data_peribadi',
    'data_alamat' => 'icares_form_data_alamat',
    'data_pekerjaan' => 'icares_form_data_pekerjaan',
    'data_kesihatan' => 'icares_form_data_kesihatan',
    'data_akaun' => 'icares_form_data_akaun',
    'data_akademik' => 'icares_form_data_akademik',
    'data_kokurikulum' => 'icares_form_data_kokurikulum',
    'keluarga_adik_beradik' => 'icares_form_keluarga_adik_beradik',
    'keluarga_bapa' => 'icares_form_keluarga_bapa',
    'keluarga_ibu' => 'icares_form_keluarga_ibu',
    'keluarga_penjaga' => 'icares_form_keluarga_penjaga',
    'permohonan_bantuan_kes_khas' => 'icares_form_permohonan_bantuan_kes_khas',
    'permohonan_bantuan_kes_khas_perakuan' => 'icares_form_permohonan_bantuan_kes_khas_perakuan',
    'permohonan_pengesahan_gl' => 'icares_form_permohonan_pengesahan_gl',
    'permohonan_pengesahan_gl_perakuan' => 'icares_form_permohonan_pengesahan_gl_perakuan',
    'permohonan_pengesahan_pelajar_penerima' => 'icares_form_permohonan_pengesahan_pelajar_penerima',
    'permohonan_pengesahan_pelajar_perakuan' => 'icares_form_permohonan_pengesahan_pelajar_perakuan',
    'permohonan_pengesahan_insurans_penerima' => 'icares_form_permohonan_pengesahan_insurans_penerima',
    'permohonan_pengesahan_insurans_perakuan' => 'icares_form_permohonan_pengesahan_insurans_perakuan',
    'permohonan_pengesahan_insurans_peribadi' => 'icares_form_permohonan_pengesahan_insurans_peribadi',
    'permohonan_pengesahan_insurans_dokumen' => 'icares_form_permohonan_pengesahan_insurans_dokumen',
    'istar_hari_inovasi_peribadi' => 'icares_form_istar_hari_inovasi_peribadi',
    'istar_hari_inovasi_penglibatan_program' => 'icares_form_istar_hari_inovasi_penglibatan_program',
    'istar_hari_inovasi_jawatan_disandang' => 'icares_form_istar_hari_inovasi_jawatan_disandang',
    'istar_hari_inovasi_anugerah_pengiktirafan' => 'icares_form_istar_hari_inovasi_anugerah_pengiktirafan',
    'istar_hari_inovasi_perakuan' => 'icares_form_istar_hari_inovasi_perakuan',
    'istar_hari_inovasi_buku_degree_perakuan' => 'icares_form_istar_hari_inovasi_buku_degree_perakuan',
    'istar_hari_inovasi_diploma_terbaik_perakuan' => 'icares_form_istar_hari_inovasi_diploma_terbaik_perakuan',
    'istar_hari_inovasi_buku_diploma_perakuan' => 'icares_form_istar_hari_inovasi_buku_diploma_perakuan',
    'istar_konvo_peribadi' => 'icares_form_istar_konvo_peribadi',
    'istar_konvo_penglibatan_program' => 'icares_form_istar_konvo_penglibatan_program',
    'istar_konvo_jawatan_disandang' => 'icares_form_istar_konvo_jawatan_disandang',
    'istar_konvo_anugerah_pengiktirafan' => 'icares_form_istar_konvo_anugerah_pengiktirafan',
    'istar_konvo_perakuan' => 'icares_form_istar_konvo_perakuan',
    'istar_konvo_buku_degree_perakuan' => 'icares_form_istar_konvo_buku_degree_perakuan',
    'istar_konvo_diploma_terbaik_perakuan' => 'icares_form_istar_konvo_diploma_terbaik_perakuan',
    'istar_konvo_buku_diploma_perakuan' => 'icares_form_istar_konvo_buku_diploma_perakuan',
    'istar_konvo_tokoh_keusahawanan_perakuan' => 'icares_form_istar_konvo_tokoh_keusahawanan_perakuan',
    'istar_konvo_khas_bem_perakuan' => 'icares_form_istar_konvo_khas_bem_perakuan',
];

$formKey = trim((string)($_POST['icares_form'] ?? ''));
$formLabelKey = $allowedForms[$formKey] ?? '';
$formLabel = $formLabelKey !== '' ? tr($formLabelKey, $formKey) : '';

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
