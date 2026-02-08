<?php
// pages/soalan-lazim.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';
require_login();

/* ================= Session / UI ================= */
$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));
$lang         = $_SESSION['lang']          ?? 'ms';
$sidebarTheme = $_SESSION['theme.sidebar'] ?? 'dark';
$version      = (string)($_ENV['APP_ASSET_VER'] ?? date('ymdHis'));

/* GET page biasa — boleh lepaskan lock */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && session_status() === PHP_SESSION_ACTIVE) {
  session_write_close();
}

/* ================= Helpers ================= */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/* ================= Data: Kategori + FAQ =================
 *  - Setiap item wajib ada: cat (kategori), q (soalan), a (jawapan), tags (opsyen)
 *  - Jika nak tambah, ikut struktur yang sama.
 */
/*
  Each FAQ now supports an optional 'audience' key:
   - 'all' (default) : visible to everyone
   - string like 'Super Admin', 'Admin HR', 'Admin Kewangan' : visible only to that group
   - array of strings : visible to any matching group
*/
$faqs = [
  // Akaun & Akses
  ['cat'=>__('faq_cat_akaun_akses') ?: 'Akaun & Akses','q'=>__('faq_q_login') ?: 'Bagaimana cara log masuk e-Prestasi?','a'=>__('faq_a_login') ?: 'Gunakan <b>ID Staf</b> dan <b>Kata Laluan</b> anda. Untuk log masuk kali pertama, gunakan ID Staf sebagai kata laluan. Jika terlupa kata laluan atau menghadapi masalah, hubungi pentadbir sistem.','tags'=>'akaun login id staf katalaluan password','audience'=>'all'],
  ['cat'=>__('faq_cat_akaun_akses') ?: 'Akaun & Akses','q'=>__('faq_q_blocked') ?: 'Kenapa saya tidak boleh log masuk walaupun ID dan kata laluan betul?','a'=>__('faq_a_blocked') ?: 'Akaun anda mungkin telah <b>disekat</b> oleh pentadbir. Sila hubungi pentadbir sistem untuk membuka semula akses. Status akses boleh disemak melalui menu <b>Senarai Pengguna</b> (untuk pentadbir sahaja).','tags'=>'akses disekat blocked login gagal'],
  ['cat'=>__('faq_cat_akaun_akses') ?: 'Akaun & Akses','q'=>__('faq_q_access') ?: 'Siapa boleh akses modul dalam sistem?','a'=>__('faq_a_access') ?: 'Akses modul ditentukan oleh <b>Kumpulan Pengguna</b> (contoh: Super Admin, Admin HR, Admin Kewangan). Setiap kumpulan mempunyai akses modul dan menu yang berbeza. Hubungi pentadbir untuk menukar kumpulan atau akses.','tags'=>'akses peranan role kumpulan pengguna modul menu'],

  // Dashboard & Prestasi
  ['cat'=>__('faq_cat_dashboard_prestasi') ?: 'Dashboard & Prestasi','q'=>__('faq_q_dashboard') ?: 'Bagaimana nak lihat statistik prestasi?','a'=>__('faq_a_dashboard') ?: 'Pergi ke <b>Dashboard</b> untuk melihat KPI, carta trend, dan analisis prestasi. Gunakan penapis <b>Tahun</b> dan <b>Jabatan</b> untuk melihat data spesifik. Pilih "Semua" untuk melihat data keseluruhan.','tags'=>'dashboard kpi statistik prestasi carta trend','audience'=>'all'],
  ['cat'=>__('faq_cat_dashboard_prestasi') ?: 'Dashboard & Prestasi','q'=>__('faq_q_dashboard_filter') ?: 'Kenapa data dashboard tidak berubah apabila saya pilih "Semua" jabatan?','a'=>__('faq_a_dashboard_filter') ?: 'Pastikan anda memilih <b>"Semua"</b> dari dropdown jabatan (bukan kosong). Sistem akan memaparkan data untuk semua jabatan apabila "Semua" dipilih. Jika masih tidak berubah, cuba refresh halaman.','tags'=>'dashboard filter semua jabatan data'],

  // Anugerah Perkhidmatan Cemerlang (APC)
  ['cat'=>__('faq_cat_apc') ?: 'Anugerah Perkhidmatan Cemerlang (APC)','q'=>__('faq_q_apc_jawatan') ?: 'Bagaimana nak kemaskini maklumat Jawatankuasa Penilai?','a'=>__('faq_a_apc_jawatan') ?: 'Pergi ke <b>Senarai APC → Tab 1: Jawatankuasa Penilai</b>. Isi maklumat mesyuarat (tarikh, masa, tempat) dan pilih ahli jawatankuasa (Pengerusi, Setiausaha, Ahli). Klik <b>Simpan</b> selepas selesai.','tags'=>'apc jawatankuasa penilai mesyuarat pengerusi setiausaha'],
  ['cat'=>__('faq_cat_apc') ?: 'Anugerah Perkhidmatan Cemerlang (APC)','q'=>__('faq_q_apc_status') ?: 'Bagaimana nak kemaskini status penerima APC dan PGT?','a'=>__('faq_a_apc_status') ?: 'Pergi ke <b>Senarai APC → Tab 2: Borang Pencalonan APC</b>. Pilih staf dan klik butang <b>APC</b> atau <b>PGT</b> untuk menukar status. Status akan disimpan secara automatik dan dipaparkan dalam jadual.','tags'=>'apc pgt penerima status pencalonan'],
  ['cat'=>__('faq_cat_apc') ?: 'Anugerah Perkhidmatan Cemerlang (APC)','q'=>__('faq_q_apc_catatan') ?: 'Bagaimana nak tambah catatan dalam Laporan Format A?','a'=>__('faq_a_apc_catatan') ?: 'Pergi ke <b>Senarai APC → Tab 3: Laporan Format A → Subtab 1: Laporan</b>. Scroll ke bahagian <b>Catatan Keseluruhan Jawatankuasa Penilai</b>. Catatan hanya boleh dikemaskini jika dokumen belum dihantar (<i>f_statusdokumen = 0</i>). Gunakan template untuk memudahkan.','tags'=>'apc format a catatan keseluruhan template dokumen'],
  ['cat'=>__('faq_cat_apc') ?: 'Anugerah Perkhidmatan Cemerlang (APC)','q'=>__('faq_q_apc_lock') ?: 'Kenapa saya tidak boleh edit catatan keseluruhan?','a'=>__('faq_a_apc_lock') ?: 'Catatan keseluruhan hanya boleh dikemaskini apabila <b>dokumen belum dihantar</b> (<i>f_statusdokumen = 0</i>). Selepas dokumen dihantar melalui <b>Subtab 2: Dokumen</b>, catatan akan dikunci. Untuk membuka semula, padam dokumen yang telah dihantar.','tags'=>'apc catatan dikunci dokumen dihantar status'],
  ['cat'=>__('faq_cat_apc') ?: 'Anugerah Perkhidmatan Cemerlang (APC)','q'=>__('faq_q_apc_print') ?: 'Bagaimana nak cetak Laporan Format A?','a'=>__('faq_a_apc_print') ?: 'Pergi ke <b>Laporan Format A</b>. Pilih tahun dan jabatan (atau "Semua"), kemudian klik butang <b>Cetak</b>. Pastikan catatan keseluruhan telah diisi dan tidak mengandungi placeholder <code>[ISI: ...]</code> sebelum mencetak.','tags'=>'apc format a cetak laporan print'],

  // Pengurusan Pengguna
  ['cat'=>__('faq_cat_pengurusan_pengguna') ?: 'Pengurusan Pengguna','q'=>__('faq_q_user_sync') ?: 'Bagaimana nak sync data pengguna dari Sybase?','a'=>__('faq_a_user_sync') ?: 'Pergi ke <b>Senarai Pengguna</b>. Klik butang <b>Sync Data</b> (icon refresh) untuk sync data secara manual. Data akan disegerakkan dari view Sybase <code>v630staf_service_skim_all</code> ke MySQL. Hanya staf aktif (<i>kodstatus = 1</i>) akan disync.','tags'=>'pengguna sync sybase mysql data staf aktif','audience'=>'Super Admin'],
  ['cat'=>__('faq_cat_pengurusan_pengguna') ?: 'Pengurusan Pengguna','q'=>__('faq_q_user_group') ?: 'Bagaimana nak tukar kumpulan pengguna?','a'=>__('faq_a_user_group') ?: 'Pergi ke <b>Senarai Pengguna</b>. Klik butang <b>Edit</b> pada baris pengguna yang ingin ditukar. Pilih kumpulan baru dari dropdown dan klik <b>Simpan</b>. Anda juga boleh tukar status akses (Dibenarkan/Disekat) dalam modal yang sama.','tags'=>'pengguna kumpulan group tukar akses status'],
  ['cat'=>__('faq_cat_pengurusan_pengguna') ?: 'Pengurusan Pengguna','q'=>__('faq_q_user_flag') ?: 'Apa maksud status "Dibenarkan" dan "Disekat"?','a' =>__('faq_a_user_flag') ?: '<b>Dibenarkan</b> (<i>f_flag = 1</i>) membolehkan pengguna log masuk ke sistem. <b>Disekat</b> (<i>f_flag = 0</i>) menghalang pengguna daripada log masuk. Status ini boleh ditukar melalui menu <b>Senarai Pengguna</b> oleh pentadbir.','tags'=>'pengguna akses dibenarkan disekat flag status'],

  // Laporan
  ['cat'=>__('faq_cat_laporan') ?: 'Laporan','q'=>__('faq_q_report') ?: 'Bagaimana nak lihat Laporan Format A?','a'=>__('faq_a_report') ?: 'Pergi ke <b>Laporan Format A</b>. Pilih tahun dan jabatan (atau "Semua") untuk melihat senarai jabatan yang telah menghantar dokumen. Klik butang <b>Lihat Dokumen</b> atau <b>Muat Turun Dokumen</b> untuk akses dokumen. Status "Dah Hantar" atau "Belum Hantar" akan dipaparkan.','tags'=>'laporan format a dokumen status hantar'],

  // Tetapan Sistem
  ['cat'=>__('faq_cat_tetapan_sistem') ?: 'Tetapan Sistem','q'=>__('faq_q_lang') ?: 'Bagaimana nak tukar bahasa paparan sistem?','a'=>__('faq_a_lang') ?: 'Klik ikon <b>bahasa</b> di topbar (ikon bendera) atau pergi ke <b>Profil</b> untuk menukar bahasa. Sistem menyokong 4 bahasa: Bahasa Melayu (BM), English (EN), 中文 (ZH), dan தமிழ் (TA). Pilihan bahasa akan disimpan dalam profil anda.','tags'=>'bahasa language tukar translate profil'],
  ['cat'=>__('faq_cat_tetapan_sistem') ?: 'Tetapan Sistem','q'=>__('faq_q_theme') ?: 'Bagaimana nak tukar tema sistem?','a'=>__('faq_a_theme') ?: 'Pergi ke <b>Profil</b> dan scroll ke bahagian <b>Tetapan Tema</b>. Anda boleh menukar warna sidebar, topbar, dan layout mode (light/dark). Tetapan akan disimpan secara automatik dan digunakan pada semua halaman.','tags'=>'tema theme warna sidebar topbar dark light','audience'=>'all'],
  // ---- Role specific / practical FAQs
  ['cat'=>'Pengurusan & Konfigurasi','q'=>'Bagaimana saya tambahkan/ubah menu akses untuk kumpulan pengguna?','a'=>'Pergi ke <b>Kumpulan Pengguna → Pilih kumpulan → Edit</b>. Di panel akses, tandakan menu yang perlu diaktifkan dan klik <b>Simpan</b>. Untuk perubahan besar, pastikan anda diuji pada akaun ujian terlebih dahulu.','tags'=>'kumpulan pengguna akses menu kumpulan','audience'=>'Super Admin'],
  ['cat'=>'HR Workflow','q'=>'Bagaimana cara Admin HR mengesahkan permohonan cuti atau pengesahan data prestasi?','a'=>'Admin HR boleh menggunakan modul <b>Pengurusan Cuti</b> dan <b>Pengesahan Prestasi</b>. Gunakan filter jabatan untuk mencari staf, buka rekod dan klik <b>Sahkan</b> atau <b>Tolak</b>. Pastikan anda mempunyai kebenaran <i>Approve</i>.','tags'=>'hr pengesahan cuti kelulusan','audience'=>'Admin HR'],
  ['cat'=>'Operasi & Kelulusan','q'=>'Bagaimana Admin Kewangan menguruskan kelulusan dan semakan tiket?','a'=>'Admin Kewangan mempunyai akses ke modul <b>Kelulusan</b> dan <b>Tiket Aduan</b>. Semak senarai tiket, klik nombor tiket untuk melihat butiran, berikan komen dan tetapkan status. Gunakan templat respons untuk menjimatkan masa.','tags'=>'kelulusan tiket aduan operasi','audience'=>'Admin Kewangan'],
  ['cat'=>'Akaun & Akses','q'=>'Bagaimana cara tukar kata laluan untuk pengguna lain (pentadbir)?','a'=>'Sebagai pentadbir (Super Admin), pergi ke <b>Senarai Pengguna</b>, pilih pengguna, klik <b>Reset Password</b> dan berikan pilihan untuk tetapkan kata laluan sementara. Ingat untuk minta pengguna menukar kata laluan selepas log masuk.','tags'=>'reset kata laluan pentadbir reset password','audience'=>'Super Admin'],
  ['cat'=>'Pengurusan Pengguna','q'=>'Soalan biasa untuk Admin HR: Bagaimana nak eksport senarai staf untuk laporan?','a'=>'Gunakan <b>Senarai Pengguna</b> → pilih penapis yang diperlukan → klik <b>Export</b> dan pilih format CSV atau Excel. Semak hak akses eksport pada kumpulan pengguna anda.','tags'=>'export csv excel senarai pengguna','audience'=>'Admin HR'],
  ['cat'=>'Operasi & Kelulusan','q'=>'Soalan biasa untuk Admin Kewangan: Bagaimana nak lihat log audit perubahan untuk rekod tertentu?','a'=>'Buka halaman rekod, klik butang <b>Jejak Audit</b> untuk melihat perubahan dan siapa yang membuatnya. Anda juga boleh muat turun log sebagai JSON jika perlu.','tags'=>'audit log jejak audit rekod muat turun','audience'=>'Admin Kewangan'],
];

