<?php
include('session_config.php');
include 'db_connect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = "SELECT * FROM users WHERE verification_token = ? AND is_verified = 0";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $_SESSION['temp_user_id'] = $user['user_id'];
        header('Location: set_password.php');
        exit();
    } else {
        $error_msg = "This link is invalid or you have already verified your email.";
    }
} else {
    $error_msg = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification | Planned Maintenance System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
    <h4 class="mb-4">Email Verification</h4>

    <div class="card p-4 shadow-sm mb-4">
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger text-center mb-3">
                <?= htmlspecialchars($error_msg) ?>
            </div>
            <div class="text-center">
                <a href="index.php" class="btn btn-primary">🔙 Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'toastr_handler.php'; ?>
<script src="scripts/header.js" defer></script>
</body>
</html>
