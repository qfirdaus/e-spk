<?php

return [
    // Maklumat asas laman:
    // - title: nama sistem untuk title browser / fallback paparan umum
    // - favicon: ikon tab browser
    // - default_home: laluan utama sistem selepas login / untuk canonical dan logo link
    'site' => [
        'title'        => 'UPNM | Sistem Pengurusan Pelajar (e-HEPA)',
        'favicon'      => 'assets/images/favicon.ico',
        'default_home' => 'pages/dashboard.php',
    ],

    // Branding aset visual utama sistem.
    // Semua path merujuk kepada fail dalam folder `assets/images/`.
    // Tukar path di sini jika projek clone ini menggunakan logo lain.
    'branding' => [
        'login_header_logo' => 'assets/images/logo-upnm.png',
        'login_panel_logo'  => 'assets/images/upnm30-logo.png',
        'topbar_logo_light' => 'assets/images/logo.png',
        'topbar_logo_dark'  => 'assets/images/logo-dark.png',
        'topbar_logo_sm'    => 'assets/images/logo-sm.png',
        'sidebar_logo'      => 'assets/images/new-logo.png',
    ],

    // Teks footer global sistem.
    // Sesuai untuk hak cipta atau organisasi pemilik projek.
    'footer' => [
        'text' => 'Hak Cipta © ' . date('Y') . ' Sistem Pengurusan Pelajar (e-HEPA)',
    ],

    // Metadata dan identiti teknikal sistem:
    // - name: nama sistem yang dipaparkan pada login / tempat umum
    // - version: versi semasa sistem
    // - author: pemilik / pembangun sistem
    // - meta_author: nilai meta author dalam <head>
    // - support: emel sokongan global
    'system' => [
        'name'        => 'Sistem Pengurusan Pelajar (e-HEPA)',
        'version'     => '1.7.0',
        'author'      => 'UPNM',
        'meta_author' => 'Sistem Pengurusan Pelajar (e-HEPA)',
        'support'     => 'support@upnm.edu.my',
    ],

    // Tetapan global untuk kandungan email sistem.
    // - system_name: nama sistem yang dipaparkan dalam template email
    // - default_action_url: pautan tindakan utama dalam email (jika berkaitan)
    // - footer_note: nota standard di bahagian bawah email
    'mail' => [
        'system_name'        => 'Sistem Pengurusan Pelajar (e-HEPA)',
        'default_action_url' => 'https://www.upnm.edu.my',
        'footer_note'        => 'Emel ini dijana secara automatik. Sila jangan balas emel ini.',
    ],

    // Tetapan tingkah laku aplikasi:
    // - idle_timeout_minutes: had masa tiada aktiviti sebelum prompt sesi tamat dipaparkan
    'session' => [
        'idle_timeout_minutes' => 30,
    ],

    // Tetapan had muat naik fail:
    // - manual_max_mb: had saiz maksimum PDF untuk modul manual pengguna
    'upload' => [
        'manual_max_mb' => 10,
    ],

    // Maklumat organisasi induk / pemilik sistem.
    // Belum digunakan sepenuhnya di semua page, tetapi disediakan untuk kegunaan akan datang.
    'organization' => [
        'name'    => 'Universiti Pertahanan Nasional Malaysia',
        'short'   => 'e-HEPA',
        'website' => 'https://www.upnm.edu.my',
    ],
];
