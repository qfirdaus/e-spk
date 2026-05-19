<?php

function JawatanDraftPath(string $matrik): string
{
    return __DIR__ . '/../temp/' . $matrik . '_jawatan.json';
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
        'rows' => array_values($data['rows'] ?? [])
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

        $id = $row['id_kegiatan_bp'] ?? $i;

        $rows[] = [
            'id' => 'ISTAD_' . $id,
            'id_kegiatan_bp' => $row['id_kegiatan_bp'] ?? null,
            'sumber' => 'IStAD',

            'nama_bp_program' => $row['nama_bp_program'] ?? '',            
            'id_jawatan' => $row['id_jawatan'] ?? '',
            'jawatan' => $row['jawatan'] ?? '',
            'tarikh_lantikan' => $row['tarikh_mula'] ?? '',

            'peringkat' => null,
            'is_dirty' => false,
            'source_override' => false
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