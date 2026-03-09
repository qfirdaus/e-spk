<?php
$version = time();
$currentPage = $currentPage ?? basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
?>

<!-- ========== Core: jQuery & Bootstrap (NO defer) ========== -->
<!-- Pastikan vendor.min.js TIDAK bundle jQuery. Kita nak hanya satu sumber jQuery -->
<script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

<!-- ========== Vendor & Plugin JS (OK untuk defer) ========== -->
<script src="<?= base_url('assets/js/vendor.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/daterangepicker/moment.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/daterangepicker/daterangepicker.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/apexcharts/apexcharts.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js') ?>?v=<?= $version ?>" defer></script>

<!-- ✅ DataTables JS (Bootstrap 5) -->
<script src="<?= base_url('assets/vendor/datatables.net/js/jquery.dataTables.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js') ?>?v=<?= $version ?>" defer></script>

<!-- (Optional) Add-on plugins -->
<script src="<?= base_url('assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/datatables.net-fixedcolumns/js/dataTables.fixedColumns.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/datatables.net-buttons/js/dataTables.buttons.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/vendor/datatables.net-select/js/dataTables.select.min.js') ?>?v=<?= $version ?>" defer></script>

<!-- ========== Page-Specific JS ========== -->
<?php 
// ✅ Only load demo.dashboard.js for actual demo dashboard, not prestasi dashboard
if (str_ends_with($currentPage, 'dashboard.php') && strpos($_SERVER['REQUEST_URI'] ?? '', 'prestasi') === false): ?>
  <script src="<?= base_url('assets/js/pages/demo.dashboard.js') ?>?v=<?= $version ?>" defer></script>
<?php endif; ?>

<!-- ========== App Core JS (OK defer) ========== -->
<script src="<?= base_url('assets/js/app.unmin.js') ?>?v=<?= $version ?>" defer></script>
<script src="<?= base_url('assets/js/theme-setting.js') ?>?v=<?= $version ?>" defer></script>

<!-- ========== Alpine.js (for interactive components) ========== -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- ========== SweetAlert (NO defer, sebab render_alert mungkin run segera) ========== -->
<script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js') ?>"></script>

<!-- Loader JS (tanpa defer, biar dia hijack fetch/klik awal-awal) -->
<script src="<?= base_url('assets/js/loader.js') ?>?v=<?= $version ?>"></script>

<!-- ========== Global JavaScript Variables ========== -->
<?php
// Expose CSRF token and BASE_URL to JavaScript
$csrfToken = $_SESSION['csrf_token'] ?? '';
$baseUrl = rtrim(base_url(''), '/');
?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
<script>
  // Safe storage helper (avoid errors when storage is blocked)
  window.safeStorage = window.safeStorage || {
    get(k){ try { return localStorage.getItem(k); } catch(e){ return null; } },
    set(k,v){ try { localStorage.setItem(k,v); return true; } catch(e){ return false; } }
  };
  // Global variables for AJAX requests
  window.csrfToken = <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE) ?>;
  window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php
require_once __DIR__ . '/../setting/helper/alert_helper.php';
if (function_exists('render_alert')) {
    render_alert();
}
?>

<!-- ========== Navigation Fallback ========== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a.nav-link, a.dropdown-item').forEach(el => {
    if (!el.hasAttribute('href')) el.setAttribute('href', '#');
  });
});
</script>

