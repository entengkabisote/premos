<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', realpath(__DIR__ . '/../session/tmp'));
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);

    session_start();
}

// ⏱ Timeout checker
$timeoutDuration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutDuration) {
    session_unset();
    session_destroy();
    require_once __FILE__; // restart session
    $_SESSION['toastMessage'] = 'You have been logged out due to inactivity.';
    $_SESSION['toastType'] = 'warning';
    header("Location: login.php");
    exit;
}

$_SESSION['last_activity'] = time();
