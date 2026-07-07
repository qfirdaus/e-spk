<?php
session_start();
echo isSet($_GET['lang']);
if (isSet($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_SESSION['lang'], time() + (3600 * 24 * 30));
    echo $_SESSION['lang'];
} else if (isSet($_COOKIE['lang'])) {
    $_SESSION['lang'] = $_COOKIE['lang'];
} else {
    $_SESSION['lang'] = 'bm';
}

header('Location: laman-utama');

?>