<!-- ========== Session Idle Guard ========== -->
<?php
$isLoggedIn = !empty($_SESSION['f_stafID']);
?>
<?php if ($isLoggedIn): ?>
<script>
(function () {
  'use strict';

  const IDLE_LIMIT_MS = 10 * 60 * 1000;   // 10 minit idle
  const PROMPT_MS = 60 * 1000;            // tunggu respon 1 minit
  const KEEPALIVE_URL = <?= json_encode(base_url('ajax/session-keepalive.php'), JSON_UNESCAPED_UNICODE) ?>;
  const LOGOUT_URL = <?= json_encode(base_url('logout.php'), JSON_UNESCAPED_UNICODE) ?>;

  const I18N = {
    title: <?= json_encode(__('session_idle_title') ?: 'Masih di sini?', JSON_UNESCAPED_UNICODE) ?>,
    text: <?= json_encode(__('session_idle_text') ?: 'Tiada aktiviti 10 minit. Kekal log masuk?', JSON_UNESCAPED_UNICODE) ?>,
    stay: <?= json_encode(__('session_idle_stay_connected') ?: 'Kekal Log Masuk', JSON_UNESCAPED_UNICODE) ?>,
    logout: <?= json_encode(__('session_idle_logout_now') ?: 'Log Keluar', JSON_UNESCAPED_UNICODE) ?>,
    timeoutText: <?= json_encode(__('session_idle_timeout_text') ?: 'Auto log keluar dalam 1 minit.', JSON_UNESCAPED_UNICODE) ?>,
    timeoutTitle: <?= json_encode(__('session_idle_timeout_title') ?: 'Sesi Tamat', JSON_UNESCAPED_UNICODE) ?>,
    timeoutLogoutNow: <?= json_encode(__('session_idle_timeout_logout_now') ?: 'Tiada respons. Sistem akan log keluar sekarang.', JSON_UNESCAPED_UNICODE) ?>,
    keepaliveFailed: <?= json_encode(__('session_idle_keepalive_failed') ?: 'Sesi tidak dapat diperbaharui. Anda akan dilog keluar.', JSON_UNESCAPED_UNICODE) ?>
  };

  let lastActivityAt = Date.now();
  let promptOpen = false;
  let checking = false;

  const markActivity = () => {
    if (promptOpen) return;
    lastActivityAt = Date.now();
  };

  const forceLogout = () => {
    window.location.href = LOGOUT_URL;
  };

  const keepAlive = async () => {
    const res = await fetch(KEEPALIVE_URL, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      cache: 'no-store'
    });
    if (!res.ok) throw new Error('keepalive_failed');
    const data = await res.json();
    if (!data || data.ok !== true) throw new Error('keepalive_invalid');
  };

  const showTimeoutLogoutAlert = async () => {
    if (window.Swal && typeof Swal.fire === 'function') {
      await Swal.fire({
        icon: 'info',
        title: I18N.timeoutTitle,
        text: I18N.timeoutLogoutNow,
        confirmButtonText: 'OK',
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    }
    forceLogout();
  };

  const showIdlePrompt = async () => {
    if (promptOpen) return;
    promptOpen = true;

    try {
      if (window.Swal && typeof Swal.fire === 'function') {
        const result = await Swal.fire({
          icon: 'warning',
          title: I18N.title,
          text: I18N.text,
          footer: I18N.timeoutText,
          showCancelButton: true,
          confirmButtonText: I18N.stay,
          cancelButtonText: I18N.logout,
          allowOutsideClick: false,
          allowEscapeKey: false,
          timer: PROMPT_MS,
          timerProgressBar: true
        });

        if (result.isConfirmed) {
          await keepAlive();
          lastActivityAt = Date.now();
          promptOpen = false;
          return;
        }

        if (result.dismiss === Swal.DismissReason.cancel) {
          promptOpen = false;
          forceLogout();
          return;
        }

        promptOpen = false;
        await showTimeoutLogoutAlert();
        return;
      }

      const stay = window.confirm(I18N.text);
      if (stay) {
        await keepAlive();
        lastActivityAt = Date.now();
        promptOpen = false;
      } else {
        promptOpen = false;
        forceLogout();
      }
    } catch (e) {
      promptOpen = false;
      if (window.Swal && typeof Swal.fire === 'function') {
        await Swal.fire({
          icon: 'error',
          title: I18N.timeoutTitle,
          text: I18N.keepaliveFailed,
          confirmButtonText: 'OK'
        });
      }
      forceLogout();
    }
  };

  const checkIdle = async () => {
    if (checking || promptOpen) return;
    checking = true;
    try {
      const idleFor = Date.now() - lastActivityAt;
      if (idleFor >= IDLE_LIMIT_MS) {
        await showIdlePrompt();
      }
    } finally {
      checking = false;
    }
  };

  const throttledMarkActivity = (() => {
    let last = 0;
    return () => {
      const now = Date.now();
      if (now - last < 500) return;
      last = now;
      markActivity();
    };
  })();

  ['click', 'keydown', 'mousedown', 'touchstart'].forEach(evt => {
    document.addEventListener(evt, markActivity, { passive: true });
  });
  ['mousemove', 'scroll', 'touchmove'].forEach(evt => {
    document.addEventListener(evt, throttledMarkActivity, { passive: true });
  });
  window.addEventListener('focus', markActivity, { passive: true });

  setInterval(checkIdle, 1000);
})();
</script>
<?php endif; ?>
<!-- ========== Topbar Theme Toggle ========== -->
<script>
const updateThemeIcon = isDark => {
  const icon = document.getElementById('theme-mode-icon');
  if (!icon) return;
  icon.classList.remove('ri-moon-fill', 'ri-sun-fill');
  icon.classList.add(isDark ? 'ri-sun-fill' : 'ri-moon-fill');
};

const applyTheme = mode => {
  const html = document.documentElement;
  const isDark = mode === 'dark';
  html.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
  document.body.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
  updateThemeIcon(isDark);
  safeStorage.set('theme', mode);
};

document.addEventListener('DOMContentLoaded', () => {
  const savedTheme = safeStorage.get('theme') || 'light';
  applyTheme(savedTheme);

  document.getElementById('light-dark-mode')?.addEventListener('click', () => {
    const newTheme = safeStorage.get('theme') === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);

    const formData = new FormData();
    formData.append('theme_type', 'data-bs-theme');
    formData.append('theme_value', newTheme);

    fetch('<?= base_url("/setting/set_theme.php") ?>', {
      method: 'POST',
      body: formData
    });
  });
});
</script>

