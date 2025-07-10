<?php
include('session_config.php');

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or display the error message
    header('Location: index.php'); // Redirect to login page
    exit; // Stop further script execution
    // die("You need to login.");
}
include 'db_connect.php';

// Make sure POST is the method used for form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['metaId'])) {
    // Get the equipment_id from the hidden field in the form
    $equipment_id = isset($_POST['equipmentId']) ? (int)$_POST['equipmentId'] : null;
    $meta_id = (int)$_POST['metaId'];
    $inspection_type = $_POST['inspectionType'];
    $inspection_interval = $_POST['inspectionInterval'];
    $person_in_charge = $_POST['personInCharge'];
    $criticality = $_POST['criticality'];

    // Prepare the SQL statement to update the data
    $update_sql = "UPDATE inspection_ancillary SET 
                   inspection_type = ?, 
                   inspection_interval = ?, 
                   person_in_charge = ?, 
                   criticality = ? 
                   WHERE meta_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $inspection_type, $inspection_interval, $person_in_charge, $criticality, $meta_id);

    // Check if the update is successful
    if ($stmt->execute()) {
        $_SESSION['success'] = "Inspection type updated successfully.";
    } else {
        $_SESSION['error'] = "Unable to update inspection type. Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

    // Redirect back to edit_equipment.php with the equipment_id
    header("Location: edit_ancillary.php?id=$equipment_id");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: edit_ancillary.php?id=$equipment_id"); // You can also put the equipment_id here if there is one
    exit();
}
?>
