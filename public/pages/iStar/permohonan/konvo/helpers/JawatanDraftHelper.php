<?php

function JawatanDraftPath(string $matrik): string
{
    return __DIR__ . '/../temp/' . $matrik . '_jawatan.json';
}

function canEditFieldJawatan($row, $field)
{
    if (($row['sumber'] ?? '') === 'IStAD') {

        $allowed = ['peringkat'];

        return in_array($field, $allowed);
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| READ DRAFT
|--------------------------------------------------------------------------
*/
function getJawatanDraft(string $matrik): array
{
    $path = JawatanDraftPath($matrik);

    if (!file_exists($path)) {
        return [
            'draft_initialized' => false,
            'updated_at' => null,
            'rows' => []
        ];
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return [
            'draft_initialized' => false,
            'updated_at' => null,
            'rows' => []
        ];
    }

    return [
        'draft_initialized' => $data['draft_initialized'] ?? false,
        'updated_at' => $data['updated_at'] ?? null,
        'rows' => is_array($data['rows'] ?? null)
            ? array_values($data['rows'])
            : []
    ];
}

/*
|--------------------------------------------------------------------------
| INIT FIRST TIME (FROM ISTAD)
|--------------------------------------------------------------------------
*/
function initJawatanDraft(string $matrik, array $istadRows): array
{
    $rows = [];

    foreach ($istadRows as $i => $row) {

        $idBase = $row['id_kegiatan_badan']
            ?? $row['id_jawatan']
            ?? uniqid('J');

        $rows[] = [
            'id' => 'ISTAD_' . $idBase,
            'id_kegiatan_badan' => $row['id_kegiatan_badan'] ?? null,
            'sumber' => 'IStAD',

            'id_kategori_kegiatan' => $row['id_kategori_aktiviti'] ?? null,
            'kod_kategori_aktiviti' => $row['kod_kategori_aktiviti'] ?? null,
            'kategori_aktiviti' => $row['kategori_aktiviti'] ?? null,

            'nama_bp_program' => $row['nama_bp_program'] ?? '',
            'id_jawatan' => $row['id_jawatan'] ?? '',
            'jawatan' => $row['jawatan'] ?? '',
            'tarikh_lantikan' => $row['tarikh_mula'] ?? '',

            'peringkat' => $row['peringkat'] ?? null,

            'is_dirty' => false,
            'source_override' => false,
            'conflict' => false
        ];
    }

    $payload = [
        'draft_initialized' => true,
        'updated_at' => date('Y-m-d H:i:s'),
        'rows' => $rows
    ];

    saveJawatanDraft($matrik, $payload);

    return $payload;
}

/*
|--------------------------------------------------------------------------
| SAVE DRAFT
|--------------------------------------------------------------------------
*/
function saveJawatanDraft(string $matrik, array $payload): bool
{
    $path = JawatanDraftPath($matrik);

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    return (bool) file_put_contents(
        $path,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

function saveJawatanDraftRows(string $matrik, array $rows): bool
{
    $payload = [
        'draft_initialized' => true,
        'updated_at' => date('Y-m-d H:i:s'),
        'rows' => array_values($rows)
    ];

    return saveJawatanDraft($matrik, $payload);
}