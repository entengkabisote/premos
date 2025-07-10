<?php
include('session_config.php');
include("db_connect.php");

$equipment_name_id = $_GET['equipment_name_id'] ?? '';
$equipment_component_id = $_GET['equipment_component_id'] ?? '';

if (isset($_GET['task_id'], $_GET['equipment_component_id'])) {
    $taskId = $_GET['task_id'];
    $equipmentComponentId = $_GET['equipment_component_id'];
    $sql = "DELETE FROM machinery_tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);

    if ($stmt->execute()) {
        // Set a success message
        $_SESSION['toastMessage'] = "Task deleted successfully";
        $_SESSION['toastType'] = "success";
    } else {
        // Set an error message
        $_SESSION['toastMessage'] = "Error: " . $conn->error;
        $_SESSION['toastType'] = "error";
    }

    $stmt->close();
    // header("Location: edit_component.php?equipment_component_id=" . $equipmentComponentId);
    header("Location: edit_component.php?equipment_component_id=" . $equipment_component_id . "&equipment_name_id=" . $equipment_name_id);

} else {
    $_SESSION['toastMessage'] = "Missing task ID or component ID.";
    $_SESSION['toastType'] = "error";
    header("Location: add_component.php?equipment_name_id=" . $equipment_name_id);
    exit;
}

?>
