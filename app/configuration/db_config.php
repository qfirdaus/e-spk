<?php
return [

    // ===================================================
    // ✅ MySQL Utama
    // ===================================================
    'mysql' => [
        'driver' => 'mysql',
        'dsn'    => 'mysql:host=172.16.2.141;dbname=ehepadb;charset=utf8mb4',
        'user'   => 'sokongan', 
        'pass'   => '_Sok0ng@n@2025?',
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
        'pass'   => 'nopassword',
    ],

    // 🔸 via DSN (Windows/Dev)
    'sybase_ehrmdb_dev_dsn' => [
        'driver' => 'odbc',
        'dsn'    => 'odbc:dsn_sybase_ehrmdb_dev',
        'user'   => 'ehrm',
        'pass'   => 'nopassword',
    ],

    // ===================================================
    // ✅ Sybase: ASISDB - PRODUCTION 172.16.2.14
    // ===================================================

    // 🔸 via dblib (Docker/Linux)
    'sybase_asisdb_dblib' => [
        'driver' => 'dblib',
        'dsn'    => 'dblib:host=172.16.2.14:5004;dbname=asisdb',
        'user'   => 'dba_student',
        'pass'   => 'mnpu123',
    ],

    // 🔸 via DSN (Windows/Pejabat)
    'sybase_asisdb_dsn' => [
        'driver' => 'odbc',
        'dsn'    => 'odbc:dsn_sybase_asisdb',
        'user'   => 'dba_student',
        'pass'   => 'mnpu123',
    ],

    // ===================================================
    // ✅ Sybase: ASISDB (Development) 172.16.2.8
    // ===================================================

    // 🔸 via dblib (Docker/Linux)
    'sybase_asisdb_dev_dblib' => [
        'driver' => 'dblib',
        'dsn'    => 'dblib:host=172.16.2.8:7000;dbname=asisdb',
        'user'   => 'dba_student',
        'pass'   => 'mnpu123',
    ],

    // 🔸 via DSN (Windows/Dev)
    'sybase_asisdb_dev_dsn' => [
        'driver' => 'odbc',
        'dsn'    => 'odbc:dsn_sybase_asisdb_dev',
        'user'   => 'dba_student',
        'pass'   => 'mnpu123',
    ],

    // ===================================================
    // ✅ Sybase: STAFDB - OLD PRODUCTION
    // ===================================================

    // 🔸 via dblib (Docker/Linux)
    'sybase_stafdb_dblib' => [
        'driver' => 'dblib',
        'dsn'    => 'dblib:host=172.16.2.14:5004;dbname=stafdb',
        'user'   => 'dba_staf',
        'pass'   => 'nopassword',
    ],

    // 🔸 via DSN (Windows/Pejabat)
    'sybase_stafdb_dsn' => [
        'driver' => 'odbc',
        'dsn'    => 'odbc:dsn_sybase_stafdb',
        'user'   => 'dba_staf',
        'pass'   => 'nopassword',
    ],


];
