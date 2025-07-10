<?php
include('session_config.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

include 'db_connect.php';

// Input validation
if (!isset($_POST['vessel_name']) || !isset($_POST['imo_number']) || empty(trim($_POST['vessel_name'])) || empty(trim($_POST['imo_number']))) {
    $_SESSION['vessel_error'] = "Please provide both vessel name and IMO number.";
    header("Location: vessel.php");
    exit;
}

$vessel_name = strtoupper($_POST['vessel_name']);
$imo_number = $_POST['imo_number'];


// Single Query for checking both vessel name and IMO number
$sql = "SELECT * FROM vessels WHERE vessel_name = ? OR imo_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $vessel_name, $imo_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch all matching vessels
    while ($existingVessel = $result->fetch_assoc()) {
        if ($existingVessel['vessel_name'] === $vessel_name) {
            $_SESSION['toastMessage'] = "A vessel with the same name already exists.";
            $_SESSION['toastType'] = "error";
            break;
        }
        if ($existingVessel['imo_number'] === $imo_number) {
            $_SESSION['toastMessage'] = "A vessel with the same IMO number already exists.";
            $_SESSION['toastType'] = "error";
            break;
        }

    }
    header("Location: vessel.php");
    exit;
} else {
    // Save vessel_name and imo_number to session
    $_SESSION['vessel_name'] = $vessel_name;
    $_SESSION['imo_number'] = $imo_number;

    header("Location: enter_vessel_details.php");
    exit;
}

$stmt->close();
$conn->close();
?>
