<?php
include('session_config.php');
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'You must be logged in to perform this action.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vesselId'])) {
    $vesselid = $_POST['vesselId'];

    // Start transaction
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $equipment_names = ['Main Engine 1', 'Gearbox 1', 'Z-peller 1', 'Main Engine 2', 'Gearbox 2', 'Z-peller 2', 'Generator 1', 'Generator 2', 'Generator 3'];

    foreach ($equipment_names as $name) {
        // I-prepare ang SQL statement batay sa equipment_name
        $sql = "SELECT equipment_name_id FROM equipment_name WHERE equipment_name = ?";
        error_log("Preparing SQL: $sql with parameter: $name"); // Log the SQL and parameter
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_message = "Error in prepare statement (SELECT equipment_name_id): " . $conn->error;
            error_log($error_message);
            echo json_encode(['message' => $error_message]);
            $conn->rollback();
            exit;
        }
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            $error_message = "Error in get_result: " . $stmt->error;
            error_log($error_message);
            echo json_encode(['message' => $error_message]);
            $conn->rollback();
            exit;
        }
        $row = $result->fetch_assoc();
        if (!$row) {
            $error_message = "No result found for equipment name: $name";
            error_log($error_message);
            echo json_encode(['message' => $error_message]);
            $conn->rollback();
            exit;
        }
        $equipment_name_id = $row['equipment_name_id'];

        // Gamitin ang vessel_id at equipment_name_id para kumuha ng specific data mula sa vessels
        $vessel_data = getSpecificData($vesselid, $name, $conn); // Ang function na ito ay dapat mong idefine

        // Suriin kung may umiiral na entry
        $sql = "SELECT specificequipmentid FROM specificequipment WHERE vesselid = ? AND equipmentnameid = ?";
        error_log("Preparing SQL: $sql with parameters: $vesselid, $equipment_name_id"); // Log the SQL and parameters
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_message = "Error in prepare statement (SELECT specificequipmentid): " . $conn->error;
            error_log($error_message);
            echo json_encode(['message' => $error_message]);
            $conn->rollback();
            exit;
        }
        $stmt->bind_param("ii", $vesselid, $equipment_name_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            $error_message = "Error in get_result: " . $stmt->error;
            error_log($error_message);
            echo json_encode(['message' => $error_message]);
            $conn->rollback();
            exit;
        }

        if ($result->num_rows > 0) {
            // Kung may umiiral na entry, gumamit ng UPDATE
            $sql = "UPDATE specificequipment SET specificname = ? WHERE vesselid = ? AND equipmentnameid = ?";
        } else {
            // Kung wala, gumamit ng INSERT
            $sql = "INSERT INTO specificequipment (vesselid, equipmentnameid, specificname) VALUES (?, ?, ?)";
        }
        error_log("Preparing SQL: $sql"); // Log the SQL
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_message = "Error in prepare statement (INSERT/UPDATE specificequipment): " . $conn->error . " - SQL: " . $sql;
            error_log($error_message);
            echo json_encode(['message' => $error_message]);
            $conn->rollback();
            exit;
        }

        if ($result->num_rows > 0) {
            $stmt->bind_param("sii", $vessel_data, $vesselid, $equipment_name_id);
        } else {
            $stmt->bind_param("iis", $vesselid, $equipment_name_id, $vessel_data);
        }

        if (!$stmt->execute()) {
            $error_message = "Error in execute: " . $stmt->error;
            error_log($error_message);
            echo json_encode(['message' => $error_message]);
            $conn->rollback();
            exit;
        }
    }

    $conn->commit();
    echo json_encode(['message' => 'Specific equipment data updated successfully.']);
} else {
    echo json_encode(['message' => 'Invalid request.']);
}

function getSpecificData($vesselid, $equipmentname, $conn) {
    // Ang function na ito ay dapat bumalik ng string na kumakatawan sa specific name para sa equipment.
    $data = '';
    $sql = "SELECT main_engine_make, main_engine_model, gearbox_make, gearbox_model, drive, aux_engine_make, aux_engine_model, aux_make 
            FROM vessels 
            WHERE id = ?";
    error_log("Preparing SQL: $sql with parameter: $vesselid"); // Log the SQL and parameter
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error_message = "Error in prepare statement (SELECT vessels): " . $conn->error;
        error_log($error_message);
        return '';
    }
    $stmt->bind_param("i", $vesselid);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        $error_message = "Error in get_result: " . $stmt->error;
        error_log($error_message);
        return '';
    }
    if ($row = $result->fetch_assoc()) {
        switch ($equipmentname) {
            case 'Main Engine 1':
            case 'Main Engine 2':
                $data = $row['main_engine_make'] . ' ' . $row['main_engine_model'];
                break;
            case 'Gearbox 1':
            case 'Gearbox 2':
                $data = $row['gearbox_make'] . ' ' . $row['gearbox_model'];
                break;
            case 'Z-peller 1':
            case 'Z-peller 2':
                $data = $row['drive'];
                break;
            case 'Generator 1':
            case 'Generator 2':
                $data = $row['aux_engine_make'] . ' ' . $row['aux_engine_model'];
                break;
            case 'Generator 3':
                $data = $row['aux_make'];
                break;
            // Dagdagin ang iba pang kaso kung kinakailangan
            default:
                $data = 'Unknown Equipment';
        }
    }
    $stmt->close();
    return $data;
}
?>