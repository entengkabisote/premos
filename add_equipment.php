<?php
include 'db_connect.php';
$equipmentName = $_POST['equipmentName'];
$equipmentCategory = $_POST['equipmentCategory'];

// Check if category is empty or null
if (empty($equipmentCategory)) {
    // Respond with an error status if category is empty
    echo json_encode(["status" => "failure", "message" => "Category cannot be empty"]);
    exit; // Stop script execution
}

// Check for duplicates
$sql_check = "SELECT * FROM equipment_table WHERE equipment_name = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $equipmentName);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if($result_check->num_rows > 0) {
    die(json_encode(["status" => "duplicate"]));
} else {
    // Using prepared statements for inserting
    $stmt = $conn->prepare("INSERT INTO equipment_table (equipment_name, category) VALUES (?, ?)");
    $stmt->bind_param("ss", $equipmentName, $equipmentCategory);
  
    if($stmt->execute()) {
        die(json_encode(["status" => "success"]));
    } else {
        die(json_encode(["status" => "failure"]));
    }
}
?>
