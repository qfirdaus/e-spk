<?php
return [

/* =====================================================
 * DASHBOARD (dash_)
 * ===================================================== */

'dash_prestasi_title'           => 'Dashboard',
'dash_sidebar_dashboard'        => 'Dashboard',

'breadcrumb_home'               => 'Dashboard',

'dash_filter_caption'           => 'View by year and/or department name',

// DataTable / paparan
'dash_prestasi_dt_length_semua' => 'All',

// Carta
'dash_chart_trend'              => 'Trend (Year)',
'dash_chart_band'               => 'Score Range Distribution',

// New dashboard-specific keys (dashboard_ prefix)
'dashboard_title'               => 'Strategic Dashboard',
'dashboard_breadcrumb'          => 'Dashboard',
'dashboard_all_teras'           => 'All Pillars',
'dashboard_deviation'           => 'Deviation:',
'dashboard_no_data_category'    => 'No data for this category.',
'dashboard_showing_projects'    => 'Showing %d to %d of %d projects',
'dashboard_chart_completed'    => 'Completed',
'dashboard_chart_on_track'     => 'On Track',
'dashboard_chart_delayed'      => 'Delayed',
'dashboard_chart_critical'     => 'Critical',
'dashboard_chart_total_label'  => 'Total',

/* Site title (used in <title>) */
'title' => 'UPNM | Project Management System - UPNM30',

// KPI
'dash_kpi_staf_rekod'           => 'No. of Staff (records)',
'dash_kpi_staf_aktif_note'      => 'Active staff (status code != 9)',

'dash_kpi_siap_label'           => '% Completed',
'dash_kpi_siap_note'            => 'Completed records',

'dash_kpi_avg_label'            => 'LPPT Average',
'dash_kpi_avg_note'             => 'Mean score (!= 0)',

'dash_kpi_median_label'         => 'LPPT Median',
'dash_kpi_median_note'          => 'Middle value (!= 0)',

'dash_kpi_beza_label'           => 'PPP–PPK Average Difference',
'dash_kpi_beza_note'            => '(+ = PPK is higher)',

'dash_kpi_belum_label'          => 'Incomplete',
'dash_kpi_belum_note'           => 'PPP/PPK/Average incomplete',

// JS
'dash_js_lppt'                  => 'LPPT',
'dash_js_bilangan'              => 'Count',


/* =====================================================
 * LOGIN & AUTH (login_, config_login_)
 * ===================================================== */

// Tajuk & navigasi
'login_title'              => 'Login',
'login.title'              => 'Login',
'login_heading'            => 'Log in',
'login_welcome'            => 'Welcome',

'login_nav.home'           => 'Home',
'login_nav.faq'            => 'FAQ',
'login_nav.directory'      => 'UPNM Directory',

// Maklumat & bantuan
'login_contact_title'      => 'Information & Contact',
'login_info'               => 'Welcome to the UPNM e-Prestasi System. Please log in to continue.',
'login_contact'            => 'If you encounter any issues, please contact the system administrator.',

// Medan borang
'login_staffid'            => 'Staff ID',
'login_userid_placeholder' => 'Example: XXXX-XX',
'login_password'           => 'Password',
'login_language'           => 'Language',

// Nota & tindakan
'login_note'               => 'For first-time login, use your Staff ID as the password.',
'login_forgot'             => 'Forgot password?',
'login_btnLogin'           => 'Login',

// Status & mesej masa
'login_locked_msg'         => 'Your account has been locked. Please try again after',
'login_seconds'            => 'seconds',

// Gagal / ralat log masuk
'login_fail_msg'           => 'Login failed. Try again:',
'login_fail_title'         => 'Login Failed',

// Validasi borang
'login_form_validation_error'
                           => 'Please enter Staff ID and password.',

// Akses & sekatan
'login_access_blocked_title'
                           => 'Access Denied',
'login_access_blocked_msg'
                           => 'Your account has been blocked. Please contact the system administrator.',

// Akaun dikunci / dibuka
'login_locked_title'       => 'Account Locked',
'login_unlocked_title'     => 'Account Unlocked',
'login_unlocked_msg'
                           => 'Your account has been unlocked. Please log in again.',

// Ralat sistem (controller / config)
'config_login_error_title'
                           => 'Login Error',
'config_login_error_message'
                           => 'An error occurred during the login process. Please try again.',


/* =====================================================
 * PROFILE (profile_)
 * ===================================================== */

// =========================
// Tajuk & Breadcrumb
// =========================
'profile_title'                 => 'Personal Information',
'profile_breadcrumb'            => 'Profile',

// =========================
// Status
// =========================
'profile_status_active'         => 'Active',
'profile_status_inactive'       => 'Inactive',

// =========================
// Aksesibiliti / Media
// =========================
'profile_avatar_alt'            => 'User avatar',

// =========================
// Maklumat Asas
// =========================
'profile_no_staf'               => 'Staff No.',
'profile_no_pekerja'            => 'Employee No.',
'profile_no_matrik'             => 'Matrik No.',
'profile_nama'                  => 'Name',
'profile_no_kad_pengenalan'     => 'Identity Card No.',
'profile_no_passport'           => 'Passport No.',
'profile_jantina'               => 'Gender',
'profile_bangsa'                => 'Ethnicity',
'profile_agama'                 => 'Religion',
'profile_jabatan'               => 'Department',
'profile_telefon'               => 'Phone No.',
'profile_fakulti'               => 'Faculty',
'profile_emel'                  => 'Email',

// =========================
// Butang & Quick Actions
// =========================
'profile_btn_copy_no_staf'      => 'Copy Staff No.',
'profile_btn_copy_email'        => 'Copy Email',
'profile_btn_email'             => 'Email',
'profile_btn_refresh'           => 'Refresh',

// =========================
// Tabs
// =========================
'profile_tabs_label'            => 'User profile tabs',
'profile_tab_profil_pengguna'   => 'Personal Information',
'profile_tab_login_aktiviti'    => 'Login Activity',
'profile_tab_jejak_audit'       => 'Audit Trail',

// =========================
// Login Activity
// =========================
'profile_login_date'            => 'Date & Time',
'profile_login_ip'              => 'IP Address',
'profile_login_device'          => 'Device',
'profile_login_duration'        => 'Duration',
'profile_login_status'          => 'Status',
'profile_login_actions'         => 'Actions',

'profile_login_active'          => 'Active',
'profile_login_ended'           => 'Ended',
'profile_login_current'         => 'Current',
'profile_login_kill_session'    => 'Terminate session',

'profile_login_aktiviti_empty'  => 'No login activity records found.',

// SweetAlert – tamatkan sesi
'profile_login_kill_confirm_title'
                                => 'Terminate Session?',
'profile_login_kill_confirm_text'
                                => 'Are you sure you want to terminate this session? The user will be forced to log out.',
'profile_login_kill_confirm_yes'
                                => 'Yes, Terminate',
'profile_login_kill_confirm_no' => 'Cancel',

'profile_login_kill_force_title'
                                => 'Your session will be terminated',
'profile_login_kill_force_text'
                                => 'You will be logged out in',

'profile_login_kill_success'
                                => 'Session terminated successfully',
'profile_login_kill_success_text'
                                => 'The session has been terminated',

'profile_login_kill_error'
                                => 'Failed to terminate session',
'profile_login_kill_error_network'
                                => 'Network error. Please try again.',
'profile_login_kill_error_no_session'
                                => 'Invalid session ID',


/* =====================================================
 * FAMILY (family_)
 * ===================================================== */
'family_title'                 => 'Family Information',   

/* =====================================================
 * SETTINGS (config result titles)
 * These keys used by TetapanSistemController for save results
 * ===================================================== */
'emel_title'        => 'Email Settings',
'emel_title_save'   => 'Email settings saved successfully',
'bahasa_title'      => 'Language Settings',
'bahasa_title_save' => 'Language settings saved successfully',
'tema_title'        => 'Theme Settings',
'tema_title_save'   => 'Theme settings updated successfully',
'config_js_btn_tutup' => 'Close',

// =========================
// Audit Trail
// =========================
'profile_jejak_audit_empty'     => 'No audit trail records found.',

'profile_audit_date'            => 'Date & Time',
'profile_audit_ip'              => 'IP Address',
'profile_audit_event_type'      => 'Event Type',
'profile_audit_outcome'         => 'Outcome',
'profile_audit_severity'        => 'Severity',
'profile_audit_actions'         => 'Actions',

'profile_audit_target_type'     => 'Target Type',
'profile_audit_target_label'    => 'Target Label',

'profile_audit_view_meta'       => 'View metadata',
'profile_audit_metadata'        => 'Metadata',
'profile_audit_metadata_content'=> 'Metadata Content',

'profile_audit_changes'         => 'Changes',
'profile_audit_change_set'      => 'Change Set',
'profile_audit_change_set_meta' => 'Change Set Metadata',

'profile_audit_field'           => 'Field',
'profile_audit_old_value'       => 'Old Value',
'profile_audit_new_value'       => 'New Value',
'profile_audit_data_type'       => 'Type',
'profile_audit_sensitive'       => 'Sensitive',

'profile_audit_no_field_changes'
                                => 'No field changes recorded.',

'profile_audit_toggle_meta'     => 'Toggle metadata',
'profile_audit_copy_meta'       => 'Copy metadata',
'profile_audit_copy_meta_btn'   => 'Copy',

'profile_audit_date_unknown'    => 'Unknown date',
'profile_audit_modal_close'     => 'Close',

// =========================
// DataTables (Profile)
 // =========================
'profile_dt_show'               => 'Show',
'profile_dt_records'            => 'records',
'profile_dt_search'             => 'Search',
'profile_dt_no_records'         => 'No records found',
'profile_dt_info'
                                => 'Showing _START_ to _END_ of _TOTAL_ records',
'profile_dt_info_empty'
                                => 'Showing 0 to 0 of 0 records',
'profile_dt_filtered'
                                => 'filtered from _MAX_ total records',
'profile_dt_previous'           => 'Previous',
'profile_dt_next'               => 'Next',
'profile_dt_error'              => 'Error loading data',
'profile_dt_error_msg'          => 'Failed to retrieve data.',

// =========================
// Lain-lain
// =========================
'profile_loading'               => 'Loading…',
'profile_js_copied'             => 'Copied',
'profile_empty_notice'
                                => 'Profile not found. Login session may have expired or record does not exist.',

/* =====================================================
 * SENARAI PENGGUNA (userList_)
 * ===================================================== */

// =========================
// Tajuk & Breadcrumb
// =========================
'pengguna_sistem'                => 'Pengguna Sistem',
'senarai_pengguna_sistem'        => 'Senarai Pengguna Sistem',

'userList_page_heading_main'     => 'System Users',
'userList_page_heading_sub'      => 'System User List',

// =========================
// Kolum Jadual
// =========================
'userList_col_no'                => 'No.',
'userList_col_name_staffid'      => 'Name (Staff ID)',
'userList_col_department'        => 'Department',
'userList_col_position'          => 'Position',
'userList_col_group'             => 'Group',
'userList_col_access'            => 'Access',
'userList_col_actions'           => 'Actions',

// =========================
// Status & Paparan
// =========================
'userList_no_records'            => 'No records',
'userList_loading_staff'         => 'Loading staff list',
'userList_no_staff_data'         => 'No staff data',
'userList_processing'            => 'Processing...',
'userList_loading'               => 'Loading...',
'userList_dt_length_menu'        => 'Show _MENU_ records',
'userList_access_granted'        => 'Allowed',
'userList_access_blocked'        => 'Blocked',
'userList_dt_search_label'       => 'Search:',
'userList_dt_info'               => 'Showing _START_ to _END_ of _TOTAL_ entries',
'userList_dt_info_empty'         => 'No records',
'userList_dt_paginate_prev'      => 'Previous',
'userList_dt_paginate_next'      => 'Next',
'userList_dt_zero_records'       => 'No matching records found',
'userList_btn_ok'                => 'OK',

// =========================
// Tindakan
// =========================
'userList_action_change_group'   => 'Change group',
'userList_action_delete_user'    => 'Delete user',

// =========================
// Modal — Umum
// =========================
'userList_modal_title'           => 'Change User Group',
'userList_modal_add_title'       => 'Add User',

'userList_modal_btn_save'        => 'Save',
'userList_modal_btn_close'       => 'Close',
'userList_modal_btn_cancel'      => 'Cancel',

// =========================
// Modal — Label Maklumat
// =========================
'userList_modal_label_name'      => 'Name',
'userList_modal_label_department'=> 'Department',
'userList_modal_label_position' => 'Position',
'userList_modal_label_group'    => 'Group',
'userList_modal_label_access'   => 'Access',
'userList_modal_label_staff'    => 'Staff',
'userList_modal_label_extra_roles' => 'Roles',
'userList_primary_role_label'   => 'Primary Role',

// =========================
// Modal — Seksyen
// =========================
'userList_modal_section_user_info'
                                => 'User Information',
'userList_modal_section_staff_info'
                                => 'Staff Information',
'userList_modal_section_settings'
                                => 'User Settings',

// =========================
// Placeholder
// =========================
'userList_modal_placeholder_select_staff'
                                => 'Select staff...',
'userList_modal_placeholder_select_group'
                                => 'Select group...',
'userList_group_filter_placeholder'
                                => '-- Select group --',
'userList_modal_add_role'       => '+ Role',
'userList_modal_extra_role_title' => 'Additional Roles',
'userList_role_none'            => 'No additional roles.',

// =========================
// Validasi / Keadaan
// =========================
'userList_staff_already_exists'  => 'Already Exists',
'userList_user_default'          => 'User',

// =========================
// Pengesahan Padam
// =========================
'userList_delete_confirm_title'  => 'Delete User?',
'userList_delete_confirm_message'
                                => 'Are you sure you want to delete this user?',
'userList_delete_confirm_warning'
                                => 'This action cannot be undone.',
'userList_delete_confirm_yes'    => 'Yes, Delete',

// =========================
// Kejayaan
// =========================
'userList_success_add'           => 'User added successfully.',
'userList_success_delete'        => 'User deleted successfully.',
'userList_success_update_roles'  => 'Additional roles updated successfully.',
'userList_success_title'         => 'Success',
'userList_success_update_group'  => 'User group and access updated successfully.',

// =========================
// Ralat
// =========================
'userList_error_title'           => 'Error',
'userList_btn_saving'            => 'Saving...',

// Buttons
'userList_sync_button'           => 'Sync Data',
'userList_sync_processing'       => 'Processing...',
'userList_add_button'            => 'Add User',

'userList_err_add_failed'        => 'Failed to add user.',
'userList_err_delete_failed'     => 'Failed to delete user.',
'userList_err_load_data'         => 'Failed to load data.',
'userList_err_load_staff'        => 'Error loading staff.',
'userList_err_param'             => 'Incomplete parameters.',
'userList_err_update_group'      => 'Failed to update group.',

'userList_err_invalid_response'
                                => 'Error: Invalid server response',
'userList_err_invalid_json'
                                => 'Error: Invalid server response (not JSON).',
'userList_err_non_json'
                                => 'Error: Server response is not JSON.',
'userList_err_server'            => 'Server error',
'userList_err_unknown'           => 'Unknown error.',
'userList_err_group_load'        => 'Failed to load group.',
'userList_err_no_permission'     => 'You do not have permission to perform this action.',
'userList_access_denied_title'   => 'Access Denied',
'userList_access_denied_text'    => 'You do not have permission to perform this action. Only {group} is allowed.',
'userList_rate_limit_title'      => 'Too Fast',
'userList_rate_limit_text'       => 'Please wait a moment before trying again.',

// =========================
// Sync
// =========================
'userList_sync_success_title'    => 'Sync Successful',
'userList_sync_success_message'  => 'Data synced successfully.',
'userList_sync_summary_title'    => 'Sync Summary',
'userList_sync_updated'          => 'Updated',
'userList_sync_skipped'          => 'Skipped',
'userList_sync_errors'           => 'Errors',
'userList_sync_total'            => 'Total',
'userList_sync_error_title'      => 'Sync Failed',
'userList_sync_error'            => 'Error during data sync.',


/* =====================================================
 * KUMPULAN PENGGUNA (userGroup_)
 * ===================================================== */

// =========================
// Tajuk & Breadcrumb
// =========================
'userGroup_page_title'              => 'User Groups',
'userGroup_intro'                   => 'List of user groups.',

// =========================
// Jadual Utama
// =========================
'userGroup_col_code'                => 'Group Code',
'userGroup_col_name'                => 'Group Name',
'userGroup_col_module_access'       => 'Module Access',
'userGroup_col_menu_access'         => 'Menu Access',
'userGroup_col_group_access'        => 'Group Access',
'userGroup_col_menu'                => 'Menu',
'userGroup_col_reorder' => 'Reorder',
'userGroup_col_status'              => 'Status',
'userGroup_col_actions'             => 'Actions',

'userGroup_no_records'              => 'No records',
'userGroup_search_label' => 'Search',
'userGroup_loading' => 'Loading data',

// =========================
// Butang & Aksi
// =========================
'userGroup_btn_add_menu'            => 'Add Menu',
'userGroup_btn_add_modul'            => 'Add Modul',
'modul_tambah'                      => 'Add Module',
'modul_tambah_title'                => 'Add Module',
'modul_nama_ms'                     => 'Module Name (BM)',
'modul_nama_en'                     => 'Module Name (EN)',
'modul_icon'                        => 'Icon',
'modul_susunan'                     => 'Order',
'modul_simpan'                      => 'Save',
'modul_batal'                       => 'Cancel',
'modul_berjaya_title'               => 'Success',
'modul_berjaya_msg'                 => 'Module added successfully.',
'modul_ralat_title'                 => 'Error',
'modul_ralat_duplikat'              => 'Module name already exists. Please use another name.',
'modul_ralat_wajib'                 => 'Module Name (BM) is required.',
'userGroup_btn_close'               => 'Close',
'userGroup_btn_save'                => 'Save',

// =========================
// Label Kecil
// =========================
'userGroup_label_module'            => 'module',
'userGroup_label_menu'              => 'menu',
'userGroup_label_modul_fallback'    => 'Module',

// =========================
// Susun Menu
// =========================
'userGroup_move_up' => 'Move up',
'userGroup_move_down' => 'Move down',

// =========================
// Status
// =========================
'userGroup_status_on' => 'ON',
'userGroup_status_off' => 'OFF',

// =========================
// Modal — Tambah / Sunting Menu
// =========================
'userGroup_modal_add_menu_title'    => 'Add Menu',
'userGroup_modal_edit_menu_title' => 'Edit Menu',

// =========================
// Modal — Akses
// =========================
'userGroup_modal_group_access_title'=> 'Group Access',
'userGroup_modal_summary_title'     => 'Access Summary',
'userGroup_modal_pick_menu_title'   => 'Select Menu',
'userGroup_modal_group_create_title'=> 'Add Group',
'userGroup_modal_group_edit_title'  => 'Edit Group',

// =========================
// Medan Borang
// =========================
'userGroup_field_group'             => 'Group',
'userGroup_field_group_code'        => 'Group Code',
'userGroup_field_group_name'        => 'Group Name',
'userGroup_field_modul'             => 'Module',
'userGroup_field_color'             => 'Color',
'userGroup_field_color_help'        => 'Pick a color visually.',
'userGroup_field_pick_module'       => 'Select Module',
'userGroup_field_pick_module_help'  => 'Select one or more modules for this group.',
'userGroup_field_pick_menu'         => 'Select Menu (depends on Module)',
'userGroup_field_pick_menu_help'    => 'Menus are shown based on selected modules.',
'userGroup_field_path'              => 'Path',
'userGroup_field_path_placeholder'  => 'example: report.php',

'userGroup_field_name_ms'           => 'Name (MS)',
'userGroup_field_name_en'           => 'Name (EN)',
'userGroup_field_name_zh'           => 'Name (ZH)',
'userGroup_field_name_ta'           => 'Name (TA)',

'userGroup_field_status'            => 'Status',
'userGroup_field_position_label' => 'Position in target module',
'userGroup_position_top' => 'Top',
'userGroup_position_bottom' => 'Bottom',

'userGroup_select_modul_placeholder'=> '— Select module —',
'userGroup_loading_modules'         => 'Loading modules…',

// =========================
// Ralat & Validasi
// =========================
'userGroup_error_unknown'           => 'Unknown error.',
'userGroup_error_network'           => 'Network error.',
'userGroup_error_reorder' => 'Failed to reorder.',
'userGroup_error_load_access' => 'Failed to load access.',
'userGroup_error_load_menu' => 'Failed to load menu.',
'userGroup_error_get_menu' => 'Failed to retrieve menu details.',
'userGroup_error_update_status' => 'Failed to update status.',

'userGroup_err_path_required'       => 'Path is required.',
'userGroup_err_group_code_name_required' => 'Please fill Group Code & Group Name.',
'userGroup_err_group_modul_required'=> 'Please select Group and Module.',
'userGroup_err_modul_required'      => 'Please select Module.',
'userGroup_err_add_menu' => 'Failed to add menu.',
'userGroup_err_save_menu' => 'Failed to save menu.',

'userGroup_bootstrap_missing'
                                      => 'Bootstrap JS not loaded. Ensure bootstrap.bundle.min.js is included.',

// =========================
// SweetAlert — Padam
// =========================
'userGroup_confirm_delete_title'    => 'Delete menu?',
'userGroup_confirm_delete_text'     => 'This action cannot be undone.',
'userGroup_confirm_title'           => 'Confirmation',
'userGroup_confirm_delete_group_text' => 'Delete group "{name}"?',
'userGroup_confirm_yes_delete'      => 'Yes, Delete',
'userGroup_confirm_yes'             => 'Yes, delete',
'userGroup_confirm_cancel'          => 'Cancel',

'userGroup_done'                    => 'Done',
'userGroup_error'                   => 'Error',
'userGroup_delete_success'          => 'Menu deleted successfully.',
'userGroup_delete_fail'             => 'Failed to delete menu.',

// =========================
// Undo (Opsyenal)
 // =========================
'userGroup_undo_btn'                => 'Undo',
'userGroup_undo_title'              => 'Undo',
'userGroup_undo_message'            => 'Menu "%s" has been deleted.',
'userGroup_undo_info'
                                      => 'Undo requires a server-side endpoint. Please contact the administrator.',

// =========================
// Carian & DataTables
// =========================
'userGroup_search_group_placeholder'=> 'Search group...',
'userGroup_dt_length_menu' => 'Show _MENU_ entries',
'userGroup_dt_info'
                                      => 'Showing _START_ to _END_ of _TOTAL_ entries',
'userGroup_dt_info_empty' => 'No entries',
'userGroup_dt_info_filtered'
                                      => '(filtered from _MAX_ total entries)',
'userGroup_dt_paginate_first'       => 'First',
'userGroup_dt_paginate_last' => 'Last',
'userGroup_dt_paginate_next'        => 'Next',
'userGroup_dt_paginate_previous'    => 'Previous',
'userGroup_edit_group'              => 'Edit Group',
'userGroup_delete_group'            => 'Delete Group',
'userGroup_info_title'              => 'Notice',
'userGroup_info_select_group_first' => 'Please select a group first using the Group Access button.',
'userGroup_btn_menu_label'          => 'Menu',
'userGroup_btn_module_label'        => 'Module',
'userGroup_btn_group_label'         => 'Group',


/* =====================================================
 * MATRIKS AKSES (access_)
 * ===================================================== */

// =========================
// Tajuk & Pengenalan
// =========================
'access_title'        => 'Access Matrix',
'access_intro'        => 'Read-only access matrix for system menus.',

// =========================
// Jadual
// =========================
'access_col_no'       => '#',
'access_menu'         => 'Menu',
'access_path'         => 'Path',
'access_modul'        => 'Module',
'access_user_level'   => 'User Level',

// =========================
// Tahap Pengguna
// =========================
'access_super_admin'  => 'Super Administrator',
'access_urusetia'     => 'Secretariat',
'access_kerani'       => 'Clerk',

// =========================
// Status Akses
// =========================
'access_ada'          => 'Has Access',
'access_tiada'        => 'No Access',

// =========================
// Paparan
// =========================
'access_no'           => 'No records',


/* =====================================================
 * TETAPAN SISTEM (config_, config_js_, config_db_)
 * ===================================================== */

/* =========================
 * Tajuk
 * ========================= */
'config_system' => 'System Configuration',

/* =========================
 * Tab Navigasi
 * ========================= */
'config_tab_emel'   => 'Email',
'config_tab_db'     => 'Database',
'config_tab_tema'   => 'Theme',
'config_tab_bahasa' => 'Language',

/* =========================
 * TAB EMEL
 * ========================= */
'config_tab_emel_header_setting'        => 'Email Server Configuration',
'config_tab_emel_driver'                => 'Email Driver',
'config_tab_emel_host'                  => 'Email Host',
'config_tab_emel_port'                  => 'Port',
'config_tab_emel_encryption'            => 'Encryption',
'config_tab_emel_sel_tiada'             => 'None',

'config_tab_emel_header_emel'            => 'Email Account Details',
'config_tab_emel_account_emel'           => 'Email Account (Username)',
'config_tab_emel_katalaluan_emel'        => 'Email Password',
'config_tab_emel_password_hint'          => 'Leave blank to keep current password',
'config_tab_emel_from'                   => 'Email From',
'config_tab_emel_from_name'              => 'Sender Name',

'config_tab_emel_uji_emel'               => 'Test Email Connection',
'config_tab_emel_simpan_tetapan_emel'    => 'Save Email Settings',

/* =========================
 * TAB DATABASE
 * ========================= */
'config_tab_db_header'                   => 'Sybase (Select One Only)',
'config_tab_db_sybase_header'            => 'Only one Sybase connection can be active at a time.',
'config_tab_db_sybase_sambungan'         => 'Connection Name',
'config_tab_db_sybase_keterangan'        => 'Description',

'config_tab_db_sybase_nama_production'   => 'e-HRMDB (Production)',
'config_tab_db_sybase_nama_production_penerangan'
                                        => 'Primary database',

'config_tab_db_sybase_nama_development'  => 'e-HRMDB (Development)',
'config_tab_db_sybase_nama_development_penerangan'
                                        => 'Development database',

'config_tab_db_sybase_smp'               => 'STAFDB',
'config_tab_db_sybase_smp_penerangan'    => 'SMP database (no longer used)',

'config_tab_db_mysql'                    => 'MySQL (Always Active)',
'config_tab_db_mysql_header'             => 'This connection is always active for the main system.',
'config_tab_db_mysql_sambungan'          => 'Field',
'config_tab_db_mysql_keterangan'         => 'Information',
'config_tab_db_mysql_host'               => 'Host',
'config_tab_db_mysql_user'               => 'User',
'config_tab_db_mysql_status'             => 'Status',

'config_tab_db_simpan_tetapan_db'        => 'Save Database Settings',

/* =========================
 * TAB TEMA
 * ========================= */
'config_tab_tema_header'                 => 'Default Theme Settings',
'config_tab_tema_header_details'         => 'This theme will be applied to users who have not customized their own theme.',
'config_tab_tema_komponen'               => 'Component',
'config_tab_tema_pilihan'                => 'Theme Option',
'config_tab_tema_penerangan'             => 'Description',

// Layout
'config_tab_tema_komponen_layout'        => 'Layout Mode',
'config_tab_tema_pilihan_layout_terang'  => 'Light',
'config_tab_tema_pilihan_layout_gelap'   => 'Dark',
'config_tab_tema_penerangan_layout_terang_penerangan'
                                        => 'Fully light design — standard light mode.',
'config_tab_tema_penerangan_layout_gelap_penerangan'
                                        => 'Dark layout — suitable for night use.',

// Topbar
'config_tab_tema_komponen_topbar'        => 'Topbar Color',
'config_tab_tema_pilihan_topbar_terang'  => 'Light',
'config_tab_tema_pilihan_topbar_gelap'   => 'Dark',
'config_tab_tema_pilihan_layout_brand'   => 'Brand',
'config_tab_tema_penerangan_topbar_terang_penerangan'
                                        => 'Light topbar, suitable for light mode.',
'config_tab_tema_penerangan_topbar_gelap_penerangan'
                                        => 'Dark topbar, suitable for night or dark mode.',
'config_tab_tema_penerangan_topbar_brand_penerangan'
                                        => 'Topbar follows system brand color.',

// Sidebar
'config_tab_tema_komponen_sidebar'       => 'Sidebar Color',
'config_tab_tema_pilihan_sidebar_terang' => 'Light',
'config_tab_tema_pilihan_sidebar_gelap'  => 'Dark',
'config_tab_tema_pilihan_sidebar_brand'  => 'Brand',
'config_tab_tema_penerangan_sidebar_terang_penerangan'
                                        => 'Light sidebar with clean white background.',
'config_tab_tema_penerangan_sidebar_gelap_penerangan'
                                        => 'Dark sidebar, comfortable for night mode.',
'config_tab_tema_penerangan_sidebar_brand_penerangan'
                                        => 'Sidebar uses main system brand color.',

'config_tab_db_simpan_tetapan_tema'      => 'Save Theme Settings',

/* =========================
 * TAB BAHASA
 * ========================= */
'config_tab_bahasa_header'               => 'Available Languages',
'config_tab_bahasa_header_details'       => 'Select the languages to be enabled in the system.',
'config_tab_bahasa_kodBahasa'            => 'Language Code',
'config_tab_bahasa_peneranganBahasa'     => 'Language Description',
'config_tab_bahasa_simpan_tetapan_bahasa'=> 'Save Language Settings',

/* =========================
 * JS / SWEETALERT
 * ========================= */
'config_js_loading'              => 'Loading…',
'config_js_memproses'            => 'Processing…',

'config_js_confirm_emel'         => 'Are you sure you want to save email settings?',
'config_js_confirm_db'           => 'Are you sure you want to save database settings?',
'config_js_confirm_tema'         => 'Are you sure you want to save default theme settings?',
'config_js_confirm_bahasa'       => 'Are you sure you want to save the active language list?',

'config_js_btn_ya_simpan'        => 'Yes, save',
'config_js_btn_ya_teruskan'      => 'Yes, continue',
'config_js_btn_ok'               => 'OK',
'config_js_btn_cancel'           => 'Cancel',

// Uji Emel
'config_js_confirm_uji_emel'     => 'Are you sure you want to test this email connection?',
'config_js_input_uji_emel'       => 'Enter Test Email',
'config_js_label_uji_emel'       => 'Email address for test delivery',
'config_js_placeholder_uji_emel' => 'e.g.: apps_email@upnm.edu.my',
'config_js_valid_emel_kosong'    => 'Email address cannot be empty',
'config_js_uji_emel_btn'         => 'Test Now',
'config_js_uji_emel_btn_loading' => 'Testing…',
'config_js_uji_emel_btn_default' => 'Test Email Connection',

// Status JS
'config_js_berjaya'              => 'Success',
'config_js_ralat'                => 'Error',
'config_js_emel_berjaya'         => '✅ Email sent successfully.',
'config_js_emel_uji_berjaya'     => 'Test email successfully sent to :email.',
'config_js_emel_gagal'           => '❌ Failed to send email.',
'config_js_emel_uji_gagal'       => '❌ Failed to send email: :error',
'config_js_ralat_sistem'         => '❌ System error while testing connection.',
'config_js_tiada_bahasa'         => 'No Language Selected',
'config_js_pilih_bahasa'         => 'Please select at least one language.',

/* =========================
 * ALERT DB (Controller)
 * ========================= */
'config_db_sambungan_tidak_sah'   => 'Invalid Connection',
'config_db_pilihan_tidak_wujud'   => 'Selected connection does not exist.',
'config_db_sambungan_gagal'       => 'Connection Failed',
'config_db_sambungan_gagal_msg'   => 'Unable to connect to database ":db".',
'config_db_sambungan_ok'          => 'Connection Successful',
'config_db_sambungan_ok_msg'      => 'Connection ":db" updated successfully.',
'config_db_ralat_simpan'          => 'Save Error',
'config_db_ralat_simpan_msg'      => 'Failed to save settings to file.',

'config_alert_title'              => 'Are you sure?',
'config_alert_no'                 => 'Cancel',
'config_alert_bahasa_berjaya'     => 'Language settings saved successfully',


/* =====================================================
 * UI GLOBAL (theme_, topbar_, sidebar_, footer_, logout_)
 * ===================================================== */

/* =========================
 * TEMA (Offcanvas / Global)
 * ========================= */
'theme_title'                 => 'Theme Settings',
'theme_close'                 => 'Close',
'theme_customize'             => 'Customize',
'theme_customize_sub'         => 'Color, menu, and other settings',

'theme_color_scheme'          => 'Color Scheme',
'theme_light'                 => 'Light',
'theme_dark'                  => 'Dark',

'theme_topbar_color'          => 'Topbar Color',
'theme_menu_color'            => 'Sidebar/Menu Color',
'theme_brand'                 => 'Brand',

'theme_note_preview'          => 'Changes here are preview only. To save permanently, use the System Settings page.',
'theme_note_preview_fallback' => 'Changes here are preview only. To save permanently, use the System Settings page.',
'theme_applied'               => 'Theme Applied',
'theme_'                      => 'Theme',

/* =========================
 * TOPBAR
 * ========================= */
'topbar_welcome'              => 'Welcome!',
'topbar_keluar'               => 'Logout',

// Profil & menu
'topbar_profile'              => 'My Profile',
'topbar_settings'             => 'Settings',
'topbar_support'              => 'Support',
'topbar_lock_screen'          => 'Lock Screen',

// Notifikasi
'topbar_notification_title'   => 'Notifications',
'topbar_clear_all'            => 'Clear All',
'topbar_today'                => 'Today',
'topbar_view_all'             => 'View All',

// Contoh notifikasi
'topbar_sample_noti_title'    => 'Sample Notification',
'topbar_sample_noti_desc'     => 'e-Prestasi system notification',
'topbar_time_1min_ago'        => '1 minute ago',
'topbar_switch_role'          => 'Switch Role',
'topbar_switch_role_title'    => 'Switch Role',
'topbar_switch_role_select'   => 'Select Role',
'topbar_switch_role_primary_label' => 'Primary role',
'topbar_switch_role_primary_tag'   => 'Primary Role',
'topbar_switch_role_none'     => 'No other roles are allowed.',
'topbar_switch_role_err_select' => 'Please select a role.',
'topbar_switch_role_err_invalid' => 'Please select a valid role.',
'topbar_switch_role_saving'   => 'Saving...',
'topbar_switch_role_success_title' => 'Role {role}',
'topbar_switch_role_success_text'  => 'Display and system access have been updated according to the newly selected role, <strong>{role}</strong>.',

/* =========================
 * SIDEBAR
 * ========================= */
'sidebar_main'                => 'Main',
'sidebar_dashboard'           => 'Dashboard',
'sidebar_dashboard_stats'     => 'Statistics',
'sidebar_modul'               => 'System Modules',
'sidebar_kawalan'             => 'System Control',
'sidebar_keluar'              => 'Logout',

'sidebar_profile_empty'       => 'Profile not found',
'sidebar_loading'             => 'Loading...',

/* =========================
 * FOOTER
 * ========================= */
'footer_it'                   => 'BTMK | Digital Application Section',
'footer_about'                => 'About Us',
'footer_help'                 => 'Help',
'footer_contact'              => 'Contact Us',

'footer_content_updating_title'
                              => 'Information',
'footer_content_updating'     => 'Content is being updated.',
'footer_content_updating_ok'  => 'OK',

/* =========================
 * LOGOUT (SweetAlert)
 * ========================= */
'logout_alert_title'          => 'Confirmation',
'logout_alert_text'           => 'Are you sure you want to log out?',
'logout_alert_yes'            => 'Yes, log out',
'logout_alert_no'             => 'Cancel',

'logout_title'                => 'Logged Out Successfully',
'logout_msg'                  => 'You have logged out of the system.',


/* =====================================================
 * KUMPULAN PENGGUNA (userGroup_)
 * ===================================================== */

/* =========================
 * Butang & Aksi
 * ========================= */
'userGroup_edit' => 'Edit',
'userGroup_delete'                  => 'Delete',


/* =========================
 * Label Kecil
 * ========================= */

/* =========================
 * Status
 * ========================= */

/* =========================
 * Modal — Menu
 * ========================= */





/* =========================
 * Modal — Akses Kumpulan
 * ========================= */

/* =========================
 * Undo (Padam Menu)
 * ========================= */
'userGroup_undo_info'
                                    => 'Undo requires a server-side endpoint. Please contact the administrator.',

/* =========================
 * SweetAlert — Padam
 * ========================= */


/* =========================
 * Ralat
 * ========================= */


'userGroup_bootstrap_missing'
                                    => 'Bootstrap JS not loaded. Ensure bootstrap.bundle.min.js is included.',

/* =========================
 * DataTables
 * ========================= */
'userGroup_dt_info'
                                    => 'Showing _START_ to _END_ of _TOTAL_ entries',
'userGroup_dt_info_filtered'
                                    => '(filtered from _MAX_ total entries)',



/* =====================================================
 * KUNCI PEMANTAUAN & SISTEM
 * ===================================================== */
'monitoring_setup_title' => 'Monitoring Setup',
'monitoring_master_data' => 'Master Data',
'monitoring_teras_register' => 'Teras Register',
'monitoring_user_management' => 'User Management',
'monitoring_system_settings' => 'System Settings',
'manage_users' => 'Manage Users',
'total_users' => 'Total Users',
'role_admin' => 'Administrator',
'role_pic' => 'Person in Charge',
'settings_description' => 'Configure system settings and preferences',
'user_management_note' => 'Add, edit, or remove system users',

/* =====================================================
 * KUNCI UMUM / PELBAGAI
 * ===================================================== */
'actions' => 'Actions',
'add_teras' => 'Add Teras',
'btn_save' => 'Save',
'enter_program_name' => 'Enter program name',
'formula_note' => 'Formula for calculation',
'generate_report' => 'Generate Report',
'good' => 'Good',
'implementation_year' => 'Implementation Year',
'monthly' => 'Monthly',
'no_data' => 'No data available',
'official_report_generation' => 'Official Report Generation',
'program_description' => 'Program Description',
'program_name' => 'Program Name',
'program_objective' => 'Program Objective',
'progress_formula' => 'Progress Formula',
'quarterly' => 'Quarterly',
'reporting_cycle' => 'Reporting Cycle',
'teras_code' => 'Teras Code',
'teras_name' => 'Teras Name',
'type' => 'Type',
'ujian_db' => 'Database Test',
'update_settings' => 'Update Settings',
'weekly' => 'Weekly',

/* =====================================================
 * KUNCI PENGURUSAN PROJEK
 * ===================================================== */
'project_name' => 'Project Name',
'project_code' => 'Project Code',
'teras_project_name' => 'Teras / Project Name',
'update_status' => 'Update Status',
'update_project_status' => 'Update Project Status',
'update_project' => 'Update Project',
'create_new_project' => 'Create New Project',
'save_report' => 'Save Report',
'save_changes' => 'Save Changes',
'save_program' => 'Save Program',
'teras_code_placeholder' => 'Teras Code (e.g.: TS-01)',

/* =====================================================
 * KUNCI BUTANG/TINDAKAN UMUM
 * ===================================================== */
'btn_update' => 'Update',
'btn_close' => 'Close',
'updating' => 'Updating',


/* =====================================================
 * MESEJ MEMUATKAN/STATUS
 * ===================================================== */
'loading_user_list' => 'Loading user list...',

/* =====================================================
 * DASHBOARD (BASE)
 * ===================================================== */
'dashboard_title' => 'Dashboard',
'dashboard_breadcrumb' => 'Dashboard',
'dashboard_welcome' => 'Welcome',
'dashboard_last_login' => 'Last login',
'dashboard_tabs_label' => 'Dashboard tabs',
'dashboard_tab_overview' => 'Overview',
'dashboard_tab_activity' => 'My Activity',
'dashboard_tab_tasks' => 'My Tasks',
'dashboard_tab_access' => 'Access & Roles',
'dashboard_tab_security' => 'Security',
'dashboard_tab_health' => 'System Health Check',
'dashboard_tab_overview_empty' => 'Overview content will appear here.',
'dashboard_tab_activity_empty' => 'My Activity content will appear here.',
'dashboard_tab_tasks_empty' => 'My Tasks content will appear here.',
'dashboard_tab_access_empty' => 'Access & Roles content will appear here.',
'dashboard_tab_security_empty' => 'Security content will appear here.',
'dashboard_health_col_check' => 'Check',
'dashboard_health_col_status' => 'Status',
'dashboard_health_col_info' => 'Info',
'dashboard_resources_title' => 'System Resources',
'dashboard_refresh' => 'Refresh',
'dashboard_resources_col_resource' => 'Resource',
'dashboard_resources_col_usage' => 'Usage',
'dashboard_resources_col_status' => 'Status',
'dashboard_announcements_title' => 'Announcements',
'dashboard_announcements_sub' => 'System notices',
'dashboard_notice' => 'Notice',
'dashboard_announcements_empty' => 'No announcements.',
'dashboard_status_ok' => 'OK',
'dashboard_status_warning' => 'Warning',
'dashboard_status_critical' => 'Critical',
'dashboard_status_unknown' => 'Unknown',
'dashboard_status_degraded' => 'Degraded',
'dashboard_resource_cpu' => 'CPU',
'dashboard_resource_memory' => 'Memory',
'dashboard_resource_disk' => 'Disk',
'dashboard_health_db' => 'Database',
'dashboard_health_connected' => 'Connected',
'dashboard_health_conn_failed' => 'Connection failed',
'dashboard_health_app' => 'Application',
'dashboard_health_bootstrap_ok' => 'Bootstrap loaded',
'dashboard_health_config_incomplete' => 'Configuration incomplete',
'dashboard_health_storage' => 'Storage',
'dashboard_health_storage_free' => '%s%% free',
'dashboard_health_unavailable' => 'Unavailable',
'dashboard_health_cache' => 'Cache',
'dashboard_health_enabled' => 'Enabled',
'dashboard_health_readonly' => 'Read-only',
'dashboard_health_disabled' => 'Disabled',
'dashboard_env_production' => 'production',
'dashboard_env_development' => 'development',
'dashboard_env_debug_on' => 'debug ON',
'dashboard_env_debug_off' => 'debug OFF',
'dashboard_health_audit' => 'Audit/Log',
'dashboard_health_writable' => 'Writable',
'dashboard_health_not_writable' => 'Not writable',
'dashboard_health_cron' => 'Scheduled Jobs',
'dashboard_health_unknown' => 'Unknown',
'dashboard_health_tz' => 'Time & Timezone',

/* =====================================================
 * FAQ
 * ===================================================== */
'faq_title' => 'Frequently Asked Questions (FAQ)',
'faq_heading' => 'System FAQ',
'faq_intro' => 'Refer to general usage guidance. Choose a category or use search to find relevant answers.',
'faq_label_category' => 'Category',
'faq_placeholder_cari' => 'Search within selected category…',
'faq_tiada_padamu' => 'No matching result found. Try another keyword.',
'faq_count_display' => 'of',
'faq_count_soalan' => 'questions shown',
'faq_cat_semua' => 'All',
'faq_cat_account_access' => 'Account & Access',
'faq_cat_navigation' => 'Navigation & Usage',
'faq_cat_profile_settings' => 'Profile & Settings',
'faq_cat_user_management' => 'User Management',
'faq_cat_group_management' => 'User Groups',
'faq_cat_support' => 'Support',

'faq_item_01_q' => 'How do I log in to the system?',
'faq_item_01_a' => 'Use your <b>Staff ID</b> and <b>password</b> on the login page. If this is your first login, follow the instructions shown on the login page or contact the system administrator.',
'faq_item_02_q' => 'Why can’t I log in?',
'faq_item_02_a' => 'Common causes are incorrect password, blocked account, or group access not yet assigned. Recheck your ID/password. If the issue persists, contact the system administrator.',
'faq_item_03_q' => 'How is menu access determined?',
'faq_item_03_a' => 'Each user is assigned to a specific <b>user group</b>. This group controls which modules and menus are visible. If a menu is missing, ask the administrator to review your group access.',
'faq_item_04_q' => 'Where can I view a quick system overview?',
'faq_item_04_a' => 'Use the <b>Dashboard</b> page for a quick overview. It helps you understand current status and navigate to key modules faster.',
'faq_item_05_q' => 'Why is my sidebar menu different from other users?',
'faq_item_05_a' => 'Sidebar menus are displayed based on user group and role. This is expected behavior so users only see functions relevant to their responsibilities.',
'faq_item_06_q' => 'How can I quickly find a function in the system?',
'faq_item_06_a' => 'Use module navigation in the sidebar and select the related page. On data table pages, use the search box at the top to filter records quickly.',
'faq_item_07_q' => 'How do I update language settings?',
'faq_item_07_a' => 'You can change language from the topbar or from the <b>Profile</b> page. Your language preference is saved to your account.',
'faq_item_08_q' => 'How do I change display theme?',
'faq_item_08_a' => 'Go to the <b>Profile</b> page to adjust theme settings such as display mode and interface colors. Changes will apply to your session.',
'faq_item_09_q' => 'What does Audit Trail on the Profile page show?',
'faq_item_09_a' => 'Audit Trail displays important activity records such as data updates and system actions. It supports review and security monitoring.',
'faq_item_10_q' => 'How can an administrator add or update users?',
'faq_item_10_a' => 'Administrators can use the <b>User List</b> page to add users, change groups, and control access status. All changes should follow your organization’s internal policy.',
'faq_item_11_q' => 'What does user access status mean?',
'faq_item_11_a' => 'Access status defines whether a user is allowed to enter the system. If blocked, the user cannot log in until reactivated by an administrator.',
'faq_item_12_q' => 'What is the purpose of the User Groups page?',
'faq_item_12_a' => 'This page manages group structure, group color identity, and module/menu access for each group. It simplifies centralized access management.',
'faq_item_13_q' => 'Can a user group be deleted?',
'faq_item_13_a' => 'A group can only be deleted when it has no active module/menu access and no users assigned to it. This prevents operational disruption.',
'faq_item_14_q' => 'What should I do if a system error occurs?',
'faq_item_14_a' => 'Record the error message, time of occurrence, and action being performed. Send this information to the system administrator for further investigation.',
'faq_item_15_q' => 'Who should I contact for access or configuration issues?',
'faq_item_15_a' => 'Contact your internal system administrator. Access, user-group, and system configuration issues usually require administrator privileges.',

];
