<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include "db_connect.php";

$equipment_name_id = $_GET['equipment_name_id'] ?? '';
$task = [];

// Handle GET task_id
if (isset($_GET['task_id'])) {
    $id = intval($_GET['task_id']);
    $query = "SELECT * FROM machinery_tasks WHERE task_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
    } else {
        die("Task not found with ID: $id");
    }
    $stmt->close();
} else {
    die("ID is required to edit the task.");
}

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_description = htmlspecialchars(trim($_POST['task_description']));
    $threshold_hour = intval($_POST['threshold_hour']);
    $task_id = intval($_POST['task_id']);

    if (empty($task_description)) {
        echo "Task description is required!";
        exit;
    }
    if ($threshold_hour <= 0) {
        echo "Threshold hour should be a positive number!";
        exit;
    }

    // Check for duplicate
    $check_query = $conn->prepare("SELECT * FROM machinery_tasks WHERE task_description = ? AND threshold_hour = ? AND task_id != ?");
    $check_query->bind_param("sii", $task_description, $threshold_hour, $task_id);
    $check_query->execute();
    $check_result = $check_query->get_result();
    if ($check_result->num_rows > 0) {
        $_SESSION['toastMessage'] = "A task with the same description and threshold hour already exists.";
        $_SESSION['toastType'] = "error";
        header("Location: edit_task.php?task_id=" . $task_id . "&equipment_name_id=" . $equipment_name_id);
        exit;
    }

    // Update
    $update_query = $conn->prepare("UPDATE machinery_tasks SET task_description = ?, threshold_hour = ? WHERE task_id = ?");
    $update_query->bind_param("sii", $task_description, $threshold_hour, $task_id);

    if ($update_query->execute()) {
        $_SESSION['toastMessage'] = "Task updated successfully.";
        $_SESSION['toastType'] = "success";
    } else {
        $_SESSION['toastMessage'] = "Error updating task: " . $conn->error;
        $_SESSION['toastType'] = "error";
    }
    $update_query->close();
    header("Location: edit_task.php?task_id=" . $task_id . "&equipment_name_id=" . $equipment_name_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- uniform with equipment.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        .form-label { font-weight: 600; }
        .table-hover tbody tr:hover td { background: #f3f6fa; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container py-4">
    <h4 class="mb-4 text-center">Edit Task</h4>
    <div class="card p-4 shadow-sm mb-4">
        <form action="" method="POST" id="editTaskForm" class="mb-4">
            <input type="hidden" name="equipment_name_id" value="<?php echo $task['equipment_name_id']; ?>">
            <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="task_description" class="form-label">Task Description</label>
                    <input type="text" class="form-control" id="task_description" name="task_description" value="<?php echo htmlspecialchars($task['task_description']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="threshold_hour" class="form-label">Threshold Hour</label>
                    <input type="number" class="form-control" id="threshold_hour" name="threshold_hour" value="<?php echo htmlspecialchars($task['threshold_hour']); ?>" required>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <button class="btn btn-success" type="submit" data-bs-toggle="tooltip" title="Update Task">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
                <a href="edit_component.php?equipment_component_id=<?php echo $task['equipment_component_id']; ?>&equipment_name_id=<?php echo $equipment_name_id; ?>" class="btn btn-secondary" data-bs-toggle="tooltip" title="Back">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </form>
    </div>        
</div>
<?php include 'footer.php'; ?>

<!-- SCRIPTS: match equipment.php structure -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="scripts/header.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'toastr_handler.php'; ?>

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
