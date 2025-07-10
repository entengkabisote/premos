<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!in_array(strtolower($_SESSION['role']), ['admin', 'superadmin'])) {
    $_SESSION['toastMessage'] = "Access denied.";
    $_SESSION['toastType'] = "error";
    header('Location: index.php');
    exit;
}

include 'db_connect.php';

if (isset($_POST['delete_user_id'])) {
    $delete_id = intval($_POST['delete_user_id']);
    if ($delete_id != $_SESSION['user_id']) {
        $getrole = mysqli_query($conn, "SELECT role FROM users WHERE user_id = $delete_id");
        $role_row = mysqli_fetch_assoc($getrole);
        if ($role_row && strtolower($role_row['role']) != 'admin') {
            mysqli_query($conn, "DELETE FROM users_vessels WHERE user_id = $delete_id");
            mysqli_query($conn, "DELETE FROM users WHERE user_id = $delete_id");
            $_SESSION['toastMessage'] = "User deleted successfully!";
            $_SESSION['toastType'] = "success";
        } else {
            $_SESSION['toastMessage'] = "Cannot delete admin accounts.";
            $_SESSION['toastType'] = "error";
        }
    } else {
        $_SESSION['toastMessage'] = "You cannot delete your own account.";
        $_SESSION['toastType'] = "error";
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
} else {
    $successMessage = '';
}

$user_select_query = "SELECT user_id, username FROM users WHERE role = 'User'";
$user_select_result = mysqli_query($conn, $user_select_query);
$users = mysqli_fetch_all($user_select_result, MYSQLI_ASSOC);

$vessel_query = "SELECT id, vessel_name FROM vessels";
$vessel_result = mysqli_query($conn, $vessel_query);
$vessels = mysqli_fetch_all($vessel_result, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_user_id'])) {
    $user_id = $_POST['user_id'];
    $vessel_id = $_POST['vessel_id'] ?? null;

    $check_query = "SELECT * FROM users_vessels WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($check_result) > 0) {
        $update_query = "UPDATE users_vessels SET vessel_id = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ii", $vessel_id, $user_id);
        mysqli_stmt_execute($stmt);
    } else {
        $insert_query = "INSERT INTO users_vessels (user_id, vessel_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $vessel_id);
        mysqli_stmt_execute($stmt);
    }

    $_SESSION['successMessage'] = "Vessel assignment updated successfully!";
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$query = "
    SELECT 
        u.*, 
        v.vessel_name 
    FROM users u 
    LEFT JOIN users_vessels uv ON u.user_id = uv.user_id 
    LEFT JOIN vessels v ON uv.vessel_id = v.id
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Planned Maintenance System</title>
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
    <h4 class="mb-4">User Management</h4>

    <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                🏠 Home
            </a>
            <h4 class="mb-0">User Management</h4>
            <a href="add_user.php" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus"></i> Add User
            </a>
        </div>

        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Fullname</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Assigned Vessel</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['fullname']) ?></td>
                        <td><a href="user_details.php?id=<?= $user['user_id'] ?>"><?= htmlspecialchars($user['username']) ?></a></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= strtolower($user['role']) === 'user' ? htmlspecialchars($user['vessel_name'] ?? 'Not Assigned') : '-' ?></td>
                        <td>
                            <?php if ($user['user_id'] != $_SESSION['user_id'] && strtolower($user['role']) != 'admin'): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="delete_user_id" value="<?= $user['user_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php elseif ($user['user_id'] == $_SESSION['user_id']): ?>
                                <span class="text-secondary small">(You)</span>
                            <?php else: ?>
                                <span class="text-secondary small">Protected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card p-4 shadow-sm">
        <h5>Assign Vessel to User</h5>
        <form method="POST">
            <div class="mb-3">
                <label for="user-select" class="form-label">User</label>
                <select class="form-select" id="user-select" name="user_id" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="vessel-select" class="form-label">Vessel</label>
                <select class="form-select" id="vessel-select" name="vessel_id" required>
                    <option value="">Select Vessel</option>
                    <?php foreach ($vessels as $vessel): ?>
                        <option value="<?= $vessel['id'] ?>"><?= htmlspecialchars($vessel['vessel_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Assign</button>
        </form>
        <?php if ($successMessage): ?>
            <div class="alert alert-success mt-3"><?= $successMessage ?></div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'toastr_handler.php'; ?>
<script src="scripts/header.js" defer></script>
</body>
</html>
