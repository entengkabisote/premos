<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include('db_connect.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$equipment_name_id = $_GET['equipment_name_id'] ?? '';
$componentAddedSuccessfully = false;

// Retrieve components associated with the specific equipment name
$components_list = [];
if ($equipment_name_id) {
    $query = "SELECT * FROM equipment_component WHERE equipment_name_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $equipment_name_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $components_list[] = $row;
    }
    $stmt->close();
}

$equipment_name = '';
if ($equipment_name_id) {
    $equipment_query = $conn->prepare("SELECT equipment_name FROM equipment_name WHERE equipment_name_id = ?");
    $equipment_query->bind_param("i", $equipment_name_id);
    $equipment_query->execute();
    $equipment_result = $equipment_query->get_result();
    if ($row = $equipment_result->fetch_assoc()) {
        $equipment_name = $row['equipment_name'];
    }
    $equipment_query->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Component</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        td .btn.btn-sm i {
            font-size: 1.1rem;
        }
        td .btn.btn-sm {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .d-flex.gap-2 {
            gap: 10px !important;
        }

        /* Responsive stacking ng action buttons */
        @media (max-width: 900px) {
            td .d-flex.flex-md-row {
                flex-direction: column !important;
                gap: 6px !important;
                align-items: stretch !important;
            }
            td .d-flex.flex-md-row a.btn {
                width: 100%;
                justify-content: left;
            }
        }
        td .d-flex.flex-md-row a.btn {
            min-width: 98px;
            /* Optional: para di lumiit ang button kahit maiksi label */
        }
       
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <!-- <div id="toast-container-bootstrap"></div> -->

    <div class="container py-4">
        <?php if ($_SESSION['role'] !== 'SuperUser'): ?>
            <h4 class="mb-4 text-center">Add Component to <?php echo htmlspecialchars($equipment_name); ?></h4>
            <div class="card p-4 shadow-sm mb-4">
                <form action="process_add_component.php?equipment_name_id=<?php echo $equipment_name_id; ?>" method="POST" class="mb-3">
                    <input type="hidden" name="equipment_name_id" value="<?php echo $equipment_name_id; ?>">
                    <div class="mb-3">
                        <label for="component_name" class="form-label">Component Name</label>
                        <input type="text" id="component_name" name="component_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i>Add Component
                        </button>
                        <a href="edit_equipment_rh.php?id=<?php echo $equipment_name_id; ?>" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <h5 class="text-center mb-3">Existing Components</h5>
        <?php if ($_SESSION['role'] === 'SuperUser'): ?>
            <div class="mb-3">
                <a href="machinery.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        <?php endif; ?>

        <!-- <div class="card p-4 shadow-sm"> -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="table-column-number">#</th>
                            <th class="table-column-name">Component Name</th>
                            <th class="table-column-description">Description</th>
                            <th class="table-column-action">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($components_list as $component): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($component['component_name']); ?></td>
                                <td><?php echo htmlspecialchars($component['component_description']); ?></td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center align-items-center">
                                        <?php if ($_SESSION['role'] === 'SuperUser'): ?>
                                            <a href="edit_component.php?equipment_component_id=<?= $component['equipment_component_id'] ?>&equipment_name_id=<?= $equipment_name_id ?>"
                                                class="btn btn-sm btn-outline-info p-2"
                                                data-bs-toggle="tooltip"
                                                title="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="edit_component.php?equipment_component_id=<?= $component['equipment_component_id'] ?>&equipment_name_id=<?= $equipment_name_id ?>"
                                                class="btn btn-sm btn-outline-primary p-2"
                                                data-bs-toggle="tooltip"
                                                title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="delete_component.php?id=<?= $component['equipment_component_id'] ?>&equipment_name_id=<?= $equipment_name_id ?>"
                                                class="btn btn-sm btn-outline-danger p-2"
                                                data-bs-toggle="tooltip"
                                                title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this component?');">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                        <!-- <a href="spareparts.php?component_id=<?= $component['equipment_component_id'] ?>&equipment_name_id=<?= $equipment_name_id ?>"
                                            class="btn btn-sm btn-outline-success p-2"
                                            data-bs-toggle="tooltip"
                                            title="Spare Parts">
                                            <i class="fa-solid fa-gears"></i>
                                        </a> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($components_list)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No components found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <!-- </div> -->
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'toastr_handler.php'; ?>
    <script src="scripts/header.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>

</body>
</html>
