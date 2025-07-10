<?php
include('session_config.php');
include "db_connect.php";

if (isset($_GET['id']) && isset($_GET['equipment_name_id'])) {
    $id = $_GET['id'];
    $equipment_name_id = $_GET['equipment_name_id'];

    // I-check kung may linked tasks sa machinery_tasks
    $taskQuery = "SELECT * FROM machinery_tasks WHERE equipment_component_id = ?";
    $taskStmt = $conn->prepare($taskQuery);
    $taskStmt->bind_param("i", $id);
    $taskStmt->execute();
    $taskResult = $taskStmt->get_result();
    $hasLinkedTasks = $taskResult->num_rows > 0;
    $taskStmt->close();

    if ($hasLinkedTasks) {
        header("Location: confirm_delete.php?id=" . $id . "&equipment_name_id=" . $equipment_name_id . "&hasLinkedTasks=1");
        exit;
    } else {
        $deleteSql = "DELETE FROM equipment_component WHERE equipment_component_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $id);
        if ($deleteStmt->execute()) {
            $_SESSION['toastMessage'] = "Component deleted successfully!";
            $_SESSION['toastType'] = "success";
        } else {
            $_SESSION['toastMessage'] = "Error deleting component: " . $conn->error;
            $_SESSION['toastType'] = "error";
        }
        $deleteStmt->close();
        header("Location: add_component.php?equipment_name_id=" . $equipment_name_id);
        exit;
    }
}
?>
