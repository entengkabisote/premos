    <?php
    include('session_config.php');
    include 'db_connect.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }

    // Initialize variables for component name, component ID, and equipment name ID
    $componentName = "";
    $component_id = isset($_GET['component_id']) ? $_GET['component_id'] : null;
    $equipment_name_id = isset($_GET['equipment_name_id']) ? $_GET['equipment_name_id'] : null;

    if ($component_id && $equipment_name_id) {
        // Fetch the component name for display
        $componentNameStmt = $conn->prepare("SELECT component_name FROM equipment_component WHERE equipment_component_id = ?");
        if ($componentNameStmt) {
            $componentNameStmt->bind_param("i", $component_id);
            $componentNameStmt->execute();
            $componentNameResult = $componentNameStmt->get_result();
            $componentData = $componentNameResult->fetch_assoc();
            $componentNameStmt->close();
            
            if ($componentData) {
                $componentName = $componentData['component_name'];
            } else {
                echo "Component with ID " . htmlspecialchars($component_id) . " not found.";
                exit;
            }
        } else {
            echo "Error fetching component name: " . $conn->error;
            exit;
        }

        // Fetch spare parts associated with this component
        $componentPartsStmt = $conn->prepare("
            SELECT sp.id, sp.part_name, sp.location, csp.quantity 
            FROM component_spare_parts csp 
            JOIN spare_parts sp ON csp.spare_part_id = sp.id 
            WHERE csp.component_id = ?
        ");
        if ($componentPartsStmt) {
            $componentPartsStmt->bind_param("i", $component_id);
            $componentPartsStmt->execute();
            $componentSpareParts = $componentPartsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $componentPartsStmt->close();
        } else {
            echo "Error fetching spare parts: " . $conn->error;
            $componentSpareParts = [];
        }
    } else {
        echo "Component ID or Equipment ID not provided.";
        exit;
    }
    ?>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Spare Parts for Component</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .table-column-action { width: 20%; }
        @media (max-width: 600px) {
            .table-responsive { font-size: 0.96em; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Bootstrap Toast Container -->
    <div id="toast-container-bootstrap"></div>

    <div class="container-fluid py-4">
		<div class="row justify-content-center mb-4">
			<div class="col-12 col-lg-10">
            <h4 class="mb-4">Add New Spare Part for Component: <span class="text-primary"><?php echo htmlspecialchars($componentName); ?></span></h4>
            
            <a href="add_component.php?equipment_name_id=<?php echo htmlspecialchars($equipment_name_id); ?>" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            
            <form action="process_add_spare_part.php" method="POST" class="bg-white p-4 rounded shadow-sm mb-4">
                <input type="hidden" name="component_id" value="<?php echo htmlspecialchars($component_id); ?>">
                <input type="hidden" name="equipment_name_id" value="<?php echo htmlspecialchars($equipment_name_id); ?>">

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="part_name" class="form-label">Spare Part Name:</label>
                        <input type="text" name="part_name" id="part_name" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="location" class="form-label">Location:</label>
                        <input type="text" name="location" id="location" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" min="1" class="form-control" required>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Add Spare Part
                    </button>
                </div>
            </form>
            
            <h5 class="mb-3">Current Spare Parts for This Component</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Part Name</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th class="table-column-action">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($componentSpareParts as $componentPart): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($componentPart['part_name']); ?></td>
                                <td><?php echo htmlspecialchars($componentPart['location']); ?></td>
                                <td><?php echo htmlspecialchars($componentPart['quantity']); ?></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="edit_spare_part.php?id=<?php echo $componentPart['id']; ?>&component_id=<?php echo $component_id; ?>&equipment_name_id=<?php echo $equipment_name_id; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete_spare_part.php?id=<?php echo $componentPart['id']; ?>&component_id=<?php echo $component_id; ?>&equipment_name_id=<?php echo $equipment_name_id; ?>"
                                            onclick="return confirm('Are you sure you want to delete this spare part?');"
                                            class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($componentSpareParts)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No spare parts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php
    $toast_message = '';
    $toast_type = '';
    $toast_icon = '';
    if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
        $toast_message = addslashes($_SESSION['message']);
        $toast_type = ($_SESSION['message_type'] === 'error') ? 'danger' : 'success';
        $toast_icon = ($_SESSION['message_type'] === 'error') ? 'bi-x-circle-fill' : 'bi-check-circle-fill';
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
?>
<?php if (!empty($toast_message)): ?>
window.addEventListener('DOMContentLoaded', function() {
    var toastContainer = document.getElementById('toast-container-bootstrap');
    var toastWrapper = document.createElement('div');
    toastWrapper.innerHTML = `
        <div class="toast align-items-center text-bg-<?php echo $toast_type; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi <?php echo $toast_icon; ?>"></i>
                    <?php echo $toast_message; ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    var toastEl = toastWrapper.firstElementChild;
    toastContainer.appendChild(toastEl);
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
});
<?php endif; ?>
</script>

</body>
</html>
