<?php
return [

/* =====================================================
 * DASHBOARD (dash_)
 * ===================================================== */

'dash_prestasi_title'           => 'Papan Pemuka',
'dash_sidebar_dashboard'        => 'Papan Pemuka',

'breadcrumb_home'               => 'Papan Pemuka',

'dash_filter_caption'           => 'Lihat mengikut tahun dan/atau nama jabatan',

// DataTable / paparan
'dash_prestasi_dt_length_semua' => 'Semua',

// Carta
'dash_chart_trend'              => 'Tren (Tahun)',
'dash_chart_band'               => 'Taburan Julat Skor',

// New dashboard-specific keys (dashboard_ prefix)
'dashboard_title'               => 'Dashboard Strategik',
'dashboard_breadcrumb'          => 'Dashboard',
'dashboard_all_teras'           => 'Semua Teras',
'dashboard_deviation'           => 'Penyimpangan:',
'dashboard_no_data_category'    => 'Tiada data untuk kategori ini.',
'dashboard_showing_projects'    => 'Menunjukkan %d hingga %d daripada %d projek',
'dashboard_chart_completed'    => 'Selesai',
'dashboard_chart_on_track'     => 'Ikut Jadual',
'dashboard_chart_delayed'      => 'Lewat',
'dashboard_chart_critical'     => 'Kritikal',
'dashboard_chart_total_label'  => 'Jumlah',

/* Site title (used in <title>) */
'title' => 'UPNM | Sistem Pengurusan Projek - UPNM30',

// KPI
'dash_kpi_staf_rekod'           => 'Bil. Staf (rekod)',
'dash_kpi_staf_aktif_note'      => 'Staf aktif (kodstatus != 9)',

'dash_kpi_siap_label'           => '% Siap',
'dash_kpi_siap_note'            => 'Rekod lengkap',

'dash_kpi_avg_label'            => 'Purata LPPT',
'dash_kpi_avg_note'             => 'Min markah (!= 0)',

'dash_kpi_median_label'         => 'Median LPPT',
'dash_kpi_median_note'          => 'Nilai pertengahan (!= 0)',

'dash_kpi_beza_label'           => 'Purata beza PPP–PPK',
'dash_kpi_beza_note'            => '(+ = PPK lebih tinggi)',

'dash_kpi_belum_label'          => 'Belum Lengkap',
'dash_kpi_belum_note'           => 'PPP/PPK/Purata belum lengkap',

// JS
'dash_js_lppt'                  => 'LPPT',
'dash_js_bilangan'              => 'Bilangan',


/* =====================================================
 * LOGIN & AUTH (login_, config_login_)
 * ===================================================== */

// Tajuk & navigasi
'login_title'              => 'Log Masuk',
'login.title'              => 'Log Masuk',
'login_heading'            => 'Log Masuk',
'login_welcome'            => 'Selamat Datang',

'login_nav.home'           => 'Laman Utama',
'login_nav.faq'            => 'Soalan Lazim',
'login_nav.directory'      => 'Direktori UPNM',

// Maklumat & bantuan
'login_contact_title'      => 'Maklumat & Hubungi',
'login_info'               => 'Selamat datang. Sila log masuk untuk meneruskan.',
'login_contact'            => 'Sekiranya anda menghadapi sebarang masalah, sila hubungi pentadbir sistem.',

// Medan borang
'login_staffid'            => 'ID Staf',
'login_userid_placeholder' => 'Contoh: XXXX-XX',
'login_password'           => 'Katalaluan',
'login_language'           => 'Bahasa',

// Nota & tindakan
'login_note'               => 'Untuk log masuk kali pertama, gunakan ID Staf anda sebagai katalaluan.',
'login_forgot'             => 'Lupa katalaluan?',
'login_btnLogin'           => 'Log Masuk',

// Status & mesej masa
'login_locked_msg'         => 'Akaun anda telah dikunci. Sila cuba lagi selepas',
'login_seconds'            => 'saat',

// Gagal / ralat log masuk
'login_fail_msg'           => 'Log masuk gagal. Cuba lagi:',
'login_fail_title'         => 'Log Masuk Gagal',

// Validasi borang
'login_form_validation_error'
                           => 'Sila masukkan ID dan kata laluan.',

// Akses & sekatan
'login_access_blocked_title'
                           => 'Akses Ditolak',
'login_access_blocked_msg'
                           => 'Akaun anda telah disekat. Sila hubungi pentadbir sistem untuk bantuan.',

// Akaun dikunci / dibuka
'login_locked_title'       => 'Akaun Dikunci',
'login_unlocked_title'     => 'Akaun Dibuka',
'login_unlocked_msg'
                           => 'Akaun anda telah dibuka semula. Sila log masuk semula.',

// Ralat sistem (controller / config)
'config_login_error_title'
                           => 'Ralat Log Masuk',
'config_login_error_message'
                           => 'Berlaku ralat semasa proses log masuk. Sila cuba lagi.',


/* =====================================================
 * PROFILE (profile_)
 * ===================================================== */

// =========================
// Tajuk & Breadcrumb
// =========================
'profile_title'                 => 'Maklumat Peribadi',
'profile_breadcrumb'            => 'Profil',

// =========================
// Status
// =========================
'profile_status_active'         => 'Aktif',
'profile_status_inactive'       => 'Tidak Aktif',

// =========================
// Aksesibiliti / Media
// =========================
'profile_avatar_alt'            => 'Avatar pengguna',

// =========================
// Maklumat Asas
// =========================
'profile_no_staf'               => 'No. Staf',
'profile_no_pekerja'            => 'No. Pekerja',
'profile_no_matrik'             => 'No. Matrik',
'profile_nama'                  => 'Nama',
'profile_no_kad_pengenalan'     => 'No. Kad Pengenalan',
'profile_no_passport'           => 'No. Passport',
'profile_jantina'               => 'Jantina',
'profile_bangsa'                => 'Bangsa',
'profile_agama'                 => 'Agama',
'profile_jabatan'               => 'Jabatan',
'profile_telefon'               => 'No. Telefon',
'profile_fakulti'               => 'Fakulti',
'profile_emel'                  => 'Emel',

// =========================
// Butang & Quick Actions
// =========================
'profile_btn_copy_no_staf'      => 'Salin No. Staf',
'profile_btn_copy_email'        => 'Salin Emel',
'profile_btn_email'             => 'Emel',
'profile_btn_refresh'           => 'Muat Semula',

// =========================
// Tabs
// =========================
'profile_tabs_label'            => 'Tab profil pengguna',
'profile_tab_profil_pengguna'   => 'Maklumat Peribadi',
'profile_tab_login_aktiviti'    => 'Login Aktiviti',
'profile_tab_jejak_audit'       => 'Jejak Audit',

// =========================
// Login Activity
// =========================
'profile_login_date'            => 'Tarikh & Masa',
'profile_login_ip'              => 'Alamat IP',
'profile_login_device'          => 'Peranti',
'profile_login_duration'        => 'Tempoh',
'profile_login_status'          => 'Status',
'profile_login_actions'         => 'Tindakan',

'profile_login_active'          => 'Aktif',
'profile_login_ended'           => 'Tamat',
'profile_login_current'         => 'Semasa',
'profile_login_kill_session'    => 'Tamatkan sesi',

'profile_login_aktiviti_empty'  => 'Tiada rekod login aktiviti ditemui.',

// SweetAlert – tamatkan sesi
'profile_login_kill_confirm_title'
                                => 'Tamatkan Sesi?',
'profile_login_kill_confirm_text'
                                => 'Anda pasti mahu tamatkan sesi ini? Pengguna akan dipaksa log keluar.',
'profile_login_kill_confirm_yes'
                                => 'Ya, Tamatkan',
'profile_login_kill_confirm_no' => 'Batal',

'profile_login_kill_force_title'
                                => 'Sesi anda akan ditamatkan',
'profile_login_kill_force_text'
                                => 'Anda akan dilog keluar dalam',

'profile_login_kill_success'
                                => 'Sesi berjaya ditamatkan',
'profile_login_kill_success_text'
                                => 'Sesi telah ditamatkan',

'profile_login_kill_error'
                                => 'Gagal tamatkan sesi',
'profile_login_kill_error_network'
                                => 'Ralat rangkaian. Sila cuba lagi.',
'profile_login_kill_error_no_session'
                                => 'ID sesi tidak sah',

// =====================================================
// Keluarga
// =====================================================
'family_title'                 => 'Maklumat Keluarga',                                

/* =====================================================
 * SETTINGS (config result titles)
 * These keys used by TetapanSistemController for save results
 * ===================================================== */
'emel_title'        => 'Tetapan Emel',
'emel_title_save'   => 'Tetapan emel berjaya disimpan',
'bahasa_title'      => 'Tetapan Bahasa',
'bahasa_title_save' => 'Tetapan bahasa berjaya disimpan',
'tema_title'        => 'Tetapan Tema',
'tema_title_save'   => 'Tetapan tema berjaya dikemaskini',
'config_js_btn_tutup' => 'Tutup',

// =========================
// Audit Trail
// =========================
'profile_jejak_audit_empty'     => 'Tiada rekod jejak audit ditemui.',

'profile_audit_date'            => 'Tarikh & Masa',
'profile_audit_ip'              => 'Alamat IP',
'profile_audit_event_type'      => 'Jenis Aktiviti',
'profile_audit_outcome'         => 'Hasil',
'profile_audit_severity'        => 'Keparahan',
'profile_audit_actions'         => 'Tindakan',

'profile_audit_target_type'     => 'Jenis Sasaran',
'profile_audit_target_label'    => 'Label Sasaran',

'profile_audit_view_meta'       => 'Lihat metadata',
'profile_audit_metadata'        => 'Metadata',
'profile_audit_metadata_content'=> 'Kandungan Metadata',

'profile_audit_changes'         => 'Perubahan',
'profile_audit_change_set'      => 'Set Perubahan',
'profile_audit_change_set_meta' => 'Metadata Set Perubahan',

'profile_audit_field'           => 'Medan',
'profile_audit_old_value'       => 'Nilai Lama',
'profile_audit_new_value'       => 'Nilai Baru',
'profile_audit_data_type'       => 'Jenis',
'profile_audit_sensitive'       => 'Sensitif',

'profile_audit_no_field_changes'
                                => 'Tiada perubahan medan direkodkan.',

'profile_audit_toggle_meta'     => 'Togol metadata',
'profile_audit_copy_meta'       => 'Salin metadata',
'profile_audit_copy_meta_btn'   => 'Salin',

'profile_audit_date_unknown'    => 'Tarikh tidak diketahui',
'profile_audit_modal_close'     => 'Tutup',

// =========================
// DataTables (Profile)
 // =========================
'profile_dt_show'               => 'Papar',
'profile_dt_records'            => 'rekod',
'profile_dt_search'             => 'Cari',
'profile_dt_no_records'         => 'Tiada rekod ditemui',
'profile_dt_info'
                                => 'Paparan _START_ hingga _END_ daripada _TOTAL_ rekod',
'profile_dt_info_empty'
                                => 'Paparan 0 hingga 0 daripada 0 rekod',
'profile_dt_filtered'
                                => 'ditapis daripada _MAX_ jumlah rekod',
'profile_dt_previous'           => 'Sebelum',
'profile_dt_next'               => 'Seterusnya',
'profile_dt_error'              => 'Ralat memuat data',
'profile_dt_error_msg'          => 'Gagal dapatkan data.',

// =========================
// Lain-lain
// =========================
'profile_loading'               => 'Memuatkan…',
'profile_js_copied'             => 'Disalin',
'profile_empty_notice'
                                => 'Profil tidak dijumpai. Sesi login mungkin tamat atau rekod tiada.',

/* =====================================================
 * SENARAI PENGGUNA (userList_)
 * ===================================================== */

// =========================
// Tajuk & Breadcrumb
// =========================
'pengguna_sistem'                => 'Pengguna Sistem',
'senarai_pengguna_sistem'        => 'Senarai Pengguna Sistem',

'userList_page_heading_main'     => 'Pengguna Sistem',
'userList_page_heading_sub'      => 'Senarai Pengguna Sistem',

// =========================
// Kolum Jadual
// =========================
'userList_col_no'                => 'No.',
'userList_col_name_staffid'      => 'Nama (ID Staf)',
'userList_col_department'        => 'Nama Jabatan',
'userList_col_position'          => 'Nama Jawatan',
'userList_col_group'             => 'Nama Kumpulan',
'userList_col_access'            => 'Akses',
'userList_col_actions'           => 'Tindakan',

// =========================
// Status & Paparan
// =========================
'userList_no_records'            => 'Tiada rekod',
'userList_loading_staff'         => 'Memuatkan senarai staf',
'userList_no_staff_data'         => 'Tiada data staf',
'userList_processing'            => 'Memproses...',
'userList_loading'               => 'Memuat...',
'userList_dt_length_menu'        => 'Papar _MENU_ rekod',
'userList_access_granted'        => 'Dibenarkan',
'userList_access_blocked'        => 'Disekat',
'userList_dt_search_label'       => 'Carian:',
'userList_dt_info'               => 'Menunjukkan _START_ hingga _END_ daripada _TOTAL_ rekod',
'userList_dt_info_empty'         => 'Tiada rekod',
'userList_dt_paginate_prev'      => 'Sebelum',
'userList_dt_paginate_next'      => 'Seterusnya',
'userList_dt_zero_records'       => 'Tiada rekod dijumpai',
'userList_btn_ok'                => 'OK',

// =========================
// Tindakan
// =========================
'userList_action_change_group'   => 'Tukar kumpulan',
'userList_action_delete_user'    => 'Padam pengguna',

// =========================
// Modal — Umum
// =========================
'userList_modal_title'           => 'Tukar Kumpulan Pengguna',
'userList_modal_add_title'       => 'Tambah Pengguna',

'userList_modal_btn_save'        => 'Simpan',
'userList_modal_btn_close'       => 'Tutup',
'userList_modal_btn_cancel'      => 'Batal',

// =========================
// Modal — Label Maklumat
// =========================
'userList_modal_label_name'      => 'Nama',
'userList_modal_label_department'=> 'Nama Jabatan',
'userList_modal_label_position' => 'Jawatan',
'userList_modal_label_group'    => 'Kumpulan',
'userList_modal_label_access'   => 'Akses',
'userList_modal_label_staff'    => 'Staf',
'userList_modal_label_extra_roles' => 'Peranan',
'userList_primary_role_label'   => 'Peranan Utama',

// =========================
// Modal — Seksyen
// =========================
'userList_modal_section_user_info'
                                => 'Maklumat Pengguna',
'userList_modal_section_staff_info'
                                => 'Maklumat Staf',
'userList_modal_section_settings'
                                => 'Tetapan Pengguna',

// =========================
// Placeholder
// =========================
'userList_modal_placeholder_select_staff'
                                => 'Pilih staf...',
'userList_modal_placeholder_select_group'
                                => 'Pilih kumpulan...',
'userList_group_filter_placeholder'
                                => '-- Pilih kumpulan --',
'userList_modal_add_role'       => '+ Peranan',
'userList_modal_extra_role_title' => 'Peranan Tambahan',
'userList_role_none'            => 'Tiada peranan tambahan.',

// =========================
// Validasi / Keadaan
// =========================
'userList_staff_already_exists'  => 'Sudah Wujud',
'userList_user_default'          => 'Pengguna',

// =========================
// Pengesahan Padam
// =========================
'userList_delete_confirm_title'  => 'Padam Pengguna?',
'userList_delete_confirm_message'
                                => 'Adakah anda pasti mahu memadam pengguna ini?',
'userList_delete_confirm_warning'
                                => 'Tindakan ini tidak boleh dipulihkan.',
'userList_delete_confirm_yes'    => 'Ya, Padam',

// =========================
// Kejayaan
// =========================
'userList_success_add'           => 'Pengguna berjaya ditambah.',
'userList_success_delete'        => 'Pengguna berjaya dipadam.',
'userList_success_update_roles'  => 'Peranan tambahan berjaya dikemas kini.',
'userList_success_title'         => 'Berjaya',
'userList_success_update_group'  => 'Kumpulan dan akses pengguna berjaya dikemas kini.',

// =========================
// Ralat
// =========================
'userList_error_title'           => 'Ralat',
'userList_btn_saving'            => 'Menyimpan...',

// Buttons
'userList_sync_button'           => 'Sync Data',
'userList_sync_processing'       => 'Memproses…',
'userList_add_button'            => 'Tambah Pengguna',

'userList_err_add_failed'        => 'Gagal menambah pengguna.',
'userList_err_delete_failed'     => 'Gagal memadam pengguna.',
'userList_err_load_data'         => 'Gagal memuatkan data.',
'userList_err_load_staff'        => 'Ralat memuatkan staf',
'userList_err_param'             => 'Parameter tidak lengkap.',
'userList_err_update_group'      => 'Gagal kemas kini kumpulan.',

'userList_err_invalid_response'
                                => 'Ralat: Respons pelayan tidak sah',
'userList_err_invalid_json'
                                => 'Ralat: Respons pelayan tidak sah (bukan JSON).',
'userList_err_non_json'
                                => 'Ralat: Respons pelayan bukan JSON.',
'userList_err_server'            => 'Ralat pelayan',
'userList_err_unknown'           => 'Ralat tidak diketahui.',
'userList_err_group_load'        => 'Gagal muat kumpulan.',
'userList_err_no_permission'     => 'Anda tidak mempunyai kebenaran untuk melakukan tindakan ini.',
'userList_access_denied_title'   => 'Akses Ditolak',
'userList_access_denied_text'    => 'Anda tidak mempunyai kebenaran untuk melakukan operasi ini. Hanya {group} dibenarkan.',
'userList_rate_limit_title'      => 'Terlalu Cepat',
'userList_rate_limit_text'       => 'Sila tunggu sebentar sebelum cuba lagi.',

// =========================
// Sync
// =========================
'userList_sync_success_title'    => 'Sync Berjaya',
'userList_sync_success_message'  => 'Data berjaya disegerakkan.',
'userList_sync_summary_title'    => 'Ringkasan Sync',
'userList_sync_updated'          => 'Dikemas kini',
'userList_sync_skipped'          => 'Dilangkau',
'userList_sync_errors'           => 'Ralat',
'userList_sync_total'            => 'Jumlah',
'userList_sync_error_title'      => 'Sync Gagal',
'userList_sync_error'            => 'Ralat semasa sync data.',


/* =====================================================
 * KUMPULAN PENGGUNA (userGroup_)
 * ===================================================== */

// =========================
// Tajuk & Breadcrumb
// =========================
'userGroup_page_title'              => 'Kumpulan Pengguna',
'userGroup_intro'                   => 'Senarai kumpulan pengguna.',

// =========================
// Jadual Utama
// =========================
'userGroup_col_code'                => 'Kod Kumpulan',
'userGroup_col_name'                => 'Nama Kumpulan',
'userGroup_col_module_access'       => 'Akses Modul',
'userGroup_col_menu_access'         => 'Akses Menu',
'userGroup_col_group_access'        => 'Akses Kumpulan',
'userGroup_col_menu'                => 'Menu',
'userGroup_col_reorder' => 'Susun semula',
'userGroup_col_status'              => 'Status',
'userGroup_col_actions'             => 'Tindakan',

'userGroup_no_records'              => 'Tiada rekod',
'userGroup_search_label' => 'Cari',
'userGroup_loading' => 'Memuatkan data',

// =========================
// Butang & Aksi
// =========================
'userGroup_btn_add_menu'            => 'Tambah Menu',
'modul_tambah'                      => 'Tambah Modul',
'modul_tambah_title'                => 'Tambah Modul',
'modul_nama_ms'                     => 'Nama Modul (BM)',
'modul_nama_en'                     => 'Nama Modul (EN)',
'modul_icon'                        => 'Icon',
'modul_susunan'                     => 'Susunan',
'modul_simpan'                      => 'Simpan',
'modul_batal'                       => 'Batal',
'modul_berjaya_title'               => 'Berjaya',
'modul_berjaya_msg'                 => 'Modul berjaya ditambah.',
'modul_ralat_title'                 => 'Ralat',
'modul_ralat_duplikat'              => 'Nama modul telah wujud. Sila gunakan nama lain.',
'modul_ralat_wajib'                 => 'Nama Modul (BM) wajib diisi.',
'userGroup_btn_add_modul'            => 'Tambah Modul',
'userGroup_btn_close'               => 'Tutup',
'userGroup_btn_save'                => 'Simpan',

// =========================
// Label Kecil
// =========================
'userGroup_label_module'            => 'modul',
'userGroup_label_menu'              => 'menu',
'userGroup_label_modul_fallback'    => 'Modul',

// =========================
// Susun Menu
// =========================
'userGroup_move_up' => 'Gerakkan ke atas',
'userGroup_move_down' => 'Gerakkan ke bawah',

// =========================
// Status
// =========================
'userGroup_status_on' => 'ON',
'userGroup_status_off' => 'OFF',

// =========================
// Modal — Tambah / Sunting Menu
// =========================
'userGroup_modal_add_menu_title'    => 'Tambah Menu',
'userGroup_modal_edit_menu_title' => 'Sunting Menu',

// =========================
// Modal — Akses
// =========================
'userGroup_modal_group_access_title'=> 'Akses Kumpulan',
'userGroup_modal_summary_title'     => 'Ringkasan Akses',
'userGroup_modal_pick_menu_title'   => 'Pilih Menu',
'userGroup_modal_group_create_title'=> 'Tambah Kumpulan',
'userGroup_modal_group_edit_title'  => 'Edit Kumpulan',

// =========================
// Medan Borang
// =========================
'userGroup_field_group'             => 'Kumpulan',
'userGroup_field_group_code'        => 'Kod Kumpulan',
'userGroup_field_group_name'        => 'Nama Kumpulan',
'userGroup_field_modul'             => 'Modul',
'userGroup_field_color'             => 'Warna',
'userGroup_field_color_help'        => 'Pilih warna secara visual.',
'userGroup_field_pick_module'       => 'Pilih Modul',
'userGroup_field_pick_module_help'  => 'Pilih satu atau lebih modul untuk kumpulan ini.',
'userGroup_field_pick_menu'         => 'Pilih Menu (bergantung pada Modul)',
'userGroup_field_pick_menu_help'    => 'Menu akan dipaparkan mengikut modul yang dipilih.',
'userGroup_field_path'              => 'Path',
'userGroup_field_path_placeholder'  => 'contoh: laporan.php',

'userGroup_field_name_ms'           => 'Nama (MS)',
'userGroup_field_name_en'           => 'Nama (EN)',
'userGroup_field_name_zh'           => 'Nama (ZH)',
'userGroup_field_name_ta'           => 'Nama (TA)',

'userGroup_field_status'            => 'Status',
'userGroup_field_position_label' => 'Letak di modul sasaran',
'userGroup_position_top' => 'Di atas sekali',
'userGroup_position_bottom' => 'Di bawah sekali',

'userGroup_select_modul_placeholder'=> '— Pilih modul —',
'userGroup_loading_modules'         => 'Memuatkan modul…',

// =========================
// Ralat & Validasi
// =========================
'userGroup_error_unknown'           => 'Ralat tidak diketahui.',
'userGroup_error_network'           => 'Ralat rangkaian.',
'userGroup_error_reorder' => 'Gagal tukar susunan.',
'userGroup_error_load_access' => 'Gagal memuat akses.',
'userGroup_error_load_menu' => 'Gagal memuat menu.',
'userGroup_error_get_menu' => 'Gagal dapatkan butiran menu.',
'userGroup_error_update_status' => 'Gagal kemas kini status.',

'userGroup_err_path_required'       => 'Path tidak boleh kosong.',
'userGroup_err_group_code_name_required' => 'Sila isi Kod & Nama Kumpulan.',
'userGroup_err_group_modul_required'=> 'Sila pilih Kumpulan dan Modul.',
'userGroup_err_modul_required'      => 'Sila pilih Modul.',
'userGroup_err_add_menu' => 'Gagal tambah menu.',
'userGroup_err_save_menu' => 'Gagal simpan menu.',

'userGroup_bootstrap_missing'
                                      => 'Bootstrap JS tidak dimuat. Pastikan bootstrap.bundle.min.js dimasukkan.',

// =========================
// SweetAlert — Padam
// =========================
'userGroup_confirm_delete_title'    => 'Padam menu?',
'userGroup_confirm_delete_text'     => 'Tindakan ini tidak boleh diundur.',
'userGroup_confirm_title'           => 'Pengesahan',
'userGroup_confirm_delete_group_text' => 'Padam kumpulan "{name}"?',
'userGroup_confirm_yes_delete'      => 'Ya, Padam',
'userGroup_confirm_yes'             => 'Ya, padam',
'userGroup_confirm_cancel'          => 'Batal',

'userGroup_done'                    => 'Selesai',
'userGroup_error'                   => 'Ralat',
'userGroup_delete_success'          => 'Menu telah dipadam.',
'userGroup_delete_fail'             => 'Gagal memadam menu.',

// =========================
// Undo (Opsyenal)
 // =========================
'userGroup_undo_btn'                => 'Batal',
'userGroup_undo_title'              => 'Batal',
'userGroup_undo_message'            => 'Menu "%s" telah dipadam.',
'userGroup_undo_info'
                                      => 'Fungsi Undo memerlukan endpoint sisi server. Sila hubungi pentadbir.',

// =========================
// Carian & DataTables
// =========================
'userGroup_search_group_placeholder'=> 'Cari kumpulan...',
'userGroup_dt_length_menu' => 'Papar _MENU_ rekod',
'userGroup_dt_info'
                                      => 'Memaparkan _START_ hingga _END_ daripada _TOTAL_ entri',
'userGroup_dt_info_empty' => 'Tiada rekod',
'userGroup_dt_info_filtered'
                                      => '(ditapis daripada _MAX_ jumlah entri)',
'userGroup_dt_paginate_first'       => 'Pertama',
'userGroup_dt_paginate_last' => 'Akhir',
'userGroup_dt_paginate_next'        => 'Seterusnya',
'userGroup_dt_paginate_previous'    => 'Sebelumnya',
'userGroup_edit_group'              => 'Edit Kumpulan',
'userGroup_delete_group'            => 'Padam Kumpulan',
'userGroup_info_title'              => 'Makluman',
'userGroup_info_select_group_first' => 'Sila pilih kumpulan dahulu melalui butang Akses Menu.',
'userGroup_btn_menu_label'          => 'Menu',
'userGroup_btn_module_label'        => 'Modul',
'userGroup_btn_group_label'         => 'Kumpulan',


/* =====================================================
 * MATRIKS AKSES (access_)
 * ===================================================== */

// =========================
// Tajuk & Pengenalan
// =========================
'access_title'        => 'Matriks Akses',
'access_intro'        => 'Matriks akses baca sahaja untuk menu sistem.',

// =========================
// Jadual
// =========================
'access_col_no'       => '#',
'access_menu'         => 'Menu',
'access_path'         => 'Laluan',
'access_modul'        => 'Modul',
'access_user_level'   => 'Tahap Pengguna',

// =========================
// Tahap Pengguna
// =========================
'access_super_admin'  => 'Super Pentadbir',
'access_urusetia'     => 'Urusetia',
'access_kerani'       => 'Kerani',

// =========================
// Status Akses
// =========================
'access_ada'          => 'Ada Akses',
'access_tiada'        => 'Tiada Akses',

// =========================
// Paparan
// =========================
'access_no'           => 'Tiada rekod',


/* =====================================================
 * TETAPAN SISTEM (config_, config_js_, config_db_)
 * ===================================================== */

/* =========================
 * Tajuk
 * ========================= */
'config_system' => 'Konfigurasi Sistem',

/* =========================
 * Tab Navigasi
 * ========================= */
'config_tab_emel'   => 'Emel',
'config_tab_db'     => 'Pangkalan Data',
'config_tab_tema'   => 'Tema',
'config_tab_bahasa' => 'Bahasa',

/* =========================
 * TAB EMEL
 * ========================= */
'config_tab_emel_header_setting'        => 'Konfigurasi Pelayan Emel',
'config_tab_emel_driver'                => 'Pemacu Emel',
'config_tab_emel_host'                  => 'Hos Emel',
'config_tab_emel_port'                  => 'Port',
'config_tab_emel_encryption'            => 'Penyulitan',
'config_tab_emel_sel_tiada'             => 'Tiada',

'config_tab_emel_header_emel'            => 'Butiran Akaun Emel',
'config_tab_emel_account_emel'           => 'Akaun Emel (Username)',
'config_tab_emel_katalaluan_emel'        => 'Kata Laluan Emel',
'config_tab_emel_password_hint'          => 'Biarkan kosong untuk mengekalkan kata laluan semasa',
'config_tab_emel_from'                   => 'Emel daripada?',
'config_tab_emel_from_name'              => 'Nama Pemilik Emel',

'config_tab_emel_uji_emel'               => 'Uji Sambungan Emel',
'config_tab_emel_simpan_tetapan_emel'    => 'Simpan Tetapan Emel',

/* =========================
 * TAB DATABASE
 * ========================= */
'config_tab_db_header'                   => 'Sybase EHRM (Pilih Satu Sahaja)',
'config_tab_db_header_2'                 => 'Sybase ASIS (Pilih Satu Sahaja)',   
'config_tab_db_sybase_header'            => 'Hanya satu sambungan Sybase EHRM dibenarkan aktif dalam satu masa.',
'config_tab_db_sybase_header_asis'       => 'Hanya satu sambungan Sybase ASIS dibenarkan aktif dalam satu masa.',
'config_tab_db_sybase_sambungan'         => 'Nama Sambungan',
'config_tab_db_sybase_keterangan'        => 'Keterangan',

'config_tab_db_sybase_nama_production'   => 'e-HRMDB (Production)',
'config_tab_db_sybase_nama_production_penerangan'
                                        => 'Pangkalan data utama sistem EHRM',

'config_tab_db_sybase_nama_production_asis'   => 'SAP (Production)',
'config_tab_db_sybase_nama_production_penerangan_asis'
                                        => 'Pangkalan data utama sistem SAP',                                        

'config_tab_db_sybase_nama_development'  => 'e-HRMDB (Development)',
'config_tab_db_sybase_nama_development_penerangan'
                                        => 'Pangkalan data pembangunan',

'config_tab_db_sybase_nama_development_asis'  => 'SAP (Development)',
'config_tab_db_sybase_nama_development_penerangan_asis'
                                        => 'Pangkalan data pembangunan SAP',
                                        
'config_tab_db_sybase_smp'               => 'STAFDB',
'config_tab_db_sybase_smp_penerangan'    => 'Pangkalan data SMP (tidak digunakan lagi)',

'config_tab_db_mysql'                    => 'MySQL (Sentiasa Aktif)',
'config_tab_db_mysql_header'             => 'Sambungan ini sentiasa aktif untuk sistem utama.',
'config_tab_db_mysql_sambungan'          => 'Medan',
'config_tab_db_mysql_keterangan'         => 'Maklumat',
'config_tab_db_mysql_host'               => 'Hos',
'config_tab_db_mysql_user'               => 'Pengguna',
'config_tab_db_mysql_status'             => 'Status',

'config_tab_db_simpan_tetapan_db'        => 'Simpan Tetapan Pangkalan Data',

/* =========================
 * TAB TEMA
 * ========================= */
'config_tab_tema_header'                 => 'Tetapan Tema Lalai',
'config_tab_tema_header_details'         => 'Tema ini akan digunakan untuk pengguna yang belum pernah menetapkan tema tersendiri.',
'config_tab_tema_komponen'               => 'Komponen',
'config_tab_tema_pilihan'                => 'Pilihan Tema',
'config_tab_tema_penerangan'             => 'Penerangan',

// Layout
'config_tab_tema_komponen_layout'        => 'Mod Susun Atur (Layout)',
'config_tab_tema_pilihan_layout_terang'  => 'Warna Terang',
'config_tab_tema_pilihan_layout_gelap'   => 'Warna Gelap',
'config_tab_tema_penerangan_layout_terang_penerangan'
                                        => 'Rekaan cerah sepenuhnya — standard mod terang.',
'config_tab_tema_penerangan_layout_gelap_penerangan'
                                        => 'Susun atur gelap — sesuai untuk malam.',

// Topbar
'config_tab_tema_komponen_topbar'        => 'Warna Topbar',
'config_tab_tema_pilihan_topbar_terang'  => 'Warna Terang',
'config_tab_tema_pilihan_topbar_gelap'   => 'Warna Gelap',
'config_tab_tema_pilihan_layout_brand'   => 'Warna Brand',
'config_tab_tema_penerangan_topbar_terang_penerangan'
                                        => 'Topbar cerah, sesuai untuk mod terang.',
'config_tab_tema_penerangan_topbar_gelap_penerangan'
                                        => 'Topbar gelap, sesuai untuk waktu malam atau mod gelap.',
'config_tab_tema_penerangan_topbar_brand_penerangan'
                                        => 'Topbar ikut warna tema rasmi sistem.',

// Sidebar
'config_tab_tema_komponen_sidebar'       => 'Warna Sidebar',
'config_tab_tema_pilihan_sidebar_terang' => 'Warna Terang',
'config_tab_tema_pilihan_sidebar_gelap'  => 'Warna Gelap',
'config_tab_tema_pilihan_sidebar_brand'  => 'Warna Brand',
'config_tab_tema_penerangan_sidebar_terang_penerangan'
                                        => 'Sidebar cerah dengan latar putih bersih.',
'config_tab_tema_penerangan_sidebar_gelap_penerangan'
                                        => 'Sidebar gelap, selesa untuk mata dalam mod malam.',
'config_tab_tema_penerangan_sidebar_brand_penerangan'
                                        => 'Sidebar guna warna jenama utama sistem.',

'config_tab_db_simpan_tetapan_tema'      => 'Simpan Tetapan Tema',

/* =========================
 * TAB BAHASA
 * ========================= */
'config_tab_bahasa_header'               => 'Bahasa yang Tersedia',
'config_tab_bahasa_header_details'       => 'Tandakan bahasa yang ingin diaktifkan untuk digunakan dalam sistem.',
'config_tab_bahasa_kodBahasa'            => 'Kod Bahasa',
'config_tab_bahasa_peneranganBahasa'     => 'Penerangan Bahasa',
'config_tab_bahasa_simpan_tetapan_bahasa'=> 'Simpan Tetapan Bahasa',

/* =========================
 * JS / SWEETALERT
 * ========================= */
'config_js_loading'              => 'Memuat…',
'config_js_memproses'            => 'Memproses…',

'config_js_confirm_emel'         => 'Anda pasti mahu simpan tetapan emel?',
'config_js_confirm_db'           => 'Anda pasti mahu simpan tetapan pangkalan data?',
'config_js_confirm_tema'         => 'Anda pasti mahu simpan tetapan tema lalai?',
'config_js_confirm_bahasa'       => 'Anda pasti mahu simpan senarai bahasa aktif?',

'config_js_btn_ya_simpan'        => 'Ya, simpan',
'config_js_btn_ya_teruskan'      => 'Ya, teruskan',
'config_js_btn_ok'               => 'OK',
'config_js_btn_cancel'           => 'Batal',

// Uji Emel
'config_js_confirm_uji_emel'     => 'Anda pasti mahu uji sambungan emel ini?',
'config_js_input_uji_emel'       => 'Masukkan Emel Ujian',
'config_js_label_uji_emel'       => 'Alamat emel untuk uji penghantaran',
'config_js_placeholder_uji_emel' => 'cth: apps_email@upnm.edu.my',
'config_js_valid_emel_kosong'    => 'Alamat emel tidak boleh kosong',
'config_js_uji_emel_btn'         => 'Uji Sekarang',
'config_js_uji_emel_btn_loading' => 'Menguji…',
'config_js_uji_emel_btn_default' => 'Uji Sambungan Emel',

// Status JS
'config_js_berjaya'              => 'Berjaya',
'config_js_ralat'                => 'Ralat',
'config_js_emel_berjaya'         => '✅ Emel berjaya dihantar.',
'config_js_emel_uji_berjaya'     => 'Emel ujian berjaya dihantar ke :email.',
'config_js_emel_gagal'           => '❌ Gagal hantar emel.',
'config_js_emel_uji_gagal'       => '❌ Gagal hantar emel: :error',
'config_js_ralat_sistem'         => '❌ Ralat sistem semasa menguji sambungan.',
'config_js_tiada_bahasa'         => 'Tiada Bahasa Dipilih',
'config_js_pilih_bahasa'         => 'Sila pilih sekurang-kurangnya satu bahasa.',

/* =========================
 * ALERT DB (Controller)
 * ========================= */
'config_db_sambungan_tidak_sah'   => 'Sambungan Tidak Sah',
'config_db_pilihan_tidak_wujud'   => 'Pilihan sambungan tidak wujud.',
'config_db_sambungan_gagal'       => 'Sambungan Gagal',
'config_db_sambungan_gagal_msg'   => 'Tidak dapat menyambung ke pangkalan data ":db".',
'config_db_sambungan_ok'          => 'Sambungan Berjaya',
'config_db_sambungan_ok_msg'      => 'Sambungan berjaya dikemaskini ":db".',
'config_db_ralat_simpan'          => 'Ralat Simpanan',
'config_db_ralat_simpan_msg'      => 'Gagal menyimpan tetapan ke dalam fail.',

'config_alert_title'              => 'Anda pasti?',
'config_alert_no'                 => 'Batal',
'config_alert_bahasa_berjaya'     => 'Berjaya Simpan Tetapan Bahasa',


/* =====================================================
 * UI GLOBAL (theme_, topbar_, sidebar_, footer_, logout_)
 * ===================================================== */

/* =========================
 * TEMA (Offcanvas / Global)
 * ========================= */
'theme_title'                 => 'Tetapan Tema',
'theme_close'                 => 'Tutup',
'theme_customize'             => 'Sesuaikan',
'theme_customize_sub'         => 'Tetapan warna, menu, dan lain-lain',

'theme_color_scheme'          => 'Skema Warna',
'theme_light'                 => 'Cerah',
'theme_dark'                  => 'Gelap',

'theme_topbar_color'          => 'Warna Topbar',
'theme_menu_color'            => 'Warna Sidebar/Menu',
'theme_brand'                 => 'Jenama',

'theme_note_preview'          => 'Perubahan di sini adalah pratayang. Untuk simpan kekal, guna halaman Tetapan Sistem.',
'theme_note_preview_fallback' => 'Perubahan di sini adalah pratayang. Untuk simpan kekal, guna halaman Tetapan Sistem.',
'theme_applied'               => 'Tema Diterapkan',
'theme_'                      => 'Tema',

/* =========================
 * TOPBAR
 * ========================= */
'topbar_welcome'              => 'Selamat Datang!',
'topbar_keluar'               => 'Log Keluar',

// Profil & menu
'topbar_profile'              => 'Profil Saya',
'topbar_settings'             => 'Tetapan',
'topbar_support'              => 'Sokongan',
'topbar_lock_screen'          => 'Kunci Skrin',

// Notifikasi
'topbar_notification_title'   => 'Notifikasi',
'topbar_clear_all'            => 'Kosongkan Semua',
'topbar_today'                => 'Hari Ini',
'topbar_view_all'             => 'Lihat Semua',

// Contoh notifikasi
'topbar_sample_noti_title'    => 'Contoh Notifikasi',
'topbar_sample_noti_desc'     => 'Notifikasi sistem e-Prestasi',
'topbar_time_1min_ago'        => '1 minit lalu',
'topbar_switch_role'          => 'Tukar Peranan',
'topbar_switch_role_title'    => 'Tukar Peranan',
'topbar_switch_role_select'   => 'Pilih Peranan',
'topbar_switch_role_primary_label' => 'Peranan utama',
'topbar_switch_role_primary_tag'   => 'Peranan Utama',
'topbar_switch_role_none'     => 'Tiada peranan lain yang dibenarkan.',
'topbar_switch_role_err_select' => 'Sila pilih peranan.',
'topbar_switch_role_err_invalid' => 'Sila pilih peranan yang sah.',
'topbar_switch_role_saving'   => 'Menyimpan...',
'topbar_switch_role_success_title' => 'Peranan {role}',
'topbar_switch_role_success_text'  => 'Paparan dan akses sistem telah dikemas kini mengikut pilihan peranan baru iaitu <strong>{role}</strong>.',

/* =========================
 * SIDEBAR
 * ========================= */
'sidebar_main'                => 'Utama',
'sidebar_dashboard'           => 'Papan Pemuka',
'sidebar_dashboard_stats'     => 'Statistik',
'sidebar_modul'               => 'Modul Sistem',
'sidebar_kawalan'             => 'Kawalan Sistem',
'sidebar_keluar'              => 'Log Keluar',

'sidebar_profile_empty'       => 'Profil tidak ditemui',
'sidebar_loading'             => 'Memuatkan...',

/* =========================
 * FOOTER
 * ========================= */
'footer_it'                   => 'BTMK | Seksyen Aplikasi Digital',
'footer_about'                => 'Tentang Kami',
'footer_help'                 => 'Bantuan',
'footer_contact'              => 'Hubungi Kami',

'footer_content_updating_title'
                              => 'Maklumat',
'footer_content_updating'     => 'Kandungan sedang dikemaskini.',
'footer_content_updating_ok'  => 'OK',

/* =========================
 * LOGOUT (SweetAlert)
 * ========================= */
'logout_alert_title'          => 'Pengesahan',
'logout_alert_text'           => 'Anda pasti mahu log keluar?',
'logout_alert_yes'            => 'Ya, log keluar',
'logout_alert_no'             => 'Batal',

'logout_title'                => 'Log Keluar Berjaya',
'logout_msg'                  => 'Anda telah log keluar daripada sistem.',


/* =====================================================
 * KUMPULAN PENGGUNA (userGroup_)
 * ===================================================== */

/* =========================
 * Butang & Aksi
 * ========================= */
'userGroup_edit' => 'Sunting',
'userGroup_delete'                  => 'Padam',


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
                                    => 'Fungsi batal memerlukan endpoint server-side. Sila hubungi admin.',

/* =========================
 * SweetAlert — Padam
 * ========================= */


/* =========================
 * Ralat
 * ========================= */


'userGroup_bootstrap_missing'
                                    => 'Bootstrap JS tidak dimuat. Pastikan bootstrap.bundle.min.js dimasukkan.',

/* =========================
 * DataTables
 * ========================= */
'userGroup_dt_info'
                                    => 'Menunjukkan _START_ hingga _END_ daripada _TOTAL_ rekod',
'userGroup_dt_info_filtered'
                                    => '(ditapis daripada _MAX_ jumlah rekod)',



/* =====================================================
 * KUNCI PEMANTAUAN & SISTEM
 * ===================================================== */
'monitoring_setup_title' => 'Persediaan Pemantauan',
'monitoring_master_data' => 'Data Induk',
'monitoring_teras_register' => 'Daftar Teras',
'monitoring_user_management' => 'Pengurusan Pengguna',
'monitoring_system_settings' => 'Tetapan Sistem',
'manage_users' => 'Urus Pengguna',
'total_users' => 'Jumlah Pengguna',
'role_admin' => 'Pentadbir',
'role_pic' => 'Pegawai Bertanggungjawab',
'settings_description' => 'Konfigurasi tetapan dan keutamaan sistem',
'user_management_note' => 'Tambah, sunting, atau buang pengguna sistem',

/* =====================================================
 * KUNCI UMUM / PELBAGAI
 * ===================================================== */
'actions' => 'Tindakan',
'add_teras' => 'Tambah Teras',
'btn_save' => 'Simpan',
'enter_program_name' => 'Masukkan nama program',
'formula_note' => 'Formula untuk pengiraan',
'generate_report' => 'Jana Laporan',
'good' => 'Baik',
'implementation_year' => 'Tahun Pelaksanaan',
'monthly' => 'Bulanan',
'no_data' => 'Tiada data tersedia',
'official_report_generation' => 'Penjanaan Laporan Rasmi',
'program_description' => 'Penerangan Program',
'program_name' => 'Nama Program',
'program_objective' => 'Objektif Program',
'progress_formula' => 'Formula Kemajuan',
'quarterly' => 'Suku Tahun',
'reporting_cycle' => 'Kitaran Pelaporan',
'teras_code' => 'Kod Teras',
'teras_name' => 'Nama Teras',
'type' => 'Jenis',
'ujian_db' => 'Ujian Pangkalan Data',
'update_settings' => 'Kemas Kini Tetapan',
'weekly' => 'Mingguan',

/* =====================================================
 * KUNCI PENGURUSAN PROJEK
 * ===================================================== */
'project_name' => 'Nama Projek',
'project_code' => 'Kod Projek',
'teras_project_name' => 'Nama Teras / Projek',
'update_status' => 'Kemaskini Status',
'update_project_status' => 'Kemaskini Status Projek',
'update_project' => 'Kemaskini Projek',
'create_new_project' => 'Cipta Projek Baru',
'save_report' => 'Simpan Laporan',
'save_changes' => 'Simpan Perubahan',
'save_program' => 'Simpan Program',
'teras_code_placeholder' => 'Kod Teras (cth: TS-01)',

/* =====================================================
 * KUNCI BUTANG/TINDAKAN UMUM
 * ===================================================== */
'btn_update' => 'Kemaskini',
'btn_close' => 'Tutup',
'updating' => 'Mengemas kini',


/* =====================================================
 * MESEJ MEMUATKAN/STATUS
 * ===================================================== */
'loading_user_list' => 'Memuatkan senarai pengguna...',

/* =====================================================
 * DASHBOARD (ASAS)
 * ===================================================== */
'dashboard_title' => 'Dashboard',
'dashboard_breadcrumb' => 'Dashboard',
'dashboard_welcome' => 'Selamat datang',
'dashboard_last_login' => 'Log masuk terakhir',
'dashboard_tabs_label' => 'Tab dashboard',
'dashboard_tab_overview' => 'Gambaran',
'dashboard_tab_activity' => 'Aktiviti Saya',
'dashboard_tab_tasks' => 'Tugasan Saya',
'dashboard_tab_access' => 'Akses & Peranan',
'dashboard_tab_security' => 'Keselamatan',
'dashboard_tab_health' => 'Semakan Kesihatan Sistem',
'dashboard_tab_overview_empty' => 'Kandungan gambaran akan dipaparkan di sini.',
'dashboard_tab_activity_empty' => 'Kandungan aktiviti akan dipaparkan di sini.',
'dashboard_tab_tasks_empty' => 'Kandungan tugasan akan dipaparkan di sini.',
'dashboard_tab_access_empty' => 'Kandungan akses & peranan akan dipaparkan di sini.',
'dashboard_tab_security_empty' => 'Kandungan keselamatan akan dipaparkan di sini.',
'dashboard_health_col_check' => 'Semakan',
'dashboard_health_col_status' => 'Status',
'dashboard_health_col_info' => 'Maklumat',
'dashboard_resources_title' => 'Sumber Sistem',
'dashboard_refresh' => 'Muat semula',
'dashboard_resources_col_resource' => 'Sumber',
'dashboard_resources_col_usage' => 'Penggunaan',
'dashboard_resources_col_status' => 'Status',
'dashboard_announcements_title' => 'Pengumuman',
'dashboard_announcements_sub' => 'Notis sistem',
'dashboard_notice' => 'Notis',
'dashboard_announcements_empty' => 'Tiada pengumuman.',
'dashboard_status_ok' => 'OK',
'dashboard_status_warning' => 'Amaran',
'dashboard_status_critical' => 'Kritikal',
'dashboard_status_unknown' => 'Tidak diketahui',
'dashboard_status_degraded' => 'Menurun',
'dashboard_resource_cpu' => 'CPU',
'dashboard_resource_memory' => 'Memori',
'dashboard_resource_disk' => 'Cakera',
'dashboard_health_db' => 'Pangkalan Data',
'dashboard_health_connected' => 'Bersambung',
'dashboard_health_conn_failed' => 'Sambungan gagal',
'dashboard_health_app' => 'Aplikasi',
'dashboard_health_bootstrap_ok' => 'Bootstrap dimuat',
'dashboard_health_config_incomplete' => 'Konfigurasi tidak lengkap',
'dashboard_health_storage' => 'Storan',
'dashboard_health_storage_free' => '%s%% ruang kosong',
'dashboard_health_unavailable' => 'Tidak tersedia',
'dashboard_health_cache' => 'Cache',
'dashboard_health_enabled' => 'Diaktifkan',
'dashboard_health_readonly' => 'Baca sahaja',
'dashboard_health_disabled' => 'Dinonaktifkan',
'dashboard_env_production' => 'produksi',
'dashboard_env_development' => 'pembangunan',
'dashboard_env_debug_on' => 'debug ON',
'dashboard_env_debug_off' => 'debug OFF',
'dashboard_health_audit' => 'Audit/Log',
'dashboard_health_writable' => 'Boleh ditulis',
'dashboard_health_not_writable' => 'Tidak boleh ditulis',
'dashboard_health_cron' => 'Kerja Berjadual',
'dashboard_health_unknown' => 'Tidak diketahui',
'dashboard_health_tz' => 'Masa & Zon Masa',

/* =====================================================
 * FAQ (SOALAN LAZIM)
 * ===================================================== */
'faq_title' => 'Soalan Lazim (FAQ)',
'faq_heading' => 'Soalan Lazim Sistem',
'faq_intro' => 'Rujuk panduan umum penggunaan sistem. Pilih kategori atau gunakan carian untuk jawapan yang berkaitan.',
'faq_label_category' => 'Kategori',
'faq_placeholder_cari' => 'Cari dalam kategori terpilih…',
'faq_tiada_padamu' => 'Tiada padanan ditemui. Cuba kata kunci lain.',
'faq_count_display' => 'daripada',
'faq_count_soalan' => 'soalan dipaparkan',
'faq_cat_semua' => 'Semua',
'faq_cat_account_access' => 'Akaun & Akses',
'faq_cat_navigation' => 'Navigasi & Penggunaan',
'faq_cat_profile_settings' => 'Profil & Tetapan',
'faq_cat_user_management' => 'Pengurusan Pengguna',
'faq_cat_group_management' => 'Kumpulan Pengguna',
'faq_cat_support' => 'Sokongan',

'faq_item_01_q' => 'Bagaimana cara log masuk ke sistem?',
'faq_item_01_a' => 'Gunakan <b>ID Staf</b> dan <b>kata laluan</b> anda pada halaman log masuk. Jika ini kali pertama, sila ikut arahan yang dipaparkan pada halaman login atau hubungi pentadbir sistem untuk bantuan.',
'faq_item_02_q' => 'Kenapa saya tidak boleh log masuk?',
'faq_item_02_a' => 'Punca biasa ialah kata laluan tidak tepat, akaun disekat, atau akses kumpulan belum ditetapkan. Semak semula ID/kata laluan anda. Jika masih gagal, hubungi pentadbir sistem.',
'faq_item_03_q' => 'Bagaimana akses menu ditentukan?',
'faq_item_03_a' => 'Setiap pengguna berada dalam <b>kumpulan pengguna</b> tertentu. Kumpulan ini menentukan modul dan menu yang boleh dilihat. Jika menu tiada, minta pentadbir semak tetapan kumpulan anda.',
'faq_item_04_q' => 'Di mana saya boleh lihat maklumat ringkas sistem?',
'faq_item_04_a' => 'Gunakan halaman <b>Dashboard</b> untuk paparan ringkas. Ia membantu anda faham status semasa dan navigasi ke modul utama dengan lebih cepat.',
'faq_item_05_q' => 'Kenapa menu sidebar saya berbeza dengan pengguna lain?',
'faq_item_05_a' => 'Menu sidebar dipaparkan mengikut kumpulan dan peranan pengguna. Ini adalah normal untuk memastikan setiap pengguna hanya melihat fungsi yang berkaitan dengan tugas masing-masing.',
'faq_item_06_q' => 'Bagaimana cara cepat cari fungsi dalam sistem?',
'faq_item_06_a' => 'Gunakan menu modul di sidebar dan pilih halaman berkaitan. Untuk halaman jadual, gunakan carian di bahagian atas jadual untuk tapis data dengan cepat.',
'faq_item_07_q' => 'Bagaimana saya kemaskini tetapan bahasa?',
'faq_item_07_a' => 'Anda boleh menukar bahasa melalui topbar atau halaman <b>Profil</b>. Pilihan bahasa akan disimpan untuk akaun anda.',
'faq_item_08_q' => 'Bagaimana saya ubah tema paparan?',
'faq_item_08_a' => 'Pergi ke halaman <b>Profil</b> untuk menukar tetapan tema seperti mode paparan dan warna antaramuka. Perubahan akan digunakan pada sesi anda.',
'faq_item_09_q' => 'Apa kandungan Jejak Audit di halaman Profil?',
'faq_item_09_a' => 'Jejak Audit memaparkan rekod aktiviti penting seperti kemaskini data dan tindakan sistem. Ia membantu semakan dan pemantauan keselamatan.',
'faq_item_10_q' => 'Bagaimana pentadbir tambah atau kemaskini pengguna?',
'faq_item_10_a' => 'Pentadbir boleh menggunakan halaman <b>Senarai Pengguna</b> untuk menambah pengguna, menukar kumpulan, dan mengawal status akses. Setiap perubahan perlu ikut polisi dalaman organisasi.',
'faq_item_11_q' => 'Apakah maksud status akses pengguna?',
'faq_item_11_a' => 'Status akses menentukan sama ada pengguna dibenarkan masuk ke sistem. Jika status disekat, pengguna tidak boleh log masuk sehingga status diaktifkan semula oleh pentadbir.',
'faq_item_12_q' => 'Apa fungsi halaman Kumpulan Pengguna?',
'faq_item_12_a' => 'Halaman ini digunakan untuk mengurus struktur kumpulan, warna identiti kumpulan, serta akses modul/menu bagi setiap kumpulan. Ini memudahkan pengurusan hak capaian secara berpusat.',
'faq_item_13_q' => 'Bolehkah kumpulan dipadam?',
'faq_item_13_a' => 'Kumpulan hanya boleh dipadam jika tiada akses modul/menu yang aktif dan tiada pengguna yang masih ditetapkan pada kumpulan tersebut. Ini untuk elak gangguan operasi.',
'faq_item_14_q' => 'Apa perlu dibuat jika berlaku ralat sistem?',
'faq_item_14_a' => 'Catat mesej ralat, masa kejadian, dan tindakan semasa ralat berlaku. Hantar maklumat tersebut kepada pentadbir sistem untuk semakan lanjut.',
'faq_item_15_q' => 'Siapa perlu dihubungi untuk isu akses atau konfigurasi?',
'faq_item_15_a' => 'Hubungi pentadbir sistem dalaman organisasi anda. Isu akses, kumpulan pengguna, dan tetapan sistem biasanya memerlukan kebenaran pentadbir.',

];