/* ================= Kategori ================= */
// Semua FAQ dipaparkan (tiada penapisan berdasarkan role di page)
$cats = array_values(array_unique(array_map(fn($x)=> (string)$x['cat'], $faqs)));
array_unshift($cats, __('faq_cat_semua') ?: 'Semua');
$defaultCat = $cats[0];
?>
<!doctype html>
<html lang="<?= h($lang) ?>" data-bs-theme="<?= h($_SESSION['theme.layout'] ?? 'light') ?>">
<head>
  <?php
    $NEED_DATERANGE  = false;
    $NEED_VECTORMAP  = false;
    $NEED_DATATABLES = false;
    $NEED_SELECT2    = false;
    $INCLUDE_I18N_PRESTASI = true;
    include __DIR__ . '/../includes/head.php';
  ?>
  <style>
    body { font-size:.95rem }
    .faq-muted { color: var(--bs-secondary-color) }
    .faq-lead { max-width:800px; margin:0 auto }

    /* Left category list - Professional styling */
    .faq-cat .list-group-item{ 
      cursor:pointer; 
      user-select:none;
      border-left: 3px solid transparent;
      transition: all 0.3s ease;
      padding: 0.75rem 1rem;
      color: var(--bs-body-color); /* Use body text color for default */
    }
    .faq-cat .list-group-item:hover:not(.active) {
      background: var(--bs-primary-bg-subtle);
      border-left-color: var(--bs-primary);
      padding-left: 1.25rem;
      color: var(--bs-primary-text-emphasis) !important; /* Ensure text is visible on hover */
    }
    .faq-cat .list-group-item.active,
    .faq-cat .list-group-item.active:hover,
    .faq-cat .list-group-item.active:focus {
      background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important; /* Use specific blue gradient */
      border-left-color: #0d6efd !important;
      color: #ffffff !important; /* Force white text for active state */
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .faq-cat .list-group-item.active:hover {
      background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%) !important; /* Darker on hover */
    }
    /* Ensure text inside active item is always white */
    .faq-cat .list-group-item.active * {
      color: #ffffff !important;
    }

    /* Right content - Enhanced accordion */
    .accordion-button { 
      text-decoration:none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    .accordion-button:not(.collapsed) {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      color: var(--bs-primary);
    }
    .acc-item { 
      border-radius:.75rem; 
      overflow:hidden;
      border: 1px solid #e9ecef;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
    }
    .acc-item:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transform: translateY(-2px);
    }
    .acc-item + .acc-item { margin-top:1rem }

    .no-result { 
      display:none;
      border-radius: 0.75rem;
      border-left: 4px solid var(--bs-warning);
    }
    mark { 
      padding:.15em .35em; 
      border-radius:.3rem;
      background: linear-gradient(135deg, #fff3cd 0%, #ffc107 100%);
      font-weight: 600;
    }

    /* Search box enhancement */
    #faqSearch {
      border-radius: 0.5rem;
      border: 2px solid #e9ecef;
      transition: all 0.3s ease;
    }
    #faqSearch:focus {
      border-color: var(--bs-primary);
      box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
    }

    /* Count badge */
    #faqCount {
      background: var(--bs-primary-bg-subtle);
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 500;
    }
  </style>
