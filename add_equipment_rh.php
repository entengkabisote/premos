<?php
include('session_config.php');
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipment_name = trim($_POST['equipment_name']);
    $equipment_type = trim($_POST['equipment_type']);

    // Get equipment_category_id from equipment_category table
    $type_id_query = "SELECT equipment_category_id FROM equipment_category WHERE category_name = ?";
    $type_id_stmt = $conn->prepare($type_id_query);
    $type_id_stmt->bind_param("s", $equipment_type);
    $type_id_stmt->execute();
    $type_id_result = $type_id_stmt->get_result();
    if ($type_id_result->num_rows > 0) {
        $type_id_row = $type_id_result->fetch_assoc();
        $equipment_category_id = $type_id_row['equipment_category_id'];
    } else {
        $_SESSION['toastMessage'] = "No matching equipment category ID found!";
        $_SESSION['toastType'] = "error";
        header("Location: machinery.php");
        exit;
    }
    $type_id_stmt->close();

    // Check for duplicate equipment name
    $checkQuery = "SELECT equipment_name FROM equipment_name WHERE equipment_name = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $equipment_name);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['toastMessage'] = "Equipment name already exists!";
        $_SESSION['toastType'] = "error";
        $checkStmt->close();
        header("Location: machinery.php");
        exit;
    }
    $checkStmt->close();

    // Insert new equipment
    $stmt = $conn->prepare("INSERT INTO equipment_name (equipment_name, equipment_category_id) VALUES (?, ?)");
    $stmt->bind_param("si", $equipment_name, $equipment_category_id);
    if ($stmt->execute()) {
        $_SESSION['toastMessage'] = "Equipment added successfully!";
        $_SESSION['toastType'] = "success";
    } else {
        $_SESSION['toastMessage'] = "Failed to add equipment!";
        $_SESSION['toastType'] = "error";
    }
    $stmt->close();

    header("Location: machinery.php");
    exit;
}
?>
