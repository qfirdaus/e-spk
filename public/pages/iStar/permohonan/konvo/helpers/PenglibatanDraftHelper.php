<?php
function penglibatanDraftPath(string $matrik): string
{
    return __DIR__ . '/../temp/' . $matrik . '_penglibatan.json';
}

function canEditField($row, $field)
{
    if (($row['sumber'] ?? '') === 'ISTAD') {

        $allowed = ['wakil', 'peringkat', 'pencapaian'];

        return in_array($field, $allowed);
    }

    return true;
}

function getPenglibatanDraft(string $matrik): array
{
    $path = penglibatanDraftPath($matrik);

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
| LOAD DRAFT (atau create from ISTAD first time)
|--------------------------------------------------------------------------
*/
function loadPenglibatanDraft(string $matrik, array $istadRows = []): array
{
    $path = penglibatanDraftPath($matrik);

    if (!file_exists($path)) {

        $rows = [];

        foreach ($istadRows as $i => $row) {

            $rows[] = [
                'id' => 'ISTAD_' . ($row['id_kegiatan_pelajar'] ?? $i),
                'id_kegiatan_pelajar' => $row['id_kegiatan_pelajar'] ?? null,
                'sumber' => 'ISTAD',
                'nama' => $row['nama'] ?? '',
                'tarikh' => $row['tarikh'] ?? '',
                'wakil' => null,
                'peringkat' => null,
                'pencapaian' => 'PESERTA',
                'source_override' => false
            ];
        }

        $payload = [
            'draft_initialized' => true,
            'updated_at' => date('Y-m-d H:i:s'),
            'rows' => $rows
        ];

        saveDraft($matrik, $rows);

        return $payload;
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

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
function savePenglibatanDraft(string $matrik, array $payload): bool
{
    $path = penglibatanDraftPath($matrik);

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    return (bool) file_put_contents(
        $path,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

//save by row (for inline edit)
function saveDraft(string $matrik, array $rows): bool
{
    $payload = [
        'draft_initialized' => true,
        'updated_at' => date('Y-m-d H:i:s'),
        'rows' => array_values($rows)
    ];

    return savePenglibatanDraft($matrik, $payload);
}