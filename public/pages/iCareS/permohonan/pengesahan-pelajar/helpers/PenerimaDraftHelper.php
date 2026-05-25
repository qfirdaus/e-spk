<?php

function PenerimaDraftPath(string $matrik): string
{
    return __DIR__ . '/../temp/' . $matrik . '_draft.json';
}

/*
|--------------------------------------------------------------------------
| READ DRAFT
|--------------------------------------------------------------------------
*/
function getPenerimaDraft(string $matrik): array
{
    $path = PenerimaDraftPath($matrik);

    if (!file_exists($path)) {

        return [
            'draft_initialized' => false,
            'updated_at' => null,

            'dataStudent' => [],
            'penerima' => [],
            'perakuan' => []
        ];
    }

    $json = file_get_contents($path);

    $data = json_decode($json, true);

    if (!is_array($data)) {

        return [
            'draft_initialized' => false,
            'updated_at' => null,

            'dataStudent' => [],
            'penerima' => [],
            'perakuan' => []
        ];
    }

    return $data;
}
/*
|--------------------------------------------------------------------------
| SAVE DRAFT
|--------------------------------------------------------------------------
*/
function savePenerimaDraft(string $matrik, array $draft): bool
{
    $path = PenerimaDraftPath($matrik);

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    $draft['draft_initialized'] = true;
    $draft['updated_at'] = date('Y-m-d H:i:s');

    return (bool) file_put_contents(
        $path,
        json_encode(
            $draft,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        )
    );
}