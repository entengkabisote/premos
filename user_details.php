<?php
include('session_config.php');
include 'db_connect.php';

$toastMessage = $_SESSION['toastMessage'] ?? '';
$toastType = $_SESSION['toastType'] ?? '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $fullname = htmlspecialchars($user['fullname']);
        $username = htmlspecialchars($user['username']);
        $email = htmlspecialchars($user['email']);
        $role = htmlspecialchars($user['role']);
        $company = htmlspecialchars($user['company']);
        $profile_picture = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default_profile.png';
        $profile_picture_path = 'uploads/profile_pictures/' . $profile_picture;
    } else {
        $error_message = "User not found.";
    }
    $roles = ['superadmin', 'admin', 'superuser', 'user'];
} else {
    $error_message = "Invalid user ID.";
}

$assigned_vessel_name = '';
if ($user && $role === 'user') {
    $stmtVessel = $conn->prepare("SELECT v.vessel_name FROM users_vessels uv JOIN vessels v ON uv.vessel_id = v.id WHERE uv.user_id = ? LIMIT 1");
    $stmtVessel->bind_param("i", $user_id);
    $stmtVessel->execute();
    $resVess = $stmtVessel->get_result();
    if ($rowVessel = $resVess->fetch_assoc()) {
        $assigned_vessel_name = $rowVessel['vessel_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details | Planned Maintenance System</title>
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
    <h4 class="mb-4">User Details</h4>
    <div class="card p-4 shadow-sm mb-4">
        <form method="POST" action="update_user.php" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= $user_id ?? ''; ?>">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" id="fullname" name="fullname" value="<?= $fullname ?? ''; ?>" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" value="<?= $username ?? ''; ?>" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="<?= $email ?? ''; ?>" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="company" class="form-label">Company</label>
                    <input type="text" id="company" name="company" value="<?= $company ?? ''; ?>" class="form-control" required>
                </div>
            </div>

            <?php if ($_SESSION['role'] === 'SuperAdmin'): ?>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select">
                    <?php foreach ($roles as $role_option): ?>
                        <option value="<?= $role_option; ?>" <?= $role == $role_option ? 'selected' : ''; ?>>
                            <?= ucfirst($role_option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="role" value="<?= $role ?>">
            <?php endif; ?>

            <?php if ($role === 'user'): ?>
            <div class="mb-3">
                <label class="form-label">Assigned Vessel</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($assigned_vessel_name ?: 'N/A'); ?>" readonly>
            </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="profile_picture" class="form-label">Profile Picture</label>
                    <input class="form-control" type="file" name="profile_picture" id="profile_picture" accept="image/*">
                </div>
                <div class="col-md-6 text-center">
                    <img src="<?= htmlspecialchars($profile_picture_path); ?>" alt="Profile Picture" class="rounded-circle border mt-3" width="120" height="120" style="object-fit:cover;">
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_superintendent" id="is_superintendent"
                    <?= (!empty($user['is_superintendent']) && $user['is_superintendent'] == 1) ? 'checked' : ''; ?>
                    <?= !in_array($_SESSION['role'], ['Admin', 'SuperAdmin']) ? 'disabled' : ''; ?>>
                <label class="form-check-label" for="is_superintendent">Is Superintendent</label>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="two_factor_enabled" id="two_factor_enabled"
                    <?= (!empty($user['two_factor_enabled']) && $user['two_factor_enabled'] == 1) ? 'checked' : ''; ?>
                    <?= !in_array($_SESSION['role'], ['Admin', 'SuperAdmin']) ? 'disabled' : ''; ?>>
                <label class="form-check-label" for="two_factor_enabled">Enable Two-Factor Authentication</label>
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?= $_SESSION['role'] === 'SuperAdmin' ? 'user_management.php' : 'dashboard.php' ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts/header.js" defer></script>

<?php include 'toastr_handler.php'; ?>
</body>
</html>
