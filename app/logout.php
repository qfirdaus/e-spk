<?php
// declare(strict_types=1);

// // 🔐 Security Headers
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Pragma: no-cache");
// header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; object-src 'none';");

// // 🔁 Init Session & Autoload
// if (session_status() === PHP_SESSION_NONE) session_start();

// require_once __DIR__ . '/includes/init.php';         // Untuk autoload helper & lang
// require_once __DIR__ . '/controllers/LogoutController.php';

// // 🧯 Handle Logout
// Simple logout page: use LogoutController to perform logout and redirect
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/controllers/LogoutController.php';

// Perform full logout (audit, session cleanup, cookies) and redirect to index.php
LogoutController::handle();
// Failsafe
exit;

// Security headers
// Clear SSO cookie server-side if present (best-effort)
// $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
if (isset($_COOKIE['sso_cre'])) {
	setcookie('sso_cre', '', [
		'expires' => time() - 3600,
		'path' => '/',
		'domain' => '',
		'secure' => $secure,
		'httponly' => true,
		'samesite' => 'Lax'
	]);
}

// If SSO client defines IdP domain, ask opener to navigate there (no new tab),
// then close this window. If no opener, just attempt to close; if close is
// blocked, redirect to home as fallback.
define('SSO_SP_CLIENT_NOAUTO', true);
if (file_exists(__DIR__ . '/sso_sp_client.php')) {
	include_once __DIR__ . '/sso_sp_client.php';
	if (defined('SSO_IDP_DOMAIN')) {
		$idp = SSO_IDP_DOMAIN;
		$idpJs = json_encode($idp);
		echo "<script>
			(function(){
				var idp = $idpJs;
				try {
					if (window.opener) {
						try { window.opener.location = idp; } catch(e) { try { window.opener.location.reload(); } catch(e){} }
						try { window.close(); return; } catch(e) {}
					}
					// No opener: try close, otherwise redirect to IdP (no new tab)
					try { window.close(); } catch(e) {}
					// If still open, navigate the current window to IdP
					window.location = idp;
				} catch(err) {
					window.location = '/';
				}
			})();
		</script>";
		exit;
	}
}

// Default: try to close the window (works when opened by script). If
// blocked, redirect to home so user isn't left on a blank page.
echo '<script>try{ if (window.opener) { try{ window.opener.location.reload(); }catch(e){} window.close(); } else { window.close(); } } catch(e) { window.location = "/"; }</script>';
exit;
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/controllers/LogoutController.php';

// Perform audit and session cleanup
LogoutController::performLogoutNoRedirect();

// Clear SSO cookie server-side if present (best-effort)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
if (isset($_COOKIE['sso_cre'])) {
	setcookie('sso_cre', '', [
		'expires' => time() - 3600,
		'path' => '/',
		'domain' => '',
		'secure' => $secure,
		'httponly' => true,
		'samesite' => 'Lax'
	]);
}

// If SSO client defines IdP domain, ask opener to navigate there (no new tab),
// then close this window. If no opener, just attempt to close; if close is
// blocked, redirect to home as fallback.
define('SSO_SP_CLIENT_NOAUTO', true);
if (file_exists(__DIR__ . '/sso_sp_client.php')) {
	include_once __DIR__ . '/sso_sp_client.php';
	if (defined('SSO_IDP_DOMAIN')) {
		$idp = SSO_IDP_DOMAIN;
		$idpJs = json_encode($idp);
		echo "<script>
			(function(){
				var idp = $idpJs;
				try {
					if (window.opener) {
						try { window.opener.location = idp; } catch(e) { try { window.opener.location.reload(); } catch(e){} }
						try { window.close(); return; } catch(e) {}
					}
					// No opener: try close, otherwise redirect to IdP (no new tab)
					try { window.close(); } catch(e) {}
					// If still open, navigate the current window to IdP
					window.location = idp;
				} catch(err) {
					window.location = '/';
				}
			})();
		</script>";
		exit;
	}
}

// Default: try to close the window (works when opened by script). If
// blocked, redirect to home so user isn't left on a blank page.
echo '<script>try{ if (window.opener) { try{ window.opener.location.reload(); }catch(e){} window.close(); } else { window.close(); } } catch(e) { window.location = "/"; }</script>';
exit;