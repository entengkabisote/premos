<?php
include('session_config.php');
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: index.php');
    exit();
}

$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Password | Planned Maintenance System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom styles -->
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
    <h4 class="mb-4">Set New Password</h4>

    <div class="card p-4 shadow-sm mb-4">
        <form action="save_password.php" method="post" autocomplete="off">
            <input type="hidden" name="user_id" value="<?= $_SESSION['temp_user_id'] ?>">
            <div class="row mb-3">
                <div class="col-12">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('password')">
                            <i id="password-toggle-icon" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('confirm_password')">
                            <i id="confirm_password-toggle-icon" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">🏠 Home</a>
                <button type="submit" class="btn btn-primary">🔐 Set Password</button>
            </div>
        </form>
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

<script>
    <?php if (!empty($error_message)): ?>
        toastr.error("<?= addslashes($error_message) ?>");
    <?php endif; ?>

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '-toggle-icon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        }
    }
</script>
</body>
</html>
