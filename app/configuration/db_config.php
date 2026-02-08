<?php
return [

    // ===================================================
    // ✅ MySQL Utama
    // ===================================================
    'mysql' => [
        'driver' => 'mysql',
        'dsn'    => 'mysql:host=172.16.2.141;dbname=ebasedb;charset=utf8mb4',
        'user'   => 'dbapps', 
        'pass'   => '@plikasiDigit@l25',
    ],

    // ===================================================
    // ✅ Sybase: EHRMDB - PRODUCTION 172.16.2.14
    // ===================================================

    // 🔸 via dblib (Docker/Linux)
    'sybase_ehrmdb_dblib' => [
        'driver' => 'dblib',
        'dsn'    => 'dblib:host=172.16.2.14:5004;dbname=ehrmdb',
        'user'   => 'expdir',
        'pass'   => 'X@directory1',
    ],

    // 🔸 via DSN (Windows/Pejabat)
    'sybase_ehrmdb_dsn' => [
        'driver' => 'odbc',
        'dsn'    => 'odbc:dsn_sybase_ehrmdb',
        'user'   => 'expdir',
        'pass'   => 'X@directory1',
    ],

    // ===================================================
    // ✅ Sybase: EHRMDB (Development) 172.16.2.8
    // ===================================================

    // 🔸 via dblib (Docker/Linux)
    'sybase_ehrmdb_dev_dblib' => [
        'driver' => 'dblib',
        'dsn'    => 'dblib:host=172.16.2.8:7000;dbname=ehrmdb',
        'user'   => 'ehrm',
        'pass'   => 'eHRM@2025',
    ],

    // 🔸 via DSN (Windows/Dev)
    'sybase_ehrmdb_dev_dsn' => [
        'driver' => 'odbc',
        'dsn'    => 'odbc:dsn_sybase_ehrmdb_dev',
        'user'   => 'ehrm',
        'pass'   => 'eHRM@2025',
    ],

    // ===================================================
    // ✅ Sybase: STAFDB - OLD PRODUCTION
    // ===================================================

    // 🔸 via dblib (Docker/Linux)
    'sybase_stafdb_dblib' => [
        'driver' => 'dblib',
        'dsn'    => 'dblib:host=172.16.2.14:5004;dbname=stafdb',
        'user'   => 'dba_staf',
        'pass'   => 'noP@ssword123',
    ],

    // 🔸 via DSN (Windows/Pejabat)
    'sybase_stafdb_dsn' => [
        'driver' => 'odbc',
        'dsn'    => 'odbc:dsn_sybase_stafdb',
        'user'   => 'dba_staf',
        'pass'   => 'noP@ssword123',
    ],


];
