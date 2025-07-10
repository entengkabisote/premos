<?php
include 'session_config.php';
require 'db_connect.php';
require_once 'functions.php';
include 'header.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$sql = "SELECT user_id, fullname, username, email, role, status, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users - PREMOS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/buttons.css">
    <link rel="stylesheet" href="styles/all_users.css"> <!-- Optional external styling -->
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
    <h2>All Users</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>
    <a href="add_user.php" class="btn btn-primary mb-3">+ Add New User</a>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['role']; ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="view_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-view">View</a>
                        <a href="edit_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-edit">Edit</a>
                        <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>

                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'toastr_handler.php'; ?>
<?php include 'footer.php'; ?>
<script src="scripts/header.js" defer></script>
</body>
</html>
