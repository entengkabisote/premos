<?php
include('session_config.php');
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id'])) {
    $spare_part_id = $_GET['id'];

    // JOIN to get both component_id and equipment_name_id for redirection
    $stmt = $conn->prepare("
        SELECT csp.component_id, ec.equipment_name_id
        FROM component_spare_parts csp
        JOIN equipment_component ec ON csp.component_id = ec.equipment_component_id
        WHERE csp.spare_part_id = ?
    ");
    if (!$stmt) {
        die("Error in prepare (fetching for redirect): " . $conn->error);
    }
    $stmt->bind_param("i", $spare_part_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $componentData = $result->fetch_assoc();
    $stmt->close();

    if ($componentData) {
        // Delete the spare part record from spare_parts table
        $deleteStmt = $conn->prepare("DELETE FROM spare_parts WHERE id = ?");
        $deleteStmt->bind_param("i", $spare_part_id);

        if ($deleteStmt->execute()) {
            $_SESSION['message'] = "Spare part deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting spare part: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        $deleteStmt->close();

        header("Location: spareparts.php?component_id={$componentData['component_id']}&equipment_name_id={$componentData['equipment_name_id']}");
        exit;
    } else {
        $_SESSION['message'] = "Spare part not found.";
        $_SESSION['message_type'] = "error";
        header("Location: spareparts.php");
        exit;
    }
}

?>
