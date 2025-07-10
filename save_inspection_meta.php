<?php
include('session_config.php');
include('db_connect.php');

error_log(print_r($_POST['inspection_data'], true));

if (!isset($_POST['equipmentId']) || empty($_POST['equipmentId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Equipment ID.']);
    exit;
}

$equipment_id = $_POST['equipmentId'];
$inspection_data = json_decode($_POST['inspection_data'], true);

if ($inspection_data === null || empty($inspection_data)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or empty inspection data.']);
    exit;
}

$isDuplicate = false;
$sql = "SELECT * FROM inspection_meta_table WHERE equipment_id = ? AND inspection_type = ?";
$stmt_select = $conn->prepare($sql);
$stmt_select->bind_param("ss", $equipment_id, $type);

foreach ($inspection_data as $inspection) {
    $type = $inspection['type'];

    if (!$stmt_select->execute()) {
        echo json_encode(['status' => 'error', 'message' => $stmt_select->error]);
        $stmt_select->close();
        exit;
    }
    $result = $stmt_select->get_result();

    if ($result->num_rows > 0) {
        $isDuplicate = true;
        break;
    }
}

$stmt_select->close();

if ($isDuplicate) {
    echo json_encode(['status' => 'duplicate']);
    exit;
}

$allSuccess = true;

foreach ($inspection_data as $data) {
    $type = $data['type'];
    $interval = isset($data['interval']) ? $data['interval'] : '';
    $person = $data['person'];
    $criticality = $data['criticality'];

    if (empty($data['type'])) {
        $allSuccess = false;
        break;
    }

    $sql_insert = "INSERT INTO inspection_meta_table (equipment_id, inspection_type, inspection_interval, person_in_charge, criticality) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssss", $equipment_id, $type, $interval, $person, $criticality);

    if (!$stmt_insert->execute()) {
        $allSuccess = false;
        $stmt_insert->close();
        break;
    }

    $stmt_insert->close();
}

$saved_inspection_data = [];
$sql_fetch = "SELECT * FROM inspection_meta_table WHERE equipment_id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("s", $equipment_id);

if ($stmt_fetch->execute()) {
    $result_fetch = $stmt_fetch->get_result();
    while ($row = $result_fetch->fetch_assoc()) {
        $saved_inspection_data[] = $row;
    }
    $stmt_fetch->close();
}


echo $allSuccess 
    ? json_encode(['status' => 'success', 'data' => $saved_inspection_data]) 
    : json_encode(['status' => 'error', 'message' => 'Failed to insert data.']);


$conn->close();
?>
