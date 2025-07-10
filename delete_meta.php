<?php
include 'db_connect.php';

// Check if meta_id is set from POST
if (isset($_POST['meta_id'])) {
    $meta_id = $_POST['meta_id'];

    // SQL query to delete
    $sql = "DELETE FROM inspection_meta_table WHERE meta_id = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameter and execute statement
    $stmt->bind_param('i', $meta_id);
    if ($stmt->execute()) {
        $response = [
            'status' => 'success',
            'message' => 'Record successfully deleted.'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to delete the record.'
        ];
    }
    $stmt->close();
} else {
    $response = [
        'status' => 'error',
        'message' => 'meta_id is not set.'
    ];
}

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
