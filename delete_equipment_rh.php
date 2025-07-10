<?php
include('session_config.php');
include "db_connect.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id <= 0) {
        $_SESSION['toastMessage'] = "Invalid equipment ID.";
        $_SESSION['toastType'] = "error";
        header("Location: machinery.php");
        exit;
    }

    $sql = "DELETE FROM equipment_name WHERE equipment_name_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['toastMessage'] = "Equipment deleted successfully!";
        $_SESSION['toastType'] = "success";
    } else {
        $_SESSION['toastMessage'] = "Error deleting equipment: " . $conn->error;
        $_SESSION['toastType'] = "error";
    }
    $stmt->close();
    header("Location: machinery.php");
    exit;
}
?>
