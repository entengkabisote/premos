<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

// Get user ID
$user_id = $_SESSION['user_id'];
$role = strtolower($_SESSION['role'] ?? 'user');

// Query vessels
if (in_array($role, ['admin', 'superuser', 'superadmin'])) {
    $sql = "SELECT * FROM vessels ORDER BY vessel_name ASC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT v.* FROM vessels v
            INNER JOIN users_vessels uv ON v.id = uv.vessel_id
            WHERE uv.user_id = ? ORDER BY v.vessel_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vessels | Planned Maintenance System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap, Font Awesome, Toastr -->
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
    <h4 class="mb-4">Vessel Dashboard</h4>
    <div class="d-flex justify-content-between mb-3">
        <a href="dashboard.php" class="btn btn-secondary">🏠 Home</a>
    </div>

    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($v = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= htmlspecialchars($v['imahe']) ?>" class="card-img-top" alt="<?= htmlspecialchars($v['vessel_name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($v['vessel_name']) ?></h5>
                            <p class="card-text">
                                <strong>Type:</strong> <?= htmlspecialchars($v['ship_type']) ?><br>
                                <strong>Home Port:</strong> <?= htmlspecialchars($v['home_port']) ?><br>
                                <strong>Year Built:</strong> <?= htmlspecialchars($v['year_built']) ?><br>
                                <strong>Country:</strong> <?= htmlspecialchars($v['flag']) ?><br>
                                <strong>Owner:</strong> <?= htmlspecialchars($v['owner']) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="vessel_maintenance.php?id=<?= $v['id'] ?>" class="btn btn-primary w-100">
                                <i class="fa-solid fa-ship me-1"></i> View Maintenance
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">No vessels assigned to your account.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<?php include 'toastr_handler.php'; ?>
<script src="scripts/header.js" defer></script>

</body>
</html>
