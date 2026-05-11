<?php
function penglibatanDraftPath(string $matrik): string
{
    return __DIR__ . '/../temp/' . $matrik . '.json';
}

function canEditField($row, $field)
{
    if (($row['sumber'] ?? '') === 'IStAD') {

        $allowed = ['wakil', 'peringkat', 'pencapaian'];

        return in_array($field, $allowed);
    }

    return true;
}

function getPenglibatanDraft(string $matrik): array
{
    $path = penglibatanDraftPath($matrik);

    if (!file_exists($path)) {
        return [];
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return [];
    }

    return $data['rows'] ?? [];
}

/*
|--------------------------------------------------------------------------
| LOAD DRAFT (atau create from IStAD first time)
|--------------------------------------------------------------------------
*/
function loadPenglibatanDraft(string $matrik, array $istadRows = []): array
{
    $path = penglibatanDraftPath($matrik);

    if (!file_exists($path)) {

        // convert IStAD → draft awal
        $rows = [];

        foreach ($istadRows as $i => $row) {

            $rows[] = [
                'id'          => 'istad_' . $i,
                'id_kegiatan_pelajar' => $row['id_kegiatan_pelajar'] ?? null,
                'sumber'      => 'IStAD',
                'nama'        => $row['nama'] ?? '',
                'tarikh'      => $row['tarikh'] ?? '',
                'wakil'       => null,
                'peringkat'   => null,
                'pencapaian'  => 'PESERTA'
            ];
        }

        savePenglibatanDraft($matrik, $rows);

        return $rows;
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    return $data['rows'] ?? [];
}

/*
|--------------------------------------------------------------------------
| SAVE DRAFT
|--------------------------------------------------------------------------
*/
function savePenglibatanDraft(string $matrik, array $rows): bool
{
    $path = penglibatanDraftPath($matrik);

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    foreach ($rows as $row) {
        if (!isset($row['id']) || !isset($row['sumber']) || !array_key_exists('nama', $row)) {
            throw new Exception("Invalid draft structure");
        }
    }

    $payload = [
        'updated_at' => date('Y-m-d H:i:s'),
        'rows' => array_values($rows)
    ];

    return (bool) file_put_contents(
        $path,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}