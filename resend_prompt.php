<?php
include 'session_config.php';
require_once 'functions.php';
include 'header.php';

if (!isset($_SESSION['resend_user_id'], $_SESSION['resend_email'])) {
    $_SESSION['toastMessage'] = "Missing session info. Please try again.";
    $_SESSION['toastType'] = "error";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['resend_user_id'];
$username = $_SESSION['resend_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resend Verification - PREMOS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <link rel="stylesheet" href="styles/footer.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
</head>
<body>
<div class="container py-4">
    <h4 class="mb-3">Verification Link Expired</h4>
    <p>The verification link for <strong><?= htmlspecialchars($username) ?></strong> has expired.</p>
    <p>You may request a new one below:</p>

    <form action="resend_verification.php" method="POST" class="mb-3">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
        <a href="login.php" class="btn btn-outline-secondary ms-2">Cancel</a>
    </form>
</div>

<?php include 'toastr_handler.php'; ?>
<?php include 'footer.php'; ?>
<script src="scripts/header.js" defer></script>
</body>
</html>
