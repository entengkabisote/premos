<?php
include('session_config.php');

// var_dump($_GET, $_POST, $_SERVER['REQUEST_METHOD']);

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

$equipment_id = $_GET['id'];
$sql_equipment = "SELECT * FROM ancillary_table WHERE equipment_id = $equipment_id";
$result_equipment = $conn->query($sql_equipment);
$row_equipment = $result_equipment->fetch_assoc();

$sql_meta = "SELECT * FROM inspection_ancillary WHERE equipment_id = $equipment_id";
$result_meta = $conn->query($sql_meta);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machinery | Planned Maintenance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- for uniform PREMOS look -->
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    
    
    <!-- <style>
        .toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 1080;
        }
    </style> -->
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
    <h5 class="text-center mb-4">Insert Inspection for Machinery</h5>
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
                <button class="btn btn-success me-2" id="saveEquipment" type="submit">Save</button>
                <a href="ancillary.php" class="btn btn-secondary">Back</a>
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
                            $sql = "SELECT * FROM interval_ancillary_table";
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
                <!-- <table class="table table-bordered table-striped align-middle"> -->
                <table class="table table-bordered table-striped table-hover align-middle">

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
                                <a href="edit_ancillary_inspection_type.php?meta_id=<?php echo $row_meta['meta_id']; ?>&equipment_id=<?php echo $equipment_id; ?>" class="btn btn-primary btn-sm me-1">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm btn-delete">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/edit_ancillary_script.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="scripts/header.js" defer></script>
<?php include 'toastr_handler.php'; ?>

<!-- <script>

$(document).ready(function() {
    attachEventListeners();

    $("#addRow").click(function() {
        // Only clone the first .inspectionRow (template), not all rows
        var originalRow = $(".inspectionRow").first();
        var newRow = originalRow.clone();

        // Clean up the cloned row
        newRow.find("input").val("");
        newRow.find("select").val('');
        newRow.find(".removeRow").removeClass('d-none').show();

        // Append after the last .inspectionRow
        $("#inspectionMetaContainer").append(newRow);
    });

    $(document).on("click", ".removeRow", function() {
        if ($(".inspectionRow").length > 1) {
            $(this).closest(".inspectionRow").remove();
        }
    });

    $("#saveInspection").click(function() {
        let inspectionData = [];
        let uniqueTypes = new Set();
        let hasDuplicate = false;

        $(".inspectionRow").each(function() {
            let type = $.trim($(this).find("input[name='inspection_type[]']").val());
            let interval = $(this).find("select[name='inspection_interval[]']").val();
            let person = $(this).find("input[name='person_in_charge[]']").val();
            let criticality = $(this).find("input[name='criticality[]']").val();

            if (type !== "") {
                inspectionData.push({type: type, interval, person, criticality});
            }
            if (uniqueTypes.has(type)) {
                // showToast("Duplicate type of inspection: " + type, 'bg-danger');
                hasDuplicate = true;toastr.error("Duplicate type of inspection: " + type);

                return false;
            } else {
                uniqueTypes.add(type);
            }
        });

        let equipmentId = $("#equipmentId").val();
        if (!equipmentId || isNaN(equipmentId)) {
            // showToast("Invalid equipment ID.", 'bg-danger');
            toastr.error("Invalid equipment ID.");
            return;
        }
        if (inspectionData.length === 0) {
            // showToast("No valid inspection data provided.", 'bg-danger');
            toastr.warning("No valid inspection data provided.");
            return;
        }

        if (!hasDuplicate) {
            $.ajax({
                url: "save_ancillary_inspection_meta.php",
                type: "POST",
                data: {
                    equipmentId: equipmentId,
                    inspection_data: JSON.stringify(inspectionData)
                },
                success: function(response) {
                    const parsedResponse = JSON.parse(response);
                    if (parsedResponse.status === 'success') {
                        // showToast("Inspection data successfully saved!", 'bg-success');
                        toastr.success("Inspection data successfully saved!");
                        displayInspectionTypes(parsedResponse.data);
                    } else if (parsedResponse.status === 'duplicate') {
                        // showToast("Duplicate inspection types detected.", 'bg-danger')
                        toastr.error("Duplicate inspection types detected.");
                    } else {
                        // showToast("Failed to save inspection data.", 'bg-danger');
                        toastr.error("Failed to save inspection data.");
                    }
                    $(".inspectionRow input[type='text']").val('');
                    $(".inspectionRow select").val('');
                },
                error: function(xhr, status, error) {
                    // showToast("Failed to save inspection data.", 'bg-danger');
                    toastr.error("Failed to save inspection data.");
                }
            });
        }
    });
});

function displayInspectionTypes(inspectionTypes) {
    const tableBody = $('#inspectionMetaContainer2 table tbody');
    tableBody.empty();
    inspectionTypes.forEach(type => {
        const row = `
            <tr data-meta-id="${type.meta_id}">
                <td>${type.inspection_type}</td>
                <td>${type.inspection_interval}</td>
                <td>${type.person_in_charge}</td>
                <td>${type.criticality}</td>
                <td>
                    <a href="edit_ancillary_inspection_type.php?meta_id=${type.meta_id}&equipment_id=${type.equipment_id}" class="btn btn-primary btn-sm me-1">Edit</a>
                    <button type="button" class="btn btn-danger btn-sm btn-delete">Delete</button>
                </td>
            </tr>
        `;
        tableBody.append(row);
    });
    attachEventListeners();
}

function attachEventListeners() {
    $(document).off('click', '.btn-delete');
    $(document).on('click', '.btn-delete', function() {
        var row = $(this).closest('tr');
        var metaId = row.data('meta-id');
        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                url: "delete_ancillary_meta.php",
                type: "POST",
                data: { meta_id: metaId },
                dataType: "json",
                success: function(response) {
                    if(response.status === "success") {
                        // showToast(response.message, 'bg-success');
                        toastr.success(response.message);
                        row.remove();
                    } else {
                        // showToast("Error: " + response.message, 'bg-danger');
                        toastr.error("Error: " + response.message);
                    }
                },
                error: function() {
                    // showToast("Something went wrong with the request.", 'bg-danger');
                    toastr.error("Something went wrong with the request.");
                }
            });
        }
    });
}
</script> -->
</body>
</html>
