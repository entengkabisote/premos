<?php
include('session_config.php');
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// 1. Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Invalid request method.";
    exit;
}

// 2. CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo "Invalid CSRF token.";
    exit;
}

// 3. Get and validate IDs
$CertificateID = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$vessel_id = filter_input(INPUT_POST, 'vessel_id', FILTER_VALIDATE_INT);

if (!$CertificateID || !$vessel_id) {
    $_SESSION['toastMessage'] = "Invalid certificate ID or vessel ID.";
    $_SESSION['toastType'] = "error";
    header("Location: view_certificates.php?id=$vessel_id");
    exit;
}

// 4. Strict role check (superuser, admin, superadmin only)
$allowed_roles = ['admin', 'superadmin', 'superuser'];
$user_role = strtolower($_SESSION['role'] ?? '');

if (!in_array($user_role, $allowed_roles)) {
    $_SESSION['toastMessage'] = "Unauthorized access.";
    $_SESSION['toastType'] = "error";
    header("Location: view_certificates.php?id=$vessel_id");
    exit;
}

// 5. Delete image file kung meron
$sql_img = "SELECT CertificateImage FROM vesselcertificates WHERE CertificateID = ?";
$stmt_img = $conn->prepare($sql_img);
$stmt_img->bind_param("i", $CertificateID);
$stmt_img->execute();
$res_img = $stmt_img->get_result();
if ($img_row = $res_img->fetch_assoc()) {
    $image_path = $img_row['CertificateImage'];
    if ($image_path && file_exists($image_path)) {
        unlink($image_path);
    }
}
$stmt_img->close();

// 6. Delete the certificate
$delete_sql = "DELETE FROM vesselcertificates WHERE CertificateID = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $CertificateID);

if ($delete_stmt->execute()) {
    $_SESSION['toastMessage'] = "Certificate deleted successfully!";
    $_SESSION['toastType'] = "success";
} else {
    $_SESSION['toastMessage'] = "Error deleting certificate: " . $delete_stmt->error;
    $_SESSION['toastType'] = "error";
}
$delete_stmt->close();

header('Location: view_certificates.php?id=' . $vessel_id);
exit;
?>
