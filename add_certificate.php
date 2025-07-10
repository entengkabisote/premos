<?php
include 'db_connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $VesselID = filter_input(INPUT_POST, 'VesselID', FILTER_VALIDATE_INT);
    $DocumentNumber = htmlspecialchars($_POST['DocumentNumber']);
    $CertificateName = htmlspecialchars($_POST['CertificateName']);
    $IssuanceDate = $_POST['IssuanceDate'];

    // Check if Perpetual checkbox is checked
    $isPerpetual = isset($_POST['Perpetual']) && $_POST['Perpetual'] === 'on';

    // Handle Expiry Date and Renewal Date based on Perpetual status
    $ExpiryDate = $isPerpetual ? null : (!empty($_POST['ExpiryDate']) ? $_POST['ExpiryDate'] : null);
    $RenewalDate = $isPerpetual ? null : (!empty($_POST['RenewalDate']) ? $_POST['RenewalDate'] : null);
    
    $IssuedBy = htmlspecialchars($_POST['IssuedBy']);
    $Remarks = htmlspecialchars($_POST['Remarks']);

    // Compute automatic status bago mag-insert
    $Status = "Active";
    $current_date = date('Y-m-d');

    if ($isPerpetual || empty($ExpiryDate) || $ExpiryDate === '0000-00-00') {
        $Status = "Perpetual";
    } else {
        $days_diff = (strtotime($ExpiryDate) - strtotime($current_date)) / (60 * 60 * 24);
        if ($days_diff < 0) {
            $Status = "Expired";
        } elseif ($days_diff <= 60) {
            $Status = "Expiring Soon";
        }
    }
    if (!empty($RenewalDate) && $RenewalDate <= $current_date) {
        $Status = "Pending Renewal";
    }

    // Check for existing certificate
    $check_sql = "SELECT * FROM vesselcertificates WHERE VesselID = ? AND (CertificateName = ? OR DocumentNumber = ?)";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iss", $VesselID, $CertificateName, $DocumentNumber);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['toastMessage'] = "A certificate with the same name or document number already exists for this vessel.";
        $_SESSION['toastType'] = "danger";
        header('Location: view_certificates.php?id=' . $VesselID);
        exit;
    }


    // SECURE FILE UPLOAD
    $CertificateImage = null;
    if (isset($_FILES['CertificateImage']) && $_FILES['CertificateImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['CertificateImage'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $real_mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB

        // Validate file type and size
        if (in_array($real_mime, $allowed_types) && $file['size'] <= $max_size) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid('cert_', true) . '.' . $ext;
            $upload_dir = "uploads/certificates/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $target_path = $upload_dir . $new_filename;

            // Extra: prevent double dot attack
            if (strpos($new_filename, '..') !== false) {
                $_SESSION['toastMessage'] = "Invalid file name.";
                $_SESSION['toastType'] = "danger";
                header('Location: view_certificates.php?id=' . $VesselID);
                exit;
            }

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $CertificateImage = $target_path;
            } else {
                $_SESSION['toastMessage'] = "Failed to upload the file.";
                $_SESSION['toastType'] = "danger";
                header('Location: view_certificates.php?id=' . $VesselID);
                exit;
            }
        } else {
            if (!(in_array($real_mime, $allowed_types) && $file['size'] <= $max_size)) {
                $_SESSION['toastMessage'] = "Invalid file type or size exceeded 2MB.";
                $_SESSION['toastType'] = "danger";
                header('Location: view_certificates.php?id=' . $VesselID);
                exit;
            }

        }
    }

    // INSERT DATA
    $insert_sql = "INSERT INTO vesselcertificates 
        (VesselID, DocumentNumber, CertificateName, IssuanceDate, ExpiryDate, RenewalDate, IssuedBy, Status, Remarks, CertificateImage)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param(
        "isssssssss", 
        $VesselID, $DocumentNumber, $CertificateName, $IssuanceDate, 
        $ExpiryDate, $RenewalDate, $IssuedBy, $Status, $Remarks, $CertificateImage
    );

    if ($insert_stmt->execute()) {
        $_SESSION['toastMessage'] = "Certificate added successfully!";
        $_SESSION['toastType'] = "success";
        header('Location: view_certificates.php?id=' . $VesselID);
        exit;
    } else {
        $_SESSION['toastMessage'] = "Database error: " . $insert_stmt->error;
        $_SESSION['toastType'] = "danger";
        header('Location: view_certificates.php?id=' . $VesselID);
        exit;
    }
}
?>
