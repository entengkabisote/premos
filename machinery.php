<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

$query = "SELECT equipment_name.equipment_name_id, equipment_name.equipment_name, equipment_category.category_name 
          FROM equipment_name 
          INNER JOIN equipment_category ON equipment_name.equipment_category_id = equipment_category.equipment_category_id";
$result = $conn->query($query);

$equipments_list = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $equipments_list[] = $row;
    }
}

$type_query = "SELECT category_name FROM equipment_category";
$type_result = $conn->query($type_query);

$equipment_types = [];
if ($type_result && $type_result->num_rows > 0) {
    while($type_row = $type_result->fetch_assoc()) {
        $equipment_types[] = $type_row['category_name']; 
    }
}

// // Map session messages
// if (isset($_SESSION['equipment_added'])) {
//     $_SESSION['toastMessage'] = "Equipment added successfully!";
//     $_SESSION['toastType'] = "success";
//     unset($_SESSION['equipment_added']);
// } elseif (isset($_SESSION['equipment_exists'])) {
//     $_SESSION['toastMessage'] = "Equipment name already exists!";
//     $_SESSION['toastType'] = "error";
//     unset($_SESSION['equipment_exists']);
// } elseif (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
//     $_SESSION['toastMessage'] = $_SESSION['message'];
//     $_SESSION['toastType'] = ($_SESSION['message_type'] === "error") ? "error" : "success";
//     unset($_SESSION['message'], $_SESSION['message_type']);
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machineries | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- optional -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <h4 class="mb-4">Machineries Management Dashboard</h4>
        <div class="card p-4 shadow-sm mb-4">
            <form action="add_equipment_rh.php" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="equipment_name" class="form-label">Equipment Name</label>
                        <input id="equipment_name" name="equipment_name" type="text" class="form-control"
                            required
                            <?php if ($_SESSION['role'] === 'SuperUser') echo 'readonly'; ?>>
                    </div>
                    <div class="col-md-6">
                        <label for="equipment_type" class="form-label">Equipment Type</label>
                        <select id="equipment_type" name="equipment_type" class="form-select"
                            required
                            <?php if ($_SESSION['role'] === 'SuperUser') echo 'disabled'; ?>>
                            <option value="" disabled selected>Select Type...</option>
                            <?php foreach ($equipment_types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
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

        <h5 class="text-center mb-3">Existing Machineries</h5>
        <div class="table-responsive mb-5">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Number</th>
                        <th>Equipment Name</th>
                        <th>Equipment Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; ?>
                    <?php foreach ($equipments_list as $equipment): ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($equipment['equipment_name']) ?></td>
                        <td><?= htmlspecialchars($equipment['category_name']) ?></td>
                        <td>
                            <?php if ($_SESSION['role'] === 'SuperUser'): ?>
                                <a href="edit_equipment_rh.php?id=<?= $equipment['equipment_name_id'] ?>"
                                class="btn btn-sm btn-outline-info"
                                title="View Equipments">
                                    <i class="fa fa-gears"></i> View
                                </a>
                            <?php else: ?>
                                <a href="edit_equipment_rh.php?id=<?= $equipment['equipment_name_id'] ?>"
                                class="btn btn-sm btn-outline-primary"
                                title="Edit Equipments">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="delete_equipment_rh.php?id=<?= $equipment['equipment_name_id'] ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Are you sure you want to delete this?');">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
    <?php include 'toastr_handler.php'; ?>
    <script src="scripts/header.js" defer></script>
    
    
</body>
</html>