<!-- ========== Fullscreen Toggle ========== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('toggle-fullscreen');
  const icon = btn?.querySelector('i');

  btn?.addEventListener('click', function (e) {
    e.preventDefault();
    const doc = document;
    const docEl = document.documentElement;

    if (!doc.fullscreenElement) {
      docEl.requestFullscreen?.();
    } else {
      doc.exitFullscreen?.();
    }
  });

  document.addEventListener('fullscreenchange', () => {
    const isFullscreen = !!document.fullscreenElement;
    if (!icon) return;
    icon.classList.toggle('ri-fullscreen-line', !isFullscreen);
    icon.classList.toggle('ri-fullscreen-exit-line', isFullscreen);
  });
});
</script>

<!-- ========== Theme Settings Panel Sync ========== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const topbar = document.getElementById('topbar');
  const sidebar = document.getElementById('leftside-menu');
  const offcanvas = document.getElementById('theme-settings-offcanvas');

  const saved = {
    'data-bs-theme': safeStorage.get('layout-mode') || document.documentElement.getAttribute('data-bs-theme') || 'light',
    'data-topbar-color': safeStorage.get('topbar-color') || document.body.getAttribute('data-topbar-color') || 'light',
    'data-menu-color': safeStorage.get('sidebar-color') || document.body.getAttribute('data-menu-color') || 'light',
  };

  if (topbar) {
    topbar.className = topbar.className
      .split(' ')
      .filter(c => !c.startsWith('topbar-'))
      .join(' ')
      .trim();
    topbar.classList.add('topbar-' + saved['data-topbar-color']);
  }

  if (sidebar) {
    sidebar.setAttribute('data-menu-color', saved['data-menu-color']);
  }

  if (offcanvas) {
    offcanvas.addEventListener('shown.bs.offcanvas', () => {
      Object.entries(saved).forEach(([type, value]) => {
        const input = document.querySelector(`input[name="${type}"][value="${value}"]`);
        if (input) {
          input.checked = true;
        } else {
          document.querySelectorAll(`input[name="${type}"]`).forEach(i => i.checked = false);
        }
      });
    });
  }

  // ✅ Track if theme has changed (for reload on click outside)
  let themeChanged = false;
  let themeChangeTimeout = null;

  // ✅ Theme changes handler - apply immediately when user selects theme
  document.querySelectorAll('input[name="data-bs-theme"], input[name="data-topbar-color"], input[name="data-menu-color"]').forEach(input => {
    input.addEventListener('change', () => {
      const type = input.name;
      const value = input.value;

      // ✅ Mark that theme has changed
      themeChanged = true;

      // ✅ Update localStorage first
      if (type === 'data-bs-theme') {
        safeStorage.set('layout-mode', value);
      } else if (type === 'data-topbar-color') {
        safeStorage.set('topbar-color', value);
      } else if (type === 'data-menu-color') {
        safeStorage.set('sidebar-color', value);
      }

      // ✅ Apply theme immediately - ensure it works even if function not loaded yet
      const applyThemeNow = () => {
        const topbarColor = safeStorage.get('topbar-color') || 'light';
        const sidebarColor = safeStorage.get('sidebar-color') || 'dark';
        const layoutMode = safeStorage.get('layout-mode') || 'light';

        // Apply layout mode
        document.documentElement.setAttribute('data-bs-theme', layoutMode);
        document.body.setAttribute('data-bs-theme', layoutMode);
        if (typeof updateThemeIcon === 'function') {
          updateThemeIcon(layoutMode === 'dark');
        }

        // Apply topbar
        if (topbar) {
          topbar.className = topbar.className
            .split(' ')
            .filter(c => !c.startsWith('topbar-'))
            .join(' ')
            .trim();
          topbar.classList.add('topbar-' + topbarColor);
        }
        document.body.setAttribute('data-topbar-color', topbarColor);

        // Apply sidebar
        if (sidebar) {
          sidebar.setAttribute('data-menu-color', sidebarColor);
        }
        document.body.setAttribute('data-menu-color', sidebarColor);
      };

      // ✅ Apply immediately (manual method)
      applyThemeNow();

      // ✅ Also try to use applyThemeSetting() if available (more comprehensive)
      // Use setTimeout to ensure function is loaded if script loads after this
      setTimeout(() => {
        if (typeof applyThemeSetting === 'function') {
          applyThemeSetting();
        }
      }, 0);

      // ✅ Clear any existing timeout
      if (themeChangeTimeout) {
        clearTimeout(themeChangeTimeout);
      }

      // ✅ Auto-save is handled by theme-setting.js observeThemeChanges()
      // Theme is already applied above, so no reload needed
    });
  });

  // ✅ Reload page when offcanvas closes after theme change
  // Use both direct event listener and document-level listener for reliability
  const handleOffcanvasClose = function() {
    // ✅ Check if theme was changed while offcanvas was open
    if (themeChanged) {
      // Clear any existing timeout
      if (themeChangeTimeout) {
        clearTimeout(themeChangeTimeout);
      }
      // Wait a bit to ensure save is complete, then reload
      themeChangeTimeout = setTimeout(() => {
        window.location.reload();
      }, 1000); // Wait 1 second for save to complete
    } else {
      // Reset flag if no changes
      themeChanged = false;
    }
  };

  if (offcanvas) {
    // ✅ Listen for offcanvas close event (fires after offcanvas is fully hidden)
    offcanvas.addEventListener('hidden.bs.offcanvas', handleOffcanvasClose);
  } else {
    // ✅ Fallback: Try to find offcanvas later
    setTimeout(() => {
      const offcanvasFallback = document.getElementById('theme-settings-offcanvas');
      if (offcanvasFallback) {
        offcanvasFallback.addEventListener('hidden.bs.offcanvas', handleOffcanvasClose);
      }
    }, 500);
  }

  // ✅ Also listen on document level for Bootstrap offcanvas events (more reliable)
  document.addEventListener('hidden.bs.offcanvas', function(e) {
    if (e.target && e.target.id === 'theme-settings-offcanvas') {
      handleOffcanvasClose();
    }
  });
});
</script>

