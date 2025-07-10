<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

if (!isset($_GET['meta_id']) || !isset($_GET['equipment_id'])) {
    die('Required parameters are not specified.');
}

$meta_id = $_GET['meta_id'];
$equipment_id = $_GET['equipment_id'];

$sql_meta = "SELECT * FROM inspection_ancillary WHERE meta_id = ?";
$stmt = $conn->prepare($sql_meta);
$stmt->bind_param("i", $meta_id);
$stmt->execute();
$result_meta = $stmt->get_result();
$row_meta = $result_meta->fetch_assoc();

if (!$row_meta) {
    die('No record found for the provided ID.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ancillary Inspection | Planned Maintenance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- optional: for uniform PREMOS look -->
    <!-- <link rel="stylesheet" href="styles/container-fluid.css"> -->
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <script src="scripts/toastr_settings.js"></script>
    <script src="scripts/header.js" defer></script>
</head>
<body>
<?php include 'header.php'; ?>

    <div class="container py-4">
        <h5 class="text-center mb-4">Edit Inspection for Ancillary</h5>
		<main class="edit-inspection-type-container">
            <form action="update_ancillary_inspection_type.php" method="post" class="bg-white p-4 rounded shadow-sm">
                <input type="hidden" name="metaId" value="<?php echo $meta_id; ?>">
                <input type="hidden" name="equipmentId" value="<?php echo $equipment_id; ?>">
                <div class="mb-3">
                    <input type="text" class="form-control" name="inspectionType" id="inspectionType" value="<?php echo htmlspecialchars($row_meta['inspection_type']); ?>" required>
                    <label for="inspectionType">Inspection Type</label>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="inspectionInterval" id="inspectionInterval" value="<?php echo htmlspecialchars($row_meta['inspection_interval']); ?>" required>
                    <label for="inspectionInterval">Inspection Interval</label>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="personInCharge" id="personInCharge" value="<?php echo htmlspecialchars($row_meta['person_in_charge']); ?>" required>
                    <label for="personInCharge">Person In Charge</label>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="criticality" id="criticality" value="<?php echo htmlspecialchars($row_meta['criticality']); ?>" required>
                    <label for="criticality">Criticality</label>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-success px-4" type="submit" name="updateInspectionType">Update Inspection Type</button>
                    <a href="edit_ancillary.php?id=<?php echo $equipment_id; ?>" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </form>
        </main>
    </div>

<?php include 'toastr_handler.php'; ?>
<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
