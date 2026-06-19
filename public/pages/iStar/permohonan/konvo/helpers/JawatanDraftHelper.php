<?php

function JawatanDraftPath(string $matrik): string
{
    return __DIR__ . '/../temp/' . $matrik . '_jawatan.json';
}


function canEditFieldJawatan($row, $field)
{
    if (($row['sumber'] ?? '') === 'ISTAD') {

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
            'rows' => []
        ];
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return [
            'draft_initialized' => false,
            'rows' => []
        ];
    }

    return [
        'draft_initialized' => $data['draft_initialized'] ?? false,
        'rows' => $data['rows'] ?? []
    ];
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

//save by row 
function saveJawatanDraftRows(string $matrik, array $rows): bool
{
    $payload = [
        'draft_initialized' => true,
        'updated_at' => date('Y-m-d H:i:s'),
        'rows' => array_values($rows)
    ];

    return saveJawatanDraft($matrik, $payload);
}