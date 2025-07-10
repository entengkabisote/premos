<?php
// functions.php

if (!function_exists('getSetting')) {
    function getSetting($conn, $key, $default = '') {
        $stmt = $conn->prepare("SELECT value FROM settings WHERE `key` = ? LIMIT 1");
        if (!$stmt) return $default;
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->bind_result($val);
        return $stmt->fetch() ? $val : $default;
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'SuperAdmin';
    }
}

if (!function_exists('redirectIfNotLoggedIn')) {
    function redirectIfNotLoggedIn() {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            header("Location: login.php");
            exit;
        }
    }
}

if (!function_exists('redirectIfNotSuperAdmin')) {
    function redirectIfNotSuperAdmin() {
        if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'SuperAdmin') {
            header("Location: login.php");
            exit;
        }
    }
}


function setToast($msg, $type = 'info') {
    $_SESSION['toastMessage'] = $msg;
    $_SESSION['toastType'] = $type;
}

function is_valid_image($tmp_name, $file_name, $allowed_types, $allowed_exts, $max_size) {
    // Check extension
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) return false;

    // Check MIME type
    $file_type = mime_content_type($tmp_name);
    if (!in_array($file_type, $allowed_types)) return false;

    // Check image size
    if (filesize($tmp_name) > $max_size) return false;

    // Check real image using getimagesize (ito ang pinakaimportante)
    if (!@getimagesize($tmp_name)) return false;

    return true;
}