</head>
<body id="body-layout"
  data-topbar-color="<?= h($_SESSION['theme.topbar'] ?? 'light') ?>"
  data-menu-color="<?= h($_SESSION['theme.menu'] ?? 'light') ?>"
  data-layout="vertical" data-sidebar-size="default" class="loading">

<div class="wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-page">
    <div class="content">
      <div class="container-fluid">

        <!-- Tajuk -->
        <div class="row mb-2"><div class="col-12">
          <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="page-title"><i class="ri-questionnaire-line me-1"></i> <?= __('faq_title') ?: 'Soalan Lazim (FAQ)' ?></h4>
            <div class="page-title-right">
              <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><i class="ri-home-4-line align-middle me-1"></i> <?= __('sidebar_dashboard') ?></li>
                <li class="breadcrumb-item active"><?= __('faq_title') ?: 'Soalan Lazim (FAQ)' ?></li>
              </ol>
            </div>
          </div>
        </div></div>

        <!-- Intro -->
        <div class="row"><div class="col-12">
          <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-center faq-lead text-white py-4">
              <div class="mb-3">
                <i class="ri-question-answer-line" style="font-size: 3rem; opacity: 0.9;"></i>
              </div>
              <h3 class="mt-1 mb-3 fw-bold"><?= h(__('faq_heading') ?: 'Bantuan Pantas') ?></h3>
              <p class="mb-0" style="opacity: 0.95;">
                <?= h(__('faq_intro') ?: 'Pilih kategori di sebelah kiri, atau cari ikut kata kunci di sebelah kanan.') ?>
              </p>
            </div>
          </div>
        </div></div>

        <!-- Layout: Left Categories | Right Accordion -->
        <div class="row g-3">
          <!-- LEFT: Kategori -->
          <div class="col-xl-3">
            <div class="card shadow-sm">
              <div class="card-header bg-gradient bg-primary text-white py-3">
                <i class="ri-folder-2-line me-2"></i> <strong><?= h(__('faq_label_category') ?: 'Kategori') ?></strong>
              </div>
              <div class="card-body p-2 faq-cat">
                <div id="catList" class="list-group list-group-flush">
                  <?php foreach ($cats as $i => $c): ?>
                    <button type="button"
                            class="list-group-item list-group-item-action<?= $i===0 ? ' active':'' ?>"
                            data-cat="<?= h($c) ?>">
                      <?= h($c) ?>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT: Carian + Accordion -->
          <div class="col-xl-9">
            <div class="card shadow-sm">
              <div class="card-body">
                <!-- Carian -->
                <div class="row align-items-center g-2 mb-2">
                  <div class="col-md-8">
                    <div class="input-group">
                      <span class="input-group-text"><i class="ri-search-line"></i></span>
                      <input id="faqSearch" type="search" class="form-control"
                             placeholder="<?= h(__('faq_placeholder_cari') ?: 'Cari dalam kategori terpilih…') ?>"
                             autocomplete="off">
                    </div>
                  </div>
                  <div class="col-md-4 text-md-end">
                    <span id="faqCount" class="small faq-muted"></span>
                  </div>
                </div>

                <!-- Accordion -->
                <div id="faqContainer" class="accordion"></div>

                <div id="faqNoResult" class="alert alert-warning no-result mt-3">
                  <i class="ri-information-line me-1"></i> <?= __('faq_tiada_padamu') ?: 'Tiada padanan ditemui. Cuba kata kunci lain.' ?>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /.container-fluid -->
    </div><!-- /.content -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>
  </div><!-- /.content-page -->