<script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
<script>
// GLOBAL LOADER
(function () {
  // ====== CONFIG BOLEH DITETAPKAN DI PAGE ======
  // <body data-loader="off|bar|overlay|both" data-loader-spinner="Memuat data…"></body>
  var body   = document.body;
  var mode   = (body.dataset.loader || 'bar').toLowerCase(); // default: bar
  var text   = body.dataset.loaderSpinner || 'Memuat data…';
  if (mode === 'off') return;

  // ====== NProgress (bar) ======
  var useBar = (mode === 'bar' || mode === 'both');
  if (useBar && window.NProgress) {
    NProgress.configure({ showSpinner:false, trickleSpeed:120 });
    window.addEventListener('beforeunload', function(){ NProgress.start(); });
    window.addEventListener('pageshow', function(){ setTimeout(function(){ try{NProgress.done();}catch(e){} },80); });
  }

  // ====== Overlay (spinner) ======
  var useOverlay = (mode === 'overlay' || mode === 'both');
  var overlay;
  function ovShow(){ if(!useOverlay) return; overlay.classList.add('show'); }
  function ovHide(){ if(!useOverlay) return; overlay.classList.remove('show'); }
  if (useOverlay) {
    overlay = document.createElement('div');
    overlay.className = 'loader-overlay';
    // Build spinner content without using string concatenation to avoid inserting untrusted HTML
    (function(){
      var inner = document.createElement('div'); inner.className = 'text-center';
      var spinner = document.createElement('div'); spinner.className = 'spinner-border'; spinner.setAttribute('role','status'); spinner.setAttribute('aria-hidden','true');
      var txt = document.createElement('div'); txt.className = 'mt-2 small text-muted'; txt.textContent = text || '';
      inner.appendChild(spinner);
      inner.appendChild(txt);
      overlay.appendChild(inner);
    })();
    document.body.appendChild(overlay);
  }

  function startAll(){ if(useBar) NProgress.start(); ovShow(); }
  function stopAll(){  if(useBar) NProgress.done(); ovHide(); }

  // ====== TRIGGER: LINK CLICK ======
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href') || '';

    // abaikan: anchor, new tab, modifier keys, file download/export
    if (href.charAt(0) === '#') return;
    if (a.hasAttribute('download') || a.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;
    if (a.dataset.noLoader !== undefined) return;
    if (/download=excel/i.test(href)) return; // elak “terkunci” bila hanya trigger download

    startAll();
  }, true);

  // ====== TRIGGER: FORM SUBMIT ======
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f && f.dataset.noLoader !== undefined) return;
    startAll();
  }, true);

  // ====== jQuery AJAX (jika ada) ======
  if (window.jQuery) {
    $(document).ajaxStart(startAll);
    $(document).ajaxStop(stopAll);
    $(document).ajaxError(stopAll);
  }

  // safety stop
  window.addEventListener('pageshow', stopAll);
})();
</script>

