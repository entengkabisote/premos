<?php

// Connect to the database 
include 'db_connect.php';

$response = [];

if (isset($_POST['id'])) {
    $equipmentId = $_POST['id'];

    $sql = "DELETE FROM equipment_table WHERE equipment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $equipmentId);

    if ($stmt->execute()) {
        $response["status"] = "success";
        $response["message"] = "Equipment successfully deleted!";
    } else {
        $response["status"] = "error";
        $response["message"] = "Failed to delete equipment. " . $stmt->error;
    }
} else {
    $response["status"] = "error";
    $response["message"] = "Equipment ID is not set or invalid.";
}

echo json_encode($response);
?>
