<?php
include('db_connect.php');

if (!isset($_POST['equipmentId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing equipment ID.']);
    exit;
}

$equipment_id = $_POST['equipmentId'];

$sql = "SELECT * FROM inspection_ancillary WHERE equipment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $equipment_id);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$inspection_data = [];

while ($row = $result->fetch_assoc()) {
    $inspection_data[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $inspection_data]);

$stmt->close();
$conn->close();
?>
