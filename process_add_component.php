<?php
include('session_config.php');
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $component_name = $_POST['component_name'];
    $description = $_POST['description'];
    $equipment_name_id = $_POST['equipment_name_id'];

    // Suriin kung mayroon nang component na may parehong pangalan sa ilalim ng parehong equipment_name_id
    $check_query = $conn->prepare("SELECT * FROM equipment_component WHERE component_name = ? AND equipment_name_id = ?");
    $check_query->bind_param("si", $component_name, $equipment_name_id);
    $check_query->execute();
    $result = $check_query->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['toastMessage'] = "Component name already exists under this equipment!";
        $_SESSION['toastType'] = "error";
    } else {
        // Wala pang existing na component, mag-insert ng bagong component
        $stmt = $conn->prepare("INSERT INTO equipment_component (component_name, component_description, equipment_name_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $component_name, $description, $equipment_name_id);
        
        if ($stmt->execute()) {
            $_SESSION['toastMessage'] = "Component added successfully!";
            $_SESSION['toastType'] = "success";
        } else {
            $_SESSION['toastMessage'] = "Error: " . $conn->error;
            $_SESSION['toastType'] = "error";
        }
        $stmt->close();
    }

    $check_query->close();
    
    // Redirect back to the add component page
    // header("Location: add_component.php?id=".$equipment_name_id);
    header("Location: add_component.php?equipment_name_id=" . $equipment_name_id);
    exit;
}
