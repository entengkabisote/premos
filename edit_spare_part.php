<?php
include('session_config.php');
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['component_id']) && isset($_GET['equipment_name_id'])) {
    $spare_part_id = $_GET['id'];
    $component_id = $_GET['component_id'];
    $equipment_name_id = $_GET['equipment_name_id'];

    // Fetch spare part details (note: should be part_name not name)
    $stmt = $conn->prepare("SELECT sp.part_name, sp.location, csp.quantity 
                            FROM component_spare_parts csp 
                            JOIN spare_parts sp ON csp.spare_part_id = sp.id 
                            WHERE csp.spare_part_id = ? AND csp.component_id = ?");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $spare_part_id, $component_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sparePart = $result->fetch_assoc();
    $stmt->close();

    if (!$sparePart) {
        echo "Spare part not found.";
        exit;
    }

    // Update spare part details if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $part_name = $_POST['part_name'];
        $location = $_POST['location'];
        $quantity = $_POST['quantity'];

        // Update spare_parts table (name, location, quantity)
        $updateStmt1 = $conn->prepare("UPDATE spare_parts SET part_name = ?, location = ?, quantity = ? WHERE id = ?");
        $updateStmt1->bind_param("ssii", $part_name, $location, $quantity, $spare_part_id);

        // Update component_spare_parts table (quantity)
        $updateStmt2 = $conn->prepare("UPDATE component_spare_parts SET quantity = ? WHERE spare_part_id = ? AND component_id = ?");
        $updateStmt2->bind_param("iii", $quantity, $spare_part_id, $component_id);

        // Execute both updates
        if ($updateStmt1->execute() && $updateStmt2->execute()) {
            $_SESSION['message'] = "Spare part updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: spareparts.php?component_id=$component_id&equipment_name_id=$equipment_name_id");
            exit;
        } else {
            $_SESSION['message'] = "Error updating spare part: " . $conn->error;
            $_SESSION['message_type'] = "error";
            header("Location: spareparts.php?component_id=$component_id&equipment_name_id=$equipment_name_id");
            exit;
        }

        $updateStmt1->close();
        $updateStmt2->close();
    }
} else {
    echo "Required parameters not provided.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Spare Part</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/styles.css">   
    <link rel="stylesheet" href="styles/footer.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- Bootstrap Toast Container -->
    <div id="toast-container-bootstrap"></div>

    <div class="container-fluid py-4">
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-lg-10">
                <h4 class="mb-4">Edit Spare Part</h4>
                <?php if (isset($errorMsg)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                <?php endif; ?>
                <form method="POST" class="bg-white p-4 rounded shadow-sm">
                    <div class="mb-3">
                        <label for="part_name" class="form-label">Spare Part Name</label>
                        <input type="text" name="part_name" id="part_name" class="form-control" value="<?= htmlspecialchars($sparePart['part_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" name="location" id="location" class="form-control" value="<?= htmlspecialchars($sparePart['location']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" min="1" class="form-control" value="<?= htmlspecialchars($sparePart['quantity']) ?>" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                        <a href="spareparts.php?component_id=<?= htmlspecialchars($component_id) ?>&equipment_name_id=<?= htmlspecialchars($equipment_name_id) ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
