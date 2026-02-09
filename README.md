# e-hepa - Dokumentasi Sistem

Aplikasi web dalaman berasaskan PHP untuk pengurusan projek/pelaporan prestasi, pengurusan pengguna & akses, konfigurasi sistem, serta audit aktiviti pengguna.

Nota penting: dalam kod semasa, label sistem masih bercampur antara `e-hepa`, `e-prestasi`, dan `UPNM30`.

## Ringkasan Fungsi Sistem

- Autentikasi login staf dengan perlindungan sesi, CSRF token, dan had cubaan login.
- Kawalan akses berasaskan kumpulan/peranan (`group`) termasuk pertukaran peranan aktif semasa sesi (`role switch`).
- Dashboard prestasi dengan KPI, trend, taburan band, filter tahun/jabatan, dan cache data sesi.
- Pengurusan pengguna: senarai pengguna, tambah/padam pengguna, tukar kumpulan utama, status akses (`f_flag`), peranan tambahan, dan sync manual Sybase -> MySQL.
- Pengurusan kumpulan pengguna: cipta kumpulan, lihat akses modul/menu, simpan permission modul/menu, dan urus menu (create/update/delete/order/toggle).
- Access Matrix: visual peranan vs menu.
- Pengurusan projek: `Project Planning`, `Project Monitoring`, `Project Reporting`, `Project Details`, dan `My Projects`.
- Program setup: urus program, teras strategik, pemilik teras, dan tetapan monitoring.
- Profil pengguna: paparan profil, login activity (`audit_session`), audit event (`audit_event`), dan metadata perubahan.
- Tetapan sistem (admin): konfigurasi emel SMTP + ujian emel, pemilihan DB Sybase aktif (`ehrmdb`, `ehrmdb_dev`, `stafdb`), tema, dan bahasa.
- Sybase Structure Inspector: senarai owner/schema, table/view, `sp_help`, `sp_helpindex`, `sp_helptext`.
- FAQ dalaman berasaskan kategori.

## Ciri Keselamatan yang Sudah Ada

- Session hardening (`httponly`, `samesite`, regenerate session id).
- CSRF validation pada endpoint penting.
- Rate limiting pada kebanyakan endpoint AJAX kritikal.
- Guard `require_login()` pada halaman/endpoint terlindung.
- Authorization check berasaskan role/group untuk operasi admin.
- Audit logging untuk login/logout, pertukaran role, perubahan konfigurasi, pengguna, kumpulan, menu, projek, dan laporan.
- Sanitasi path/menu/icon pada komponen sidebar.

## Senibina Teknologi

- Backend: PHP 8.2 (Apache).
- Database utama: MySQL.
- Database integrasi: Sybase (ODBC/DBLIB, ikut platform).
- Frontend: Bootstrap 5, jQuery, Alpine.js, DataTables, Select2, SweetAlert2.
- Mail: PHPMailer.
- Deployment dev: Docker + Docker Compose.

## Struktur Projek (Sebenar)

```text
D:\WWW\e-hepa
|- app/
|  |- ajax/                 # Endpoint AJAX (user/group/menu/project/profile/config)
|  |- classes/              # Database, User, Config, AuditLogger, dsb.
|  |- configuration/        # db_config.php, settings.php, config active db
|  |- controllers/          # Controller untuk setiap halaman/modul
|  |- includes/             # init.php, topbar, sidebar, script, logger
|  |- lang/                 # Bahasa (`ms`, `en`)
|  |- pages/                # Semua halaman utama sistem
|  |- templates/mail/       # Template emel
|  |- assets/               # CSS/JS/vendor/images
|- docker/
|  |- apache/               # Virtual host + servername
|  |- ssl/                  # Sijil SSL dev
|  |- php.ini               # Override PHP config
|- Dockerfile
|- docker-compose.yml
|- package.json
|- README.md
```

## Halaman Utama

- `app/index.php` - login page.
- `app/pages/dashboard.php` - dashboard utama.
- `app/pages/senarai-pengguna.php` - pengurusan pengguna.
- `app/pages/kumpulan-pengguna.php` - pengurusan kumpulan, modul, menu.
- `app/pages/access.php` - matriks akses.
- `app/pages/program-setup.php` - setup program & teras.
- `app/pages/project-planning.php` - perancangan projek.
- `app/pages/project-monitoring.php` - pemantauan projek.
- `app/pages/project-reporting.php` - pelaporan kemajuan.
- `app/pages/my-projects.php` - projek milik pengguna semasa.
- `app/pages/project-details.php` - butiran matrix/cetak projek.
- `app/pages/profile.php` - profil + audit trail.
- `app/pages/tetapan-sistem.php` - tetapan sistem admin.
- `app/pages/sybase-structure.php` - inspector struktur Sybase.
- `app/pages/soalan-lazim.php` - FAQ.

## Endpoint AJAX Penting

- Pengguna: `user-add.php`, `user-delete.php`, `user-set-group.php`, `user-extra-roles.php`, `user-sync-sybase.php`.
- Kumpulan/menu: `group-create.php`, `group-perms-get.php`, `group-perms-save.php`, `menu-create.php`, `menu-save.php`, `menu-delete.php`, `menu-swap.php`, `menu-flag-toggle.php`.
- Projek: `project-planning-handler.php`, `project-reporting-handler.php`, `my-projects-handler.php`, `program-setup-handler.php`.
- Profil/audit: `profile-login-activity.php`, `profile-audit-events.php`, `profile-audit-event-meta.php`, `profile-kill-session.php`.
- Sistem: `role-switch.php`, `role-switch-roles.php`, `uji-emel.php`, `system-resources.php`, `clear-dashcache.php`.

## Setup Development (Docker)

1. Pastikan Docker Desktop aktif.
2. Di root projek, jalankan:

```bash
docker compose up -d --build
```

3. Akses aplikasi:
- `http://localhost`
- `https://localhost` (jika SSL dev dikonfigurasi)

4. Source code app dimount ke container melalui volume `./app:/var/www/html`.

## Konfigurasi Database

- Fail utama konfigurasi: `app/configuration/db_config.php`.
- Sistem guna MySQL untuk data aplikasi & konfigurasi.
- Sistem boleh tukar Sybase aktif melalui Tetapan Sistem.
- Nilai aktif diselaras melalui session, config DB (`SYBASE_ACTIVE_BASE`), dan fail `config_db_active.json`.

## Konfigurasi Peranan/Feature Flag

- `PRESTASI_ROLE_ID_ADM_SA`, `PRESTASI_ROLE_ID_ADM_HR`, `PRESTASI_ROLE_ID_ADM_KE`.
- `ENABLE_SYSTEM_RESOURCES` (default `false`, admin-only bila diaktifkan).
- Rujuk: `app/setting/constants/prestasi_constants.php`.

## Nota Operasi

- Jangan buang/ubah mekanisme audit tanpa semakan impak.
- Endpoint write penting sudah ada CSRF + rate limit; kekalkan pattern ini untuk endpoint baru.
- Jika tambah modul baru, ikut pola semasa: `page -> controller -> ajax handler -> audit`.
- Fail `app/configuration/db_config.php` semasa mengandungi kredensial hardcoded; disyorkan pindah ke environment secret management.
