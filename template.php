<?php
include('session_config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include 'db_connect.php';
$equipment_id = $_GET['id'];

$sql_equipment = "SELECT * FROM equipment_table WHERE equipment_id = $equipment_id";
$result_equipment = $conn->query($sql_equipment);
$row_equipment = $result_equipment->fetch_assoc();

$sql_meta = "SELECT * FROM inspection_meta_table WHERE equipment_id = $equipment_id";
$result_meta = $conn->query($sql_meta);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/footer.css">
    <style>
        .toast-container {
            position: fixed;
            top: 50%;
            right: 50%;
            transform: translate(50%, -50%);
            z-index: 1080;
        }
        .toast.bg-danger { background-color: #dc3545 !important; color: #fff; }
        .toast.bg-success { background-color: #198754 !important; color: #fff; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center mb-4">
        <div class="col-12 col-lg-10">
            <h5 class="text-center mb-4">Insert Inspection for Equipment</h5>
            <main>
                <form id="editEquipmentForm" class="mb-4">
                    <div class="row g-3 mb-3">
                        <input type="hidden" id="equipmentId" name="equipmentId" value="<?php echo $equipment_id; ?>">
                        <div class="col-12 col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="equipmentName" value="<?php echo isset($row_equipment['equipment_name']) ? $row_equipment['equipment_name'] : ''; ?>" required>
                                <label for="equipmentName">Equipment Name</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="category" name="category" value="<?php echo isset($row_equipment['category']) ? $row_equipment['category'] : ''; ?>" required>
                                <label for="category">Category</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <!-- <button class="btn waves-effect waves-light btn-space" id="saveEquipment" type="submit">Save</button> -->
                            <button class="btn btn-success me-2" id="saveEquipment" type="submit">Save</button>
                            <a href="equipment.php" class="btn btn-secondary">Back</a>
                        </div>
                    </div>

                    <div id="inspectionMetaContainer" class="mb-3">
                        <div class="row align-items-end gy-2 inspectionRow">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="inspection_type[]" placeholder="Type of Inspection">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="inspection_interval[]">
                                    <option value="" disabled selected>Select Interval...</option>
                                    <?php
                                        $sql = "SELECT * FROM interval_table";
                                        $result = $conn->query($sql);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['name'] . "'>" . $row['name'] . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="person_in_charge[]" placeholder="Person in Charge">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="criticality[]" placeholder="Criticality">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger removeRow d-none">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary me-2" type="button" id="addRow">Add Another Row</button>
                        <button class="btn btn-success me-2" id="saveInspection" type="button">Save Inspect</button>
                    </div>
                    <div id="inspectionMetaContainer2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-secondary">
                                    <tr>
                                        <th style="width: 35%;">Inspection Type</th>
                                        <th style="width: 20%;">Inspection Interval</th>
                                        <th style="width: 15%;">Person In Charge</th>
                                        <th style="width: 15%;">Criticality</th>
                                        <th style="width: 15%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while($row_meta = $result_meta->fetch_assoc()): ?>
                                    <tr data-meta-id="<?php echo $row_meta['meta_id']; ?>" class="inspectionMeta">
                                        <td><?php echo htmlspecialchars($row_meta['inspection_type']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row_meta['inspection_interval']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row_meta['person_in_charge']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row_meta['criticality']); ?></td>
                                        <td>
                                            <a href="edit_inspection_type.php?meta_id=<?php echo $row_meta['meta_id']; ?>&equipment_id=<?php echo $equipment_id; ?>" class="btn btn-primary btn-sm me-1">Edit</a>
                                            <button type="button" class="btn btn-danger btn-sm btn-delete">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-container position-fixed top-50 start-50 translate-middle p-3">
    <div id="mainToast" class="toast bg-danger align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<footer>
    <div class="footer-container">
        <div class="footer-logo">
            SMS
        </div>
        <div class="footer-links">
            <a href="#"><i class="fas fa-info-circle"></i> About</a>
            <a href="#"><i class="fas fa-phone-alt"></i> Contact</a>
            <a href="#"><i class="fas fa-question-circle"></i> Support</a>
        </div>
        <div class="footer-copyright">
            &copy; 2023. All rights reserved.
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script/edit_equipment_script.js"></script>
<script>
    // Toast sample for session messages
    <?php if (isset($_SESSION['success'])): ?>
        showToast('<?php echo $_SESSION['success']; ?>', 'bg-success');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        showToast('<?php echo $_SESSION['error']; ?>', 'bg-danger');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    function showToast(message, cls) {
        var toastBody = document.getElementById('toastBody');
        toastBody.textContent = message;
        var toast = new bootstrap.Toast(document.getElementById('mainToast'));
        document.getElementById('mainToast').className = 'toast align-items-center text-white border-0 ' + cls;
        toast.show();
    }
</script>
</body>
</html>
