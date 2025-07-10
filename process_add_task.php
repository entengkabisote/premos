<?php
include('session_config.php');
include('db_connect.php');

$equipment_component_id = $_POST['equipment_component_id'] ?? '';

// Kunin muna ang equipment_name_id at equipment_category_id
$component_info_query = $conn->prepare("
    SELECT ec.equipment_name_id, en.equipment_category_id 
    FROM equipment_component ec
    JOIN equipment_name en ON ec.equipment_name_id = en.equipment_name_id 
    WHERE ec.equipment_component_id = ?");
$component_info_query->bind_param("i", $equipment_component_id);
$component_info_query->execute();
$component_info_result = $component_info_query->get_result();
if ($component_info_row = $component_info_result->fetch_assoc()) {
    $equipment_name_id = $component_info_row['equipment_name_id'];
    $equipment_category_id = $component_info_row['equipment_category_id'];
} else {
    // Handle error - Component information not found
    $_SESSION['toastMessage'] = "Component information not found.";
    $_SESSION['toastType'] = 'error';
    header("Location: edit_component.php?equipment_component_id=" . $equipment_component_id);
    exit;
}
$component_info_query->close();

// Suriin kung ang form ay naisumite
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_description = $_POST['task_description'] ?? '';
    $threshold_hour = $_POST['threshold_hour'] ?? '';

    // Validation ng input
    if (empty($task_description) || empty($threshold_hour)) {
        // Handle error - missing required fields
        $_SESSION['toastMessage'] = "Required fields are missing.";
        $_SESSION['toastType'] = 'error';
        header("Location: edit_component.php?equipment_component_id=" . $equipment_component_id);
        exit;
    }

    // Check kung mayroon nang kaparehas na task
    $check_query = $conn->prepare("SELECT * FROM machinery_tasks WHERE equipment_component_id = ? AND task_description = ? AND threshold_hour = ?");
    $check_query->bind_param("isi", $equipment_component_id, $task_description, $threshold_hour);
    $check_query->execute();
    $check_result = $check_query->get_result();

    if ($check_result->num_rows > 0) {
        // Mayroong kaparehas na task
        $_SESSION['toastMessage'] = "A task with the same description and threshold hour already exists for this component.";
        $_SESSION['toastType'] = 'error';
    } else {
        // Insert the task into the machinery_tasks table
        $insert_query = $conn->prepare("INSERT INTO machinery_tasks (task_description, equipment_component_id, threshold_hour, equipment_name_id, equipment_category_id) VALUES (?, ?, ?, ?, ?)");
        $insert_query->bind_param("siisi", $task_description, $equipment_component_id, $threshold_hour, $equipment_name_id, $equipment_category_id);

        if ($insert_query->execute()) {
            // Success message
            $_SESSION['toastMessage'] = "Task added successfully.";
            $_SESSION['toastType'] = 'success';
        } else {
            // Error message
            $_SESSION['toastMessage'] = "Error adding task: " . $conn->error;
            $_SESSION['toastType'] = 'error';
        }

        $insert_query->close();
    }
    $check_query->close();
    // Redirect back to the edit_component.php
    header("Location: edit_component.php?equipment_component_id=" . $equipment_component_id . "&equipment_name_id=" . $equipment_name_id);
    exit;
}
?>
