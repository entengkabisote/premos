<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = $_POST['equipment_id'];
    $equipment_name = $_POST['equipment_name'];
    $category = $_POST['category']; // Get the category value

    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE ancillary_table SET equipment_name = ?, category = ? WHERE equipment_id = ?");
    
    // Bind the parameters
    $stmt->bind_param("ssi", $equipment_name, $category, $equipment_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Record updated successfully";
        
        // You can add code to update inspection_meta_table here
        
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    
    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();

?>
