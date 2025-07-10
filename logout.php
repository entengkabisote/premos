<?php
require_once 'session_config.php';

// Clear all session data
session_unset();
session_destroy();

// Start a clean session using session_config again
require_once 'session_config.php';
$_SESSION['toastMessage'] = "You have been logged out successfully.";
$_SESSION['toastType'] = "success";

header("Location: index.php");
exit;
?>
