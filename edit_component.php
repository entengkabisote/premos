<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$isSuperUser = (isset($_SESSION['role']) && $_SESSION['role'] === 'SuperUser');

include('db_connect.php');

$equipment_component_id = $_GET['equipment_component_id'] ?? '';
$equipment_name_id = $_GET['equipment_name_id'] ?? '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $component_id = $_POST['equipment_component_id'];
    $component_name = $_POST['component_name'];
    $description = $_POST['description'];

    $update_query = $conn->prepare("UPDATE equipment_component SET component_name = ?, component_description = ? WHERE equipment_component_id = ?");
    $update_query->bind_param("ssi", $component_name, $description, $component_id);

    if ($update_query->execute()) {
        $_SESSION['toastMessage'] = "Component updated successfully";
        $_SESSION['toastType'] = "success";
    } else {
        $_SESSION['toastMessage'] = "Error updating component";
        $_SESSION['toastType'] = "error";
    }
    $update_query->close();
    header("Location: edit_component.php?equipment_component_id=$component_id&equipment_name_id=$equipment_name_id");
    exit;
}


// Fetch component details
$component = [];
if ($equipment_component_id) {
    $query = $conn->prepare("SELECT * FROM equipment_component WHERE equipment_component_id = ?");
    $query->bind_param("i", $equipment_component_id);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $component = $result->fetch_assoc();
    }
    $query->close();
}

// Query tasks
$tasks_query = $conn->prepare("
    SELECT mt.*, en.equipment_name 
    FROM machinery_tasks mt
    JOIN equipment_component ec ON mt.equipment_component_id = ec.equipment_component_id
    JOIN equipment_name en ON ec.equipment_name_id = en.equipment_name_id
    WHERE mt.equipment_component_id = ?
");
$tasks_query->bind_param("i", $equipment_component_id);
$tasks_query->execute();
$tasks_result = $tasks_query->get_result();
$tasks = [];
$task_number = 1;
while ($task_row = $tasks_result->fetch_assoc()) {
    $task_row['task_number'] = $task_number++;
    $tasks[] = $task_row;
}
$tasks_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Component | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- uniform with equipment.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        .action-btns .btn {
            width: 36px; height: 36px; padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .action-btns { gap: 10px !important; }
        @media (max-width: 900px) {
            .action-btns { flex-direction: column !important; align-items: center !important; }
        }
        .table-hover tbody tr:hover td { background: #f3f6fa; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
    <?php if (!$isSuperUser): ?>    
        <h4 class="mb-4 text-center">Edit Component: <?php echo htmlspecialchars($component['component_name'] ?? 'Unknown'); ?></h4>
        <div class="card p-4 shadow-sm mb-4">
            <form action="" method="POST" id="editComponentForm">
                <input type="hidden" name="equipment_component_id" value="<?php echo $equipment_component_id; ?>">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="component_name" class="form-label">Component Name</label>
                        <input type="text" id="component_name" name="component_name" class="form-control" value="<?php echo htmlspecialchars($component['component_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="1" required><?php echo htmlspecialchars($component['component_description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <button type="submit" class="btn btn-success" data-bs-toggle="tooltip" title="Update">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                    <a href="add_component.php?equipment_name_id=<?php echo $equipment_name_id; ?>" class="btn btn-danger" data-bs-toggle="tooltip" title="Cancel">
                        <i class="fa-solid fa-xmark-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!$isSuperUser): ?>
        <h5 class="mb-3">Add Task to Component</h5>
        <div class="card p-4 shadow-sm mb-4">
            <form action="process_add_task.php" method="POST" id="addTaskForm">
                <input type="hidden" name="equipment_component_id" value="<?php echo $equipment_component_id; ?>">
                <input type="hidden" name="equipment_name_id" value="<?php echo $equipment_name_id; ?>">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="task_description" class="form-label">Task Description</label>
                        <textarea id="task_description" name="task_description" class="form-control" rows="1" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="threshold_hour" class="form-label">Threshold Hour</label>
                        <input type="number" id="threshold_hour" name="threshold_hour" class="form-control" required>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="Add Task">
                        <i class="fa-solid fa-circle-plus"></i>
                    </button>
                    <a href="add_component.php?equipment_name_id=<?php echo $equipment_name_id; ?>" class="btn btn-secondary" data-bs-toggle="tooltip" title="Back">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>


    <h5 class="text-center mb-3">Tasks for Component</h5>
    <?php if ($isSuperUser): ?>
        <div class="mb-3">
            <a href="add_component.php?equipment_name_id=<?= $equipment_name_id ?>" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    <?php endif; ?>

    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No.</th>
                    <th>Task Description</th>
                    <th>Threshold Hour</th>
                    <th>Equipment Name</th>
                    <?php if (!$isSuperUser): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['task_number']); ?></td>
                    <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                    <td><?php echo htmlspecialchars($task['threshold_hour']); ?></td>
                    <td><?php echo htmlspecialchars($task['equipment_name']); ?></td>
                    <?php if (!$isSuperUser): ?>
                    <td>
                        <div class="d-flex action-btns gap-2 justify-content-center align-items-center">
                            <a href="edit_task.php?task_id=<?php echo $task['task_id']; ?>&equipment_name_id=<?php echo $equipment_name_id; ?>"
                            class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip"
                            title="Edit Task">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="delete_task.php?task_id=<?php echo $task['task_id']; ?>&equipment_component_id=<?php echo $equipment_component_id; ?>&equipment_name_id=<?php echo $equipment_name_id; ?>"
                            class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="tooltip"
                            title="Delete Task"
                            onclick="return confirm('Are you sure you want to delete this task?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tasks)): ?>
                <tr>
                    <td colspan="<?php echo $isSuperUser ? '4' : '5'; ?>" class="text-center text-muted">No tasks found.</td>
                </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- JS dependencies, gaya ng equipment.php -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Toastr handler (if any), gaya ng equipment.php -->
<?php include 'toastr_handler.php'; ?>
<script src="scripts/header.js" defer></script>

<script>
// Enable Bootstrap tooltips (icons)
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>
</body>
</html>
