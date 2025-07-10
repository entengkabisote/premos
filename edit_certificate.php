<?php
include('session_config.php');
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Always ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// vessel_id is required
if (isset($_GET['vessel_id'])) {
    $VesselID = filter_input(INPUT_GET, 'vessel_id', FILTER_SANITIZE_NUMBER_INT);
} else {
    echo "Vessel ID is required.";
    exit;
}

$CertificateImage = "";
$oldImage = "";

// Save changes (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_changes'])) {
    $CertificateID = filter_input(INPUT_POST, 'CertificateID', FILTER_VALIDATE_INT);
    $VesselID = filter_input(INPUT_POST, 'VesselID', FILTER_VALIDATE_INT);
    $DocumentNumber = htmlspecialchars($_POST['DocumentNumber']);
    $CertificateName = htmlspecialchars($_POST['CertificateName']);
    $IssuanceDate = $_POST['IssuanceDate'];
    $ExpiryDate = empty($_POST['ExpiryDate']) ? NULL : $_POST['ExpiryDate'];
    $RenewalDate = empty($_POST['RenewalDate']) ? NULL : $_POST['RenewalDate'];
    $IssuedBy = htmlspecialchars($_POST['IssuedBy']);
    $Remarks = htmlspecialchars($_POST['Remarks']);
    $Status = "Active";

    // Handle Status
    if (isset($_POST['Perpetual']) && $_POST['Perpetual'] === 'on') {
        $Status = "Perpetual";
        $ExpiryDate = NULL;
        $RenewalDate = NULL;
    } else if (empty($ExpiryDate) || $ExpiryDate == '0000-00-00') {
        $Status = "Perpetual";
    } else {
        $today = date('Y-m-d');
        $days_diff = (strtotime($ExpiryDate) - strtotime($today)) / (60 * 60 * 24);
        if ($days_diff < 0) $Status = "Expired";
        elseif ($days_diff <= 60) $Status = "Expiring Soon";
        else $Status = "Active";
    }

    // Get old image path for deletion if replaced or cleared
    $fetch_img_sql = "SELECT CertificateImage FROM vesselcertificates WHERE CertificateID=?";
    $fetch_img_stmt = $conn->prepare($fetch_img_sql);
    $fetch_img_stmt->bind_param("i", $CertificateID);
    $fetch_img_stmt->execute();
    $fetch_img_result = $fetch_img_stmt->get_result();
    $oldImage = ($row = $fetch_img_result->fetch_assoc()) ? $row['CertificateImage'] : "";

    // File upload logic
    $CertificateImage = $oldImage;
    $upload_dir = "uploads/certificates/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    // Delete Image if requested
    if (isset($_POST['clear_image']) && $_POST['clear_image'] == "1" && !empty($oldImage)) {
        if (file_exists($oldImage)) unlink($oldImage);
        $CertificateImage = null;
    }

    // Handle New Upload
    if (isset($_FILES['CertificateImage']) && $_FILES['CertificateImage']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $file = $_FILES['CertificateImage'];
        $max_size = 2 * 1024 * 1024; // 2MB
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid('cert_', true) . '.' . $ext;
            $target_path = $upload_dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // delete old image
                if (!empty($oldImage) && file_exists($oldImage)) unlink($oldImage);
                $CertificateImage = $target_path;
            }
        } elseif($file['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['toastMessage'] = "Invalid file type or size exceeded 2MB.";
            $_SESSION['toastType'] = "error";
            header("Location: edit_certificate.php?id=$CertificateID&vessel_id=$VesselID");
            exit;

        }
    }

    // Check for duplicate
    $check_sql = "SELECT * FROM vesselcertificates WHERE VesselID = ? AND (CertificateName = ? OR DocumentNumber = ?) AND CertificateID != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("issi", $VesselID, $CertificateName, $DocumentNumber, $CertificateID);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['toastMessage'] = "Another certificate with the same name or document number already exists for this vessel.";
        $_SESSION['toastType'] = "error";
        header("Location: edit_certificate.php?id=$CertificateID&vessel_id=$VesselID");
        exit;

    } else {
        $update_sql = "UPDATE vesselcertificates SET DocumentNumber=?, CertificateName=?, IssuanceDate=?, ExpiryDate=?, RenewalDate=?, IssuedBy=?, Status=?, Remarks=?, CertificateImage=? WHERE CertificateID=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssssssssi", $DocumentNumber, $CertificateName, $IssuanceDate, $ExpiryDate, $RenewalDate, $IssuedBy, $Status, $Remarks, $CertificateImage, $CertificateID);
        if ($update_stmt->execute()) {
            $_SESSION['toastMessage'] = "Certificate updated successfully!";
            $_SESSION['toastType'] = "success";
            header('Location: view_certificates.php?id=' . $VesselID);
            exit;
        } else {
            $_SESSION['toastMessage'] = "Error updating record: " . $conn->error;
            $_SESSION['toastType'] = "error";
            header("Location: edit_certificate.php?id=$CertificateID&vessel_id=$VesselID");
            exit;
        }

    }
} else if (isset($_GET['id'])) {
    $CertificateID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $fetch_sql = "SELECT * FROM vesselcertificates WHERE CertificateID=?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("i", $CertificateID);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    if (!$certificate = $result->fetch_assoc()) {
        echo "Certificate not found.";
        exit;
    }
    $Status = $certificate['Status'];
    $CertificateImage = $certificate['CertificateImage'];
    if (empty($certificate['ExpiryDate']) || $certificate['ExpiryDate'] == '0000-00-00') {
        $Status = "Perpetual";
    } else {
        $today = date('Y-m-d');
        $days_diff = (strtotime($certificate['ExpiryDate']) - strtotime($today)) / (60 * 60 * 24);
        if ($days_diff < 0) $Status = "Expired";
        elseif ($days_diff <= 60) $Status = "Expiring Soon";
        else $Status = "Active";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Certificate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <h4 class="mb-4 text-center">Edit Certificate</h4>
        <div class="card p-4 shadow-sm mb-4">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $CertificateID . '&vessel_id=' . $VesselID; ?>" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="VesselID" value="<?php echo $VesselID; ?>">
                <input type="hidden" name="CertificateID" value="<?php echo $CertificateID; ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Document Number</label>
                        <input type="text" class="form-control" name="DocumentNumber" value="<?php echo htmlspecialchars($certificate['DocumentNumber']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Certificate Name</label>
                        <input type="text" class="form-control" name="CertificateName" value="<?php echo htmlspecialchars($certificate['CertificateName']); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Issuance Date</label>
                        <input type="date" class="form-control" name="IssuanceDate" value="<?php echo $certificate['IssuanceDate']; ?>" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="Perpetual" name="Perpetual" onchange="toggleExpiry();"
                                <?php echo ($certificate['Status'] == 'Perpetual' || empty($certificate['ExpiryDate']) || $certificate['ExpiryDate'] == '0000-00-00') ? 'checked' : ''; ?> />
                            <label class="form-check-label ms-2" for="Perpetual">Perpetual / No Expiry</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="ExpiryDate" id="ExpiryDate"
                            value="<?php echo ($certificate['ExpiryDate'] != '0000-00-00') ? $certificate['ExpiryDate'] : ''; ?>"
                            <?php echo ($certificate['Status'] == 'Perpetual' || empty($certificate['ExpiryDate']) || $certificate['ExpiryDate'] == '0000-00-00') ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Renewal Date</label>
                        <input type="date" class="form-control" name="RenewalDate" id="RenewalDate"
                            value="<?php echo $certificate['RenewalDate']; ?>"
                            <?php echo ($certificate['Status'] == 'Perpetual' || empty($certificate['ExpiryDate']) || $certificate['ExpiryDate'] == '0000-00-00') ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Issued By</label>
                        <input type="text" class="form-control" name="IssuedBy" value="<?php echo htmlspecialchars($certificate['IssuedBy']); ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" name="Status" id="Status" value="<?php echo htmlspecialchars($Status); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="Remarks" rows="1"><?php echo htmlspecialchars($certificate['Remarks']); ?></textarea>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="CertificateImage" class="form-label">Certificate Image (jpg/png/pdf)</label>
                    <input type="file" class="form-control mb-2" name="CertificateImage" accept=".jpg,.jpeg,.png,.pdf">
                    <?php if (!empty($CertificateImage)) :
                        $ext = strtolower(pathinfo($CertificateImage, PATHINFO_EXTENSION)); ?>
                        <div>
                            <strong>Current:</strong>
                            <?php if ($ext == "pdf"): ?>
                                <a href="<?php echo htmlspecialchars($CertificateImage); ?>" target="_blank" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> View PDF
                                </a>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($CertificateImage); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($CertificateImage); ?>" alt="Certificate Image" style="max-width:100px;max-height:100px;border:1px solid #ccc;border-radius:6px;">
                                </a>
                            <?php endif; ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="clear_image" name="clear_image">
                                <label class="form-check-label" for="clear_image">Remove Image</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="view_certificates.php?id=<?php echo $VesselID; ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" name="save_changes" class="btn btn-success">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'toastr_handler.php'; ?>
    <script>
    function toggleExpiry() {
        var checkBox = document.getElementById("Perpetual");
        var expiryDate = document.getElementById("ExpiryDate");
        var renewalDate = document.getElementById("RenewalDate");
        if (checkBox.checked) {
            expiryDate.disabled = true;
            expiryDate.value = '';
            renewalDate.disabled = true;
            renewalDate.value = '';
        } else {
            expiryDate.disabled = false;
            renewalDate.disabled = false;
        }
        calculateStatus();
    }
    function calculateStatus() {
        var expiryDate = document.getElementById("ExpiryDate").value;
        var perpetualChecked = document.getElementById("Perpetual").checked;
        var statusField = document.getElementById("Status");

        var today = new Date();
        today.setHours(0, 0, 0, 0);

        if (perpetualChecked || !expiryDate) {
            statusField.value = "Perpetual";
        } else {
            var expiry = new Date(expiryDate);
            var daysDiff = (expiry - today) / (1000 * 60 * 60 * 24);
            if (daysDiff < 0) statusField.value = "Expired";
            else if (daysDiff <= 60) statusField.value = "Expiring Soon";
            else statusField.value = "Active";
        }
    }
    document.getElementById("ExpiryDate").addEventListener("input", calculateStatus);
    document.getElementById("Perpetual").addEventListener("change", function () {
        toggleExpiry();
        calculateStatus();
    });
    </script>
    <script src="scripts/header.js" defer></script>
</body>
</html>
