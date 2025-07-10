<?php
include 'session_config.php';
require 'db_connect.php';
require_once 'functions.php';
include 'header.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'SuperAdmin') {
    header("Location: login.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT username, email, fullname, company, role, status, is_superintendent, two_factor_enabled FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['toastMessage'] = "User not found.";
    $_SESSION['toastType'] = "error";
    header("Location: all_users.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $company = trim($_POST['company']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $is_superintendent = isset($_POST['is_superintendent']) ? 1 : 0;
    $two_factor_enabled = isset($_POST['two_factor_enabled']) ? 1 : 0;

    // $update = $conn->prepare("UPDATE users SET fullname=?, email=?, company=?, role=?, status=?, is_superintendent=?, two_factor_enabled=? WHERE user_id=?");
    // $update->bind_param("ssssssii", $fullname, $email, $company, $role, $status, $is_superintendent, $two_factor_enabled, $user_id);
    $update = $conn->prepare("UPDATE users SET fullname=?, email=?, company=?, role=?, status=?, is_superintendent=?, two_factor_enabled=?, updated_at=NOW() WHERE user_id=?");
    $update->bind_param("ssssssii", $fullname, $email, $company, $role, $status, $is_superintendent, $two_factor_enabled, $user_id);



    if ($update->execute()) {
        $_SESSION['toastMessage'] = "User updated successfully.";
        $_SESSION['toastType'] = "success";
        header("Location: all_users.php");
        exit;
    } else {
        $_SESSION['toastMessage'] = "Update failed.";
        $_SESSION['toastType'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - PREMOS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/edit_user.css">
    <!-- Toastr CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
	<!-- jQuery (required by toastr) -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<!-- Toastr JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
	<!-- Custom style override -->
	<link rel="stylesheet" href="styles/toastr_custom.css">
	<script src="scripts/toastr_settings.js"></script>
</head>
<body>
<div class="container py-4">
    <h3>Edit User</h3>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Company</label>
            <input type="text" name="company" class="form-control" value="<?= htmlspecialchars($user['company']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="User" <?= $user['role'] === 'User' ? 'selected' : '' ?>>User</option>
                <option value="SuperUser" <?= $user['role'] === 'SuperUser' ? 'selected' : '' ?>>SuperUser</option>
                <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="SuperAdmin" <?= $user['role'] === 'SuperAdmin' ? 'selected' : '' ?>>SuperAdmin</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="disabled" <?= $user['status'] === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                <option value="banned" <?= $user['status'] === 'banned' ? 'selected' : '' ?>>Banned</option>
            </select>
        </div>
        <div class="form-check mb-2">
            <input type="checkbox" name="is_superintendent" class="form-check-input" <?= $user['is_superintendent'] ? 'checked' : '' ?>>
            <label class="form-check-label">Is Superintendent</label>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="two_factor_enabled" class="form-check-input" <?= $user['two_factor_enabled'] ? 'checked' : '' ?>>
            <label class="form-check-label">Enable Two-Factor Authentication</label>
        </div>
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="all_users.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include 'toastr_handler.php'; ?>
<?php include 'footer.php'; ?>
<script src="scripts/header.js" defer></script>
</body>
</html>
