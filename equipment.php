<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

$sql_types = "SELECT * FROM equipment_type";
$result_types = $conn->query($sql_types);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- Optional: uniform with dashboard.php -->
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Custom style override -->
    <link rel="stylesheet" href="styles/toastr_custom.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <h4 class="mb-4">Equipment Management Dashboard</h4>
        <div class="card p-4 shadow-sm mb-4">
            <form id="addEquipmentForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="equipmentName" class="form-label">Equipment Name</label>
                        <input id="equipmentName" type="text" class="form-control" required <?php if ($_SESSION['role'] === 'SuperUser') echo 'readonly'; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="equipmentCategory" class="form-label">Type</label>
                        <select id="equipmentCategory" class="form-select" required
                            <?php if ($_SESSION['role'] === 'SuperUser') echo 'disabled'; ?>>
                            <option value="" selected>Select Type...</option>
                            <?php while ($type = $result_types->fetch_assoc()) { ?>
                                <option value="<?php echo $type['type']; ?>">
                                    <?php echo $type['type']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                </div>
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-secondary">🏠 Home</a>
                    <?php if ($_SESSION['role'] !== 'SuperUser'): ?>
                        <button type="submit" class="btn btn-primary">📤 Add Equipment</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h5 class="text-center mb-3">List of Equipment</h5>
        <div class="table-responsive mb-5">
            <!-- <table class="table table-bordered table-striped"> -->
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Equipment Name</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="equipmentList">
                    <!-- Equipment will be listed here -->
                </tbody>
            </table>
        </div>
    </div>

   
    <?php include 'footer.php'; ?>
    


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
    <script src="scripts/add_equipment.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'toastr_handler.php'; ?>
    <script src="scripts/header.js" defer></script>
    <script>
        var userRole = "<?php echo $_SESSION['role']; ?>";
    </script>

    
</body>
</html>
