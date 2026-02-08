<?php

// ===============================================
// ✅ CONFIG HELPER - Sistem e-Prestasi
// ===============================================

/**
 * Ambil nilai konfigurasi dari fail settings.php
 * Contoh: app_config('site.title')
 */
function app_config($key, $default = null) {
    static $config;

    if (is_null($config)) {
        $file = realpath(__DIR__ . '/../../configuration/settings.php');
        if (!file_exists($file)) return $default;

        $loaded = include $file;
        if (!is_array($loaded)) return $default;

        $config = $loaded;
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value ?? $default;
}
