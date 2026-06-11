<?php

function akademikTambahanDraftPath(string $matrik): string
{
    return __DIR__ . '/../temp/' . $matrik . '_akademik_tambahan.json';
}

function getAkademikTambahanDraft(string $matrik): array
{
    $path = akademikTambahanDraftPath($matrik);

    if (!file_exists($path)) {
        return [
            'draft_initialized' => false,
            'updated_at' => null,
            'rows' => [],
        ];
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return [
            'draft_initialized' => false,
            'updated_at' => null,
            'rows' => [],
        ];
    }

    return [
        'draft_initialized' => (bool)($data['draft_initialized'] ?? false),
        'updated_at' => $data['updated_at'] ?? null,
        'rows' => array_values($data['rows'] ?? []),
    ];
}

function saveAkademikTambahanDraft(string $matrik, array $payload): bool
{
    $path = akademikTambahanDraftPath($matrik);

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    return (bool) file_put_contents(
        $path,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

//save by row (for inline edit)
function saveAkademikTambahanDraftRows(string $matrik, array $rows): bool
{
    $payload = [
        'draft_initialized' => true,
        'updated_at' => date('Y-m-d H:i:s'),
        'rows' => array_values($rows)
    ];

    return saveAkademikTambahanDraft($matrik, $payload);
}
