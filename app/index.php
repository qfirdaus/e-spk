<?php
if (isset($_COOKIE['sso_cre'])) {
    echo '<script>document.cookie = "sso_cre=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";</script>';
}

$version = time();

// ✅ Secure Session Settings
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
ini_set('session.cookie_httponly', '1');

if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Include & Init
//include_once __DIR__ . '/sso_sp_client.php';
require_once __DIR__ . '/includes/init.php';

// ✅ Language Detection
$uri  = $_SERVER['REQUEST_URI'];
$lang = $_SESSION['lang'] ?? 'ms';

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ms', 'en', 'zh', 'ta'])) {
    if ($lang !== $_GET['lang']) {
        $_SESSION['lang'] = $_GET['lang'];
        header("Location: " . strtok($uri, '?'));
        exit;
    }
}
$lang = $_SESSION['lang'] ?? 'ms';

// ✅ CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ✅ Redirect if already logged in
if (!empty($_SESSION['f_stafID'])) {
    redirect('pages/dashboard.php');
}

// ✅ Optional login flags
$login_failed   = $login_failed ?? false;
$locked_seconds = $locked_seconds ?? 0;
$attempts_left  = $attempts_left ?? 3;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('login_title') ?> | <?= htmlspecialchars(app_config('site.title', 'e-Prestasi')) ?></title>
  <link rel="icon" href="<?= base_url(app_config('site.favicon', 'assets/images/default.ico')) ?>" type="image/x-icon">

  <link rel="stylesheet" href="<?= base_url('assets/css/output.css?v=' . $version) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js?v=<?= $version ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11?v=<?= $version ?>"></script>

  <style>
    body { font-family: 'Poppins', sans-serif; font-size: 13px; }
  </style>
</head>
<body class="bg-gray-100" x-data="{}">

<header class="bg-white shadow">
  <div class="max-w-7xl mx-auto p-4">
    <img src="<?= base_url('assets/images/e-prestasi-logo-upnm.png') ?>" alt="UPNM Logo" class="w-44">
    <nav class="flex space-x-4 border-b border-gray-300 mt-4" id="navTabs">
      <button class="tab-btn px-4 py-2 font-semibold text-[#0babcd] border-b-4 border-[#0babcd]"><?= __('login_nav.home') ?></button>
      <button @click="$store.faq?.showFaq?.()" class="tab-btn px-4 py-2 text-[#0babcd] hover:font-semibold hover:border-b-4 hover:border-[#0babcd]"><?= __('login_nav.faq') ?></button>
      <a href="https://directory.upnm.edu.my" target="_blank" 
        class="px-4 py-2 text-[#0babcd] hover:font-semibold hover:border-b-4 hover:border-[#0babcd]">
          <?= __('login_nav.directory') ?>
      </a>
    </nav>
  </div>
</header>

<main class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 px-4">
  <!-- Banner -->
  <div class="md:col-span-2 bg-white rounded-xl shadow overflow-hidden">
    <div class="relative w-full h-[300px] sm:h-[350px] md:h-[400px] overflow-hidden"
         x-data="{ active: 0, banners: ['banner1.jpg', 'banner2.jpg', 'banner3.jpg', 'banner4.jpg'] }"
         x-init="setInterval(() => { active = (active + 1) % banners.length }, 4000)">
      <template x-for="(banner, index) in banners" :key="index">
        <img :src="`<?= base_url('assets/images/') ?>${banner}`"
             alt="Banner"
             class="absolute top-0 left-0 w-full h-full object-cover transition-opacity duration-700 ease-in-out"
             :class="{ 'opacity-0': active !== index, 'opacity-100': active === index }">
      </template>
    </div>
    <section class="p-6 text-sm">
      <h2 class="text-lg font-semibold mb-4">📢 <?= __('login_contact_title') ?></h2>
      <p class="text-gray-700"><?= __('login_info') ?></p>
      <p class="mt-2 text-gray-600 text-xs"><?= __('login_contact') ?></p>
    </section>
  </div>

  <!-- Login Form -->
  <div class="bg-white p-8 rounded-xl shadow">
    <div class="text-center mb-6">
      <img src="<?= base_url('assets/images/e-prestasi-logo.png') ?>" class="mx-auto h-20 mb-2" alt="Logo">
      <h2 class="text-lg font-bold text-gray-700"><?= __('login_heading') ?></h2>
    </div>
    
    <form method="POST" action="<?= base_path('login.php') ?>" autocomplete="off" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

      <div>
        <label for="f_stafID" class="block font-medium text-gray-700"><?= __('login_staffid') ?></label>
        <input id="f_stafID" name="f_stafID" type="text" required
              placeholder="<?= __('login_userid_placeholder') ?>"
              class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500"
              autocomplete="username">
      </div>

      <div>
        <label for="f_password" class="block font-medium text-gray-700"><?= __('login_password') ?></label>
        <input id="f_password" name="f_password" type="password" required
              placeholder="******"
              class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500"
              autocomplete="current-password">
      </div>

      <div class="text-right">
        <a href="#" onclick="return false;" class="text-sm text-blue-600 hover:underline"><?= __('login_forgot') ?></a>
      </div>

      <p class="text-sm text-gray-600 text-center"><?= __('login_note') ?></p>

      <button type="submit"
              class="w-full bg-blue-600 text-white py-2 rounded-md font-semibold hover:bg-blue-700 transition">
        <?= __('login_btnLogin') ?>
      </button>
    </form>

    <p class="text-center text-gray-400 text-xs mt-10">
      <?= htmlspecialchars(app_config('system.name', 'e-Prestasi')) ?> <?= htmlspecialchars(app_config('system.version', '2.0.0')) ?><br>
      <?= htmlspecialchars(app_config('system.author', 'Hak Cipta © UPNM')) ?>
    </p>

    <div class="text-center mt-4 text-xs">
      🌐 <?= __('login_language') ?>:
      <a href="?lang=ms" class="underline <?= $lang === 'ms' ? 'font-bold text-blue-700' : '' ?>">BM</a> | 
      <a href="?lang=en" class="underline <?= $lang === 'en' ? 'font-bold text-blue-700' : '' ?>">EN</a>
    </div>
  </div>
</main>

<!-- ✅ Alert rendering -->
<?php if (function_exists('render_alert')) render_alert(); ?>

</body>
</html>
