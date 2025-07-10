<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

// CSRF token (optional kung mag-POST ka later)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$vessel_id = intval($_GET['id'] ?? 0);

$cert_sql = "SELECT * FROM vesselcertificates WHERE VesselID = ?";
$cert_stmt = $conn->prepare($cert_sql);
$cert_stmt->bind_param("i", $vessel_id);
$cert_stmt->execute();
$certificates = $cert_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates | Planned Maintenance System</title>
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


<div class="container py-4">
    <h4 class="mb-4">Vessel Certificates Management</h4>
    <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between mb-3">
            <a href="vessel_details.php?id=<?= $vessel_id ?>" class="btn btn-outline-dark"
            data-bs-toggle="tooltip" data-bs-placement="top" title="View Vessel Details">
                <i class="fa fa-ship"></i> Back
            </a>
        </div>

        <h5 class="mb-4">Add New Certificate</h5>
        <form action="add_certificate.php" method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="VesselID" value="<?= htmlspecialchars($vessel_id); ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Document Number</label>
                    <input type="text" class="form-control" name="DocumentNumber" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Certificate Name</label>
                    <input type="text" class="form-control" name="CertificateName" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Issuance Date</label>
                    <input type="date" class="form-control" name="IssuanceDate" required>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <div class="form-check mb-2 me-2">
                        <input class="form-check-input" type="checkbox" id="Perpetual" name="Perpetual" onchange="toggleExpiry();">
                        <label class="form-check-label" for="Perpetual">Perpetual / No Expiry</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" class="form-control" name="ExpiryDate" id="ExpiryDate">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Renewal Date</label>
                    <input type="date" class="form-control" name="RenewalDate" id="RenewalDate">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issued By</label>
                    <input type="text" class="form-control" name="IssuedBy" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" name="Status" id="Status" placeholder="Auto-calculated status" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="Remarks" rows="1"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Certificate Image (jpg/png/pdf)</label>
                    <input type="file" class="form-control" name="CertificateImage" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="col-12 mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="fa fa-plus-circle"></i> Add Certificate</button>
                    <!-- <a href="vessel_details.php?id=<?= $vessel_id ?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Details</a> -->
                </div>
            </div>
        </form>
    </div>
    <div class="card p-4 shadow-sm">
        <h5 class="mb-4">List of Certificates</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Document Number</th>
                        <th>Certificate Name</th>
                        <th>Issuance Date</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $count = 1;
                while ($cert_row = $certificates->fetch_assoc()) {
                    $status = $cert_row['Status'];
                    $expiryDateRaw = $cert_row['ExpiryDate'];
                    $expiryDate = (!empty($expiryDateRaw) && $expiryDateRaw != "0000-00-00") ? new DateTime($expiryDateRaw) : null;
                    $today = new DateTime();

                    if (!$expiryDate) {
                        $status = "Perpetual";
                    } else {
                        if ($expiryDate < $today) {
                            $status = "Expired";
                        } else {
                            $daysLeft = $today->diff($expiryDate)->days;
                            if ($daysLeft <= 60) {
                                $status = "Expiring Soon";
                            }
                        }
                    }

                    // Update DB if status changed
                    if ($cert_row['Status'] !== $status) {
                        $update_sql = "UPDATE vesselcertificates SET Status = ? WHERE CertificateID = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $status, $cert_row['CertificateID']);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }

                    $statusClass = '';
                    if ($status == "Expired") {
                        $statusClass = "text-danger fw-bold";
                    } elseif ($status == "Expiring Soon") {
                        $statusClass = "text-warning fw-bold";
                    } elseif ($status == "Perpetual") {
                        $statusClass = "text-primary fw-bold";
                    } else {
                        $statusClass = "text-success fw-bold";
                    }

                    echo "<tr>";
                    echo "<td>" . $count++ . "</td>";
                    echo "<td>" . htmlspecialchars($cert_row['DocumentNumber']) . "</td>";
                    echo "<td>" . htmlspecialchars($cert_row['CertificateName']) . "</td>";
                    echo "<td>" . htmlspecialchars($cert_row['IssuanceDate']) . "</td>";
                    echo "<td>" . ($expiryDateRaw == "0000-00-00" ? '<span class="text-primary">No Expiration</span>' : htmlspecialchars($cert_row['ExpiryDate'])) . "</td>";
                    echo "<td class='$statusClass'>" . htmlspecialchars($status) . "</td>";
                    echo "<td>
                        <a href='view_certificate.php?id=" . $cert_row['CertificateID'] . "&vessel_id=" . $cert_row['VesselID'] . "' 
                            class='btn btn-sm btn-outline-info' 
                            data-bs-toggle='tooltip' data-bs-placement='top' title='View Certificate'>
                            <i class='fa fa-eye'></i>
                        </a>
                        <a href='edit_certificate.php?id=" . $cert_row['CertificateID'] . "&vessel_id=" . $cert_row['VesselID'] . "' 
                            class='btn btn-sm btn-outline-primary ms-2' 
                            data-bs-toggle='tooltip' data-bs-placement='top' title='Edit Certificate'>
                            <i class='fa fa-pencil'></i>
                        </a>
                        <form action='delete_certificate.php' method='post' style='display:inline;' 
                            onsubmit=\"return confirm('Are you sure you want to delete this certificate?');\">
                            <input type='hidden' name='id' value='" . $cert_row['CertificateID'] . "'>
                            <input type='hidden' name='vessel_id' value='" . $vessel_id . "'>
                            <input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>
                            <button type='submit' class='btn btn-sm btn-outline-danger ms-2' 
                                    data-bs-toggle='tooltip' data-bs-placement='top' title='Delete Certificate'>
                                <i class='fa fa-trash'></i>
                            </button>
                        </form>
                    </td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts/header.js" defer></script>
<?php include 'toastr_handler.php'; ?>
<script>
    // Enable all tooltips after DOM loaded
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

        

    function toggleExpiry() {
        var checkBox = document.getElementById("Perpetual");
        var expiryDate = document.getElementById("ExpiryDate");
        var renewalDate = document.getElementById("RenewalDate");

        if (checkBox.checked){
            expiryDate.disabled = true;
            expiryDate.value = '';
            renewalDate.disabled = true;
            renewalDate.value = '';
        } else {
            expiryDate.disabled = false;
            renewalDate.disabled = false;
        }
    }

    // Status calculation
    function calculateStatus() {
        var expiryDate = document.getElementById("ExpiryDate").value;
        var renewalDate = document.getElementById("RenewalDate").value;
        var perpetualChecked = document.getElementById("Perpetual").checked;
        var statusField = document.getElementById("Status");

        var today = new Date();
        today.setHours(0, 0, 0, 0);

        if (perpetualChecked) {
            statusField.value = "Perpetual";
        } else if (expiryDate) {
            var expiry = new Date(expiryDate);
            if (expiry < today) {
                statusField.value = "Expired";
            } else if ((expiry - today) / (1000 * 60 * 60 * 24) <= 30) {
                statusField.value = "Expiring Soon";
            } else {
                statusField.value = "Active";
            }
        } else {
            statusField.value = "Active";
        }

        if (renewalDate) {
            var renewal = new Date(renewalDate);
            if (renewal <= today) {
                statusField.value = "Pending Renewal";
            }
        }
    }

    document.getElementById("ExpiryDate").addEventListener("input", calculateStatus);
    document.getElementById("RenewalDate").addEventListener("input", calculateStatus);
    document.getElementById("Perpetual").addEventListener("change", calculateStatus);
    </script>
</body>
</html>
