<?php
// classes/SystemConfigConstants.php
declare(strict_types=1);

/**
 * Constants untuk System Configuration
 */
class SystemConfigConstants {
  // Supported Languages (trimmed to only Malay and English)
  const SUPPORTED_LANGUAGES = ['ms', 'en'];
  
  // Database Types
  const ALLOWED_DB_TYPES = ['ehrmdb', 'ehrmdb_dev', 'stafdb'];
  
  // Theme Settings
  const ALLOWED_THEME_MODES = ['light', 'dark'];
  const ALLOWED_THEME_COLORS = ['light', 'dark', 'brand'];
  
  // Email Settings
  const ALLOWED_MAIL_DRIVERS = ['smtp', 'mail', 'sendmail'];
  const ALLOWED_MAIL_ENCRYPTION = ['tls', 'ssl'];
  const MAX_STRING_LENGTH = 255;
  const MIN_PORT = 1;
  const MAX_PORT = 65535;
  
  // Cache TTL (in seconds)
  const CACHE_TTL_DB_CONFIG = 60;      // 1 minit (critical - changes affect all users)
  const CACHE_TTL_EMAIL = 300;          // 5 minit
  const CACHE_TTL_LANGUAGE = 600;       // 10 minit (rarely change)
  const CACHE_TTL_MYSQL_INFO = 1800;    // 30 minit (rarely change)
  const CACHE_TTL_DB_TEST = 30;         // 30 saat (database test results)
  
  // Database Test Settings
  const DB_TEST_CONNECTION_TIMEOUT = 5;        // seconds
  const DB_TEST_CACHE_CLEANUP_MAX_AGE = 3600; // 1 hour (seconds)
  const DB_TEST_CACHE_MAX_SIZE = 1048576;     // 1MB (bytes)
  const DB_TEST_RESPONSE_TIME_FAST = 1000;    // milliseconds
  const DB_TEST_RESPONSE_TIME_SLOW = 2000;   // milliseconds
  
  // Default Values
  const DEFAULT_LANGUAGE = 'ms';
  const DEFAULT_THEME_LAYOUT = 'light';
  const DEFAULT_THEME_TOPBAR = 'light';
  const DEFAULT_THEME_SIDEBAR = 'light';
  
  // Sidebar Cache TTL (in seconds)
  const CACHE_TTL_SIDEBAR = 600; // 10 minit (modules/menus rarely change)
  
  // Allowed Icon Classes (RemixIcon - common icons used in sidebar)
  const ALLOWED_SIDEBAR_ICONS = [
    'ri-folder-fill',
    'ri-folder-line',
    'ri-dashboard-fill',
    'ri-dashboard-line',
    'ri-user-fill',
    'ri-user-line',
    'ri-settings-fill',    
    'ri-settings5-fill',
    'ri-settings-line',
    'ri-file-list-fill',
    'ri-file-list-line',
    'ri-database-fill',
    'ri-database-line',
    'ri-mail-fill',
    'ri-mail-line',
    'ri-notification-fill',
    'ri-notification-line',
    'ri-shield-fill',
    'ri-shield-line',
    'ri-group-fill',
    'ri-group-line',
    'ri-calendar-fill',
    'ri-calendar-line',
    'ri-chart-fill',
    'ri-chart-line',
    'ri-bar-chart-line',
    'ri-bar-chart-fill',
    'ri-line-chart-line',
    'ri-pie-chart-line',
    'ri-book-fill',
    'ri-book-line',
    'ri-list-check',
    'ri-list-check-2',
    'ri-file-text-fill',
    'ri-file-text-line',
    'ri-logout-box-r-fill',
    'ri-logout-box-r-line',
    'ri-home-fill',
    'ri-home-line',
    'ri-arrow-right-s-fill',
    'ri-arrow-right-s-line',
    'ri-shield-user-line',
    'ri-shield-user-fill',
    'ri-sticky-note-line',
    'ri-sticky-note-fill',
    'ri-profile-line',
    'ri-profile-fill',
    'ri-account-circle-fill',
    'ri-account-circle-line',
  ];
  
  // Audit Event Types
  const AUDIT_EVENT_EMAIL_UPDATE = 'SYSTEM_CONFIG_EMAIL_UPDATE';
  const AUDIT_EVENT_DB_UPDATE = 'SYSTEM_CONFIG_DB_UPDATE';
  const AUDIT_EVENT_THEME_UPDATE = 'SYSTEM_CONFIG_THEME_UPDATE';
  const AUDIT_EVENT_LANGUAGE_UPDATE = 'SYSTEM_CONFIG_LANGUAGE_UPDATE';
  
  // Target Types for Audit
  const AUDIT_TARGET_EMAIL = 'system_config_email';
  const AUDIT_TARGET_DB = 'system_config_database';
  const AUDIT_TARGET_THEME = 'system_config_theme';
  const AUDIT_TARGET_LANGUAGE = 'system_config_language';
}

