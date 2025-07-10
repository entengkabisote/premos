<?php
include 'db_connect.php';

$sql = "SELECT * FROM equipment_table";
$result = $conn->query($sql);

$data = [];

// Modified loop to handle NULL category values
while($row = $result->fetch_assoc()) {
    // Check if 'category' column is NULL or empty and set a default value
    if (empty($row['category'])) {
        $row['category'] = 'N/A'; // Replace null with 'N/A' or any default value
    }
    $data[] = $row;
}

echo json_encode($data);
?>
