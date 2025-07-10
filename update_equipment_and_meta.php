<?php
include('session_config.php');
require 'db_connect.php';

$equipment_id = $_POST['equipment_id'] ?? '';
$equipment_name = trim($_POST['equipment_name'] ?? '');
$category = trim($_POST['category'] ?? '');

// Kung may inspection_meta ka, kunin at i-handle mo rin dito kung gusto mo
$inspection_meta = $_POST['inspection_meta'] ?? '';

// Validation
if (!$equipment_id || !$equipment_name || !$category) {
    echo json_encode(['status' => 'error', 'message' => 'All fields required.']);
    exit;
}

// Update equipment info
$sql = "UPDATE equipment_table SET equipment_name=?, category=? WHERE equipment_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $equipment_name, $category, $equipment_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
$stmt->close();
$conn->close();
?>
