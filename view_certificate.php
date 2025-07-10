<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

// CertificateID at VesselID
$certificate_id = intval($_GET['id'] ?? 0);
$vessel_id = intval($_GET['vessel_id'] ?? 0);

$sql = "SELECT * FROM vesselcertificates WHERE CertificateID = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $certificate_id);
$stmt->execute();
$cert = $stmt->get_result()->fetch_assoc();

if (!$cert) {
    $_SESSION['toastMessage'] = "Certificate not found.";
    $_SESSION['toastType'] = "danger";
    header("Location: view_certificates.php?id=$vessel_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Certificate | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'toastr_handler.php'; ?>

<div class="container py-4">
    <h4 class="mb-4">Certificate Details</h4>
    <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between mb-3">
            <a href="view_certificates.php?id=<?= $vessel_id ?>" class="btn btn-outline-dark" data-bs-toggle="tooltip" data-bs-title="Back to Certificates">
                <i class="fa fa-ship"></i>
            </a>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Document Number</label>
                <div class="form-control bg-light"><?= htmlspecialchars($cert['DocumentNumber']) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Certificate Name</label>
                <div class="form-control bg-light"><?= htmlspecialchars($cert['CertificateName']) ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Issuance Date</label>
                <div class="form-control bg-light"><?= htmlspecialchars($cert['IssuanceDate']) ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Expiry Date</label>
                <div class="form-control bg-light">
                    <?= ($cert['ExpiryDate'] == "0000-00-00" || !$cert['ExpiryDate']) ? '<span class="text-primary">No Expiration</span>' : htmlspecialchars($cert['ExpiryDate']) ?>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Renewal Date</label>
                <div class="form-control bg-light"><?= htmlspecialchars($cert['RenewalDate']) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Issued By</label>
                <div class="form-control bg-light"><?= htmlspecialchars($cert['IssuedBy']) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Status</label>
                <?php
                    $status = $cert['Status'];
                    $statusClass = "text-success";
                    if ($status == "Expired") $statusClass = "text-danger fw-bold";
                    elseif ($status == "Expiring Soon") $statusClass = "text-warning fw-bold";
                    elseif ($status == "Perpetual") $statusClass = "text-primary fw-bold";
                ?>
                <div class="form-control bg-light <?= $statusClass ?>"><?= htmlspecialchars($status) ?></div>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold">Remarks</label>
                <div class="form-control bg-light"><?= htmlspecialchars($cert['Remarks']) ?></div>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold">Certificate Image / File</label>
                <?php if (!empty($cert['CertificateImage'])): ?>
                    <div>
                        <?php
                        $file_ext = strtolower(pathinfo($cert['CertificateImage'], PATHINFO_EXTENSION));
                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            echo "<a href='".htmlspecialchars($cert['CertificateImage'])."' target='_blank'>
                                    <img src='".htmlspecialchars($cert['CertificateImage'])."' class='img-fluid rounded border shadow-sm' alt='Certificate Image' style='max-width:320px; max-height:240px;'>
                                </a>";
                        } elseif ($file_ext == 'pdf') {
                            echo "<a href='".htmlspecialchars($cert['CertificateImage'])."' target='_blank' class='btn btn-outline-danger'><i class='fa fa-file-pdf'></i> View PDF</a>";
                        } else {
                            echo "<a href='".htmlspecialchars($cert['CertificateImage'])."' target='_blank' class='btn btn-outline-secondary'><i class='fa fa-file'></i> Download</a>";
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <div class="form-control bg-light">No file uploaded.</div>
                <?php endif; ?>
            </div>
            <div class="col-12 mt-4 d-flex gap-2">
                <a href="edit_certificate.php?id=<?= $cert['CertificateID'] ?>&vessel_id=<?= $vessel_id ?>" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-title="Edit Certificate">
                    <i class="fa fa-pencil"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts/header.js" defer></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
</body>
</html>
