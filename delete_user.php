<?php
include 'session_config.php';
require 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'SuperAdmin') {
    $_SESSION['toastMessage'] = "Unauthorized access.";
    $_SESSION['toastType'] = "error";
    header("Location: login.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;

if ($user_id == 0) {
    $_SESSION['toastMessage'] = "Invalid user ID.";
    $_SESSION['toastType'] = "error";
    header("Location: all_users.php");
    exit;
}

// Prevent deletion of own account
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['toastMessage'] = "You cannot delete your own account.";
    $_SESSION['toastType'] = "error";
    header("Location: all_users.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $_SESSION['toastMessage'] = "User deleted successfully.";
    $_SESSION['toastType'] = "success";
} else {
    $_SESSION['toastMessage'] = "Failed to delete user.";
    $_SESSION['toastType'] = "error";
}
$stmt->close();

header("Location: all_users.php");
exit;