</div><!-- /.wrapper -->

<?php
  $NEED_JQUERY     = true;
  $NEED_SWEETALERT = false;
  include __DIR__ . '/../includes/script.php';
?>

<script>
(function(){
  'use strict';

  /* ==========================================================
   *  FAQ dengan Kategori (Left) & Accordion (Right)
   *  - Data PHP → JSON (render client-side)
   *  - Penapisan ikut kategori + carian (di kategori aktif)
   *  - Highlight kata kunci dalam tajuk & jawapan
   * ========================================================== */

  // ---------- Data dari PHP ----------
  const FAQS = <?= json_encode($faqs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
  const DEFAULT_CAT = <?= json_encode($defaultCat, JSON_UNESCAPED_UNICODE) ?>;

  // ---------- Elemen ----------
  const $  = (s, r=document)=> r.querySelector(s);
  const $$ = (s, r=document)=> Array.from(r.querySelectorAll(s));
  const catList      = $('#catList');
  const faqSearch    = $('#faqSearch');
  const faqContainer = $('#faqContainer');
  const noResult     = $('#faqNoResult');
  const faqCount     = $('#faqCount');

  // ---------- State ----------
  let activeCat = DEFAULT_CAT;     // "Semua" atau nama kategori lain
  let searchTerm = '';

  // ---------- Utils ----------
  const norm = s => String(s||'').toLowerCase();

  function makeId(prefix, idx){ return prefix + String(idx+1); }

  function escapeRe(s){ return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

  // Safely set innerHTML using DOMPurify when available, else DOMParser fallback
  function setSafeInnerHTML(el, html) {
    if (!el) return;
    if (!html) { el.innerHTML = ''; return; }
    if (window.DOMPurify && typeof DOMPurify.sanitize === 'function') {
      el.innerHTML = DOMPurify.sanitize(html);
      return;
    }
    try {
      const doc = new DOMParser().parseFromString('<div>' + html + '</div>', 'text/html');
      doc.querySelectorAll('script').forEach(s => s.remove());
      doc.querySelectorAll('*').forEach(n => {
        Array.from(n.attributes).forEach(a => {
          if (/^on/i.test(a.name)) n.removeAttribute(a.name);
          if ((a.name === 'src' || a.name === 'href') && /^javascript:/i.test(a.value)) n.removeAttribute(a.name);
        });
      });
      el.innerHTML = doc.body.firstChild ? doc.body.firstChild.innerHTML : '';
    } catch (e) {
      el.innerHTML = html;
    }
  }

  /** Highlight term dalam elemen (innerHTML). Simpan salinan asal pada data-raw. */
  function highlightIn(el, term){
    const t = norm(term).trim();
    const raw = el.getAttribute('data-raw') || el.innerHTML;
    if (!el.hasAttribute('data-raw')) el.setAttribute('data-raw', raw);
    if (!t) { setSafeInnerHTML(el, raw); return; }
    const rx = new RegExp('(' + escapeRe(t) + ')', 'ig');
    setSafeInnerHTML(el, raw.replace(rx, '<mark>$1</mark>'));
  }

  /** Kira & papar jumlah paparan */
  function updateCount(n, total){
    if (!faqCount) return;
    const countText = <?= json_encode(__('faq_count_display') ?: 'daripada', JSON_UNESCAPED_UNICODE) ?>;
    const soalanText = <?= json_encode(__('faq_count_soalan') ?: 'soalan dipaparkan', JSON_UNESCAPED_UNICODE) ?>;
    faqCount.textContent = n < total
      ? (n + ' ' + countText + ' ' + total + ' ' + soalanText)
      : (total + ' ' + soalanText);
  }

  // ---------- Render ----------
  /** Render accordion mengikut kategori aktif & carian */
  function renderFAQs(){
    const term = norm(searchTerm);
    const cat  = String(activeCat||'').trim();

    // Saring ikut kategori (gunakan translation untuk "Semua")
    const semuaText = <?= json_encode(__('faq_cat_semua') ?: 'Semua', JSON_UNESCAPED_UNICODE) ?>;
    let list = FAQS.filter(x => (cat === semuaText || x.cat === cat));

    // Saring ikut carian
    if (term){
      list = list.filter(x => {
        const hay = norm(x.q + ' ' + (x.a||'').replace(/<[^>]*>/g,'') + ' ' + (x.tags||''));
        return hay.indexOf(term) !== -1;
      });
    }

    // Kosongkan container
    faqContainer.innerHTML = '';

    if (!list.length){
      noResult.style.display = '';
      updateCount(0, 0);
      return;
    }
    noResult.style.display = 'none';
    updateCount(list.length, list.length);

    // Bina item accordion
    list.forEach((item, idx) => {
      const wrap  = document.createElement('div');
      wrap.className = 'accordion-item acc-item';
      wrap.dataset.cat  = item.cat;
      wrap.dataset.tags = norm(item.tags||'');

      const cid = makeId('faqC', idx);
      wrap.innerHTML = `
        <div class="accordion-header">
          <a href="#" class="accordion-button bg-light fw-medium text-dark" data-bs-toggle="collapse" data-bs-target="#${cid}" aria-expanded="false" aria-controls="${cid}">
            <span class="faq-q">${h(item.q)}</span>
          </a>
        </div>
        <div id="${cid}" class="collapse" data-bs-parent="#faqContainer">
          <div class="p-3">
            <div class="faq-a"><?= '' ?></div>
          </div>
        </div>
      `;

      // Masukkan jawapan sebagai HTML (dari server) dengan selamat (kandungan kita kawal)
      setSafeInnerHTML(wrap.querySelector('.faq-a'), item.a || '');

      // Highlight (tajuk & jawapan)
      highlightIn(wrap.querySelector('.accordion-button'), term);
      highlightIn(wrap.querySelector('.faq-a'), term);

      faqContainer.appendChild(wrap);
    });
  }

  // ---------- Interaksi ----------
  // Klik kategori (left)
  catList?.addEventListener('click', function(e){
    const btn = e.target.closest('[data-cat]');
    if (!btn) return;
    // Tukar aktif
    $$('.list-group-item', catList).forEach(x => x.classList.remove('active'));
    btn.classList.add('active');
    activeCat = btn.getAttribute('data-cat') || 'Semua';
    // Reset carian bila tukar kategori (pilihan: kekalkan — tukar ikut preferensi)
    // faqSearch.value = '';
    // searchTerm = '';
    renderFAQs();
  });

  // Carian (right)
  faqSearch?.addEventListener('input', function(){
    searchTerm = this.value || '';
    renderFAQs();
  });

  // ---------- Init ----------
  window.addEventListener('load', function(){
    // Preselect active cat dari butang yang ada kelas .active (fallback ke DEFAULT_CAT)
    const preset = $('[data-cat].active', catList);
    activeCat = preset ? (preset.getAttribute('data-cat') || DEFAULT_CAT) : DEFAULT_CAT;
    renderFAQs();
  });

  // ---------- Helper escape untuk template literal (PHP h() analog) ----------
  function h(s){
    return String(s ?? '')
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#39;');
  }
})();
</script>
</body>
</html>
