<?php
include('session_config.php');
include 'db_connect.php';
include 'functions.php'; // <--- include mo kung saan mo nilagay yung function

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or display the error message
    header('Location: index.php'); // Redirect to login page
    exit; // Stop further script execution
    // die("You need to login.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['metaId'])) {
    $equipment_id = isset($_POST['equipmentId']) ? (int)$_POST['equipmentId'] : null;
    $meta_id = (int)$_POST['metaId'];
    $inspection_type = $_POST['inspectionType'];
    $inspection_interval = $_POST['inspectionInterval'];
    $person_in_charge = $_POST['personInCharge'];
    $criticality = $_POST['criticality'];

    $update_sql = "UPDATE inspection_ancillary SET 
                   inspection_type = ?, 
                   inspection_interval = ?, 
                   person_in_charge = ?, 
                   criticality = ? 
                   WHERE meta_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $inspection_type, $inspection_interval, $person_in_charge, $criticality, $meta_id);

    if ($stmt->execute()) {
        setToast("Inspection type updated successfully.", "success");
    } else {
        setToast("Unable to update inspection type. Error: " . $stmt->error, "error");
    }

    $stmt->close();
    $conn->close();

    header("Location: edit_equipment.php?id=$equipment_id");
    exit();
} else {
    setToast("Invalid request.", "error");
    header("Location: edit_equipment.php?id=$equipment_id");
    exit();
}


?>
