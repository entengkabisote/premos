<?php
include('session_config.php');
include('db_connect.php');

// Role check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'SuperAdmin'])) {
    header('Location: vessel.php');
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['toastMessage'] = "Invalid request method.";
    $_SESSION['toastType'] = "danger";
    header("Location: vessel.php");
    exit;
}

// CSRF protection
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['toastMessage'] = "Invalid CSRF token.";
    $_SESSION['toastType'] = "danger";
    header("Location: vessel.php");
    exit;
}

// Sanitize and validate ID input
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM vessels WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['toastMessage'] = "Vessel deleted successfully!";
            $_SESSION['toastType'] = "success";
        } else {
            $_SESSION['toastMessage'] = "Error deleting vessel: " . $stmt->error;
            $_SESSION['toastType'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['toastMessage'] = "Database error: " . $conn->error;
        $_SESSION['toastType'] = "danger";
    }
} else {
    $_SESSION['toastMessage'] = "Invalid vessel ID.";
    $_SESSION['toastType'] = "danger";
}

header("Location: vessel.php");
exit;
?>
