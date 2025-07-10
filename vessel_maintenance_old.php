<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or display the error message
    header('Location: index.php'); // Redirect to login page
    exit; // Stop further script execution
    // die("You need to login.");
}

include 'db_connect.php';  // connection in database

    $id = $_GET['id'];

    
// Step 1: Update the SELECT query to include the IDs and other necessary fields
$nonCompliantQuery1 = "SELECT ve.vessel_id, ve.equipment_id, ve.inspection_meta_id, et.equipment_name, imt.inspection_type, ve.last_inspection_date, imt.inspection_interval, ve.next_inspection_date, ve.status, imt.person_in_charge
FROM vessel_equipment ve 
JOIN equipment_table et ON ve.equipment_id = et.equipment_id 
JOIN inspection_meta_table imt ON ve.inspection_meta_id = imt.meta_id 
WHERE ve.vessel_id = ?";

$stmt1 = $conn->prepare($nonCompliantQuery1);
$stmt1->bind_param("i", $id);
$stmt1->execute();
$result1 = $stmt1->get_result();


$nonCompliantQuery2 = "SELECT va.vessel_id, va.equipment_id, va.inspection_meta_id, antab.equipment_name, ia.inspection_type, va.last_inspection_date, ia.inspection_interval, va.next_inspection_date, va.status, ia.person_in_charge
FROM vessel_ancillary va 
JOIN ancillary_table antab ON va.equipment_id = antab.equipment_id 
JOIN inspection_ancillary ia ON va.inspection_meta_id = ia.meta_id 
WHERE va.vessel_id = ?";

$stmt2 = $conn->prepare($nonCompliantQuery2);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result2 = $stmt2->get_result();


function getDaysThreshold($intervalType) {
    $intervalType = strtolower($intervalType);
    switch ($intervalType) {
        case 'daily': return 1;
        case 'weekly': return 7;
        case 'monthly': return 30;
        case 'bi-monthly': return 60;
        case '3-months': return 90;
        case '6-months': return 180;
        case 'yearly': return 365;
        case '5-years': return 1825;
        case 'as required': return 2000; // or another appropriate value
        case 'every 250 hours': return 10.5; // or another appropriate value
        default: return 0;
    }
}

// Step 2: Fetch the results and populate the array with all necessary data
$nonCompliantItems1 = [];
while ($row1 = $result1->fetch_assoc()) {
    $now = new DateTime();
    $last_inspection_date = new DateTime($row1['last_inspection_date']);
    $days_since_last_inspection = $now->diff($last_inspection_date)->days;

    $threshold = getDaysThreshold($row1['inspection_interval']);
    if ($days_since_last_inspection > $threshold) {
        $nonCompliantItems1[] = [
            'vessel_id' => $row1['vessel_id'],
            'equipment_id' => $row1['equipment_id'],
            'inspection_meta_id' => $row1['inspection_meta_id'],
            'equipment_name' => $row1['equipment_name'],
            'inspection_type' => $row1['inspection_type'],
            'person_in_charge' => $row1['person_in_charge'],
            'last_inspection_date' => $row1['last_inspection_date'],
            'next_inspection_date' => $row1['next_inspection_date'],
            'status' => $row1['status'],
            // Add any additional fields you need here
        ];
    }
}

// Step 3: Use the populated arrays to update the database
foreach ($nonCompliantItems1 as $item) {
    // Check if the status is already 'Non-Compliant' before attempting to update
    if ($item['status'] !== 'Non-Compliant') {
        $updateQuery = "UPDATE vessel_equipment SET status = 'Non-Compliant' 
                        WHERE vessel_id = ? AND equipment_id = ? AND inspection_meta_id = ?";
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            error_log('Prepare failed: ' . $conn->error);
            // Handle prepare error
        } else {
            $stmt->bind_param("iii", $item['vessel_id'], $item['equipment_id'], $item['inspection_meta_id']);
            if (!$stmt->execute()) {
                // Handle execute error
                error_log('Execute failed: ' . $stmt->error);
            } else {
                if ($stmt->affected_rows === 0) {
                    // Handle the case where no rows were updated
                    error_log("No records updated for vessel_id: {$item['vessel_id']}, equipment_id: {$item['equipment_id']}, inspection_meta_id: {$item['inspection_meta_id']}");
                }
            }
        }
    }
}

$nonCompliantItems2 = [];
while ($row2 = $result2->fetch_assoc()) {
    $now = new DateTime();
    $last_inspection_date = new DateTime($row2['last_inspection_date']);
    $days_since_last_inspection = $now->diff($last_inspection_date)->days;

    $threshold = getDaysThreshold($row2['inspection_interval']);
    if ($days_since_last_inspection > $threshold) {
        $nonCompliantItems2[] = [
            'vessel_id' => $row2['vessel_id'],
            'equipment_id' => $row2['equipment_id'],
            'inspection_meta_id' => $row2['inspection_meta_id'],
            'equipment_name' => $row2['equipment_name'],
            'inspection_type' => $row2['inspection_type'],
            'person_in_charge' => $row2['person_in_charge'],
            'last_inspection_date' => $row2['last_inspection_date'],
            'next_inspection_date' => $row2['next_inspection_date'],
            'status' => $row2['status'],
            // Add any additional fields you need here
        ];
    }
}

foreach ($nonCompliantItems2 as $item) {
    // Check if the status is already 'Non-Compliant' before attempting to update
    if ($item['status'] !== 'Non-Compliant') {
        $updateQuery = "UPDATE vessel_ancillary SET status = 'Non-Compliant' 
                        WHERE vessel_id = ? AND equipment_id = ? AND inspection_meta_id = ?";
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            error_log('Prepare failed: ' . $conn->error);
            // Handle prepare error
        } else {
            $stmt->bind_param("iii", $item['vessel_id'], $item['equipment_id'], $item['inspection_meta_id']);
            if (!$stmt->execute()) {
                // Handle execute error
                error_log('Execute failed: ' . $stmt->error);
            } else {
                if ($stmt->affected_rows === 0) {
                    // Handle the case where no rows were updated
                    error_log("No records updated for vessel_id: {$item['vessel_id']}, equipment_id: {$item['equipment_id']}, inspection_meta_id: {$item['inspection_meta_id']}");
                }
            }
        }
    }
}


    // Combine both arrays
    $allNonCompliantItems = array_merge($nonCompliantItems1, $nonCompliantItems2);
   
    // Filter para makuha lang ang non-compliant items
    $nonCompliantOnlyItems = array_filter($allNonCompliantItems, function($item) {
        return $item['status'] === 'Non-Compliant';
    });


    $jsonItems = [];

    foreach ($allNonCompliantItems as $item) {
        $jsonItems[] = [
            'vessel_id' => $item['vessel_id'],                       // <-- ID FIELDS NEEDED!
            'equipment_id' => $item['equipment_id'],
            'inspection_meta_id' => $item['inspection_meta_id'],
            'name' => $item['equipment_name'],
            'type' => $item['inspection_type'],
            'person_in_charge' => $item['person_in_charge'],
            'date1' => $item['last_inspection_date'],
            'date2' => $item['next_inspection_date'] ?? 'Not Available',
            'status'=> $item['status']
        ];
    }



    // $sql = "SELECT * FROM vessels WHERE id = ?";
    $sql = "
        SELECT v.*, u.email as superintendent_email, u.fullname as superintendent_fullname
        FROM vessels v
        LEFT JOIN users u ON v.superintendent_id = u.user_id
        WHERE v.id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $current_year = date("Y");
    $start_year = $current_year - 7;
    $end_year = $current_year + 7;


    // Feb 5, 2024 for Due Soon and Over Due

    // Magdagdag ng bagong array para sa status items
    $statusItems = [];

    // Bagong code para sa pag-check ng "Due Soon" at "Over Due" status
    $queryStatus = "
    SELECT 
        rhm.equipment_component_id, 
        rhm.status, 
        en.equipment_name, 
        ec.component_name, 
        c.category_name,
        mt.task_description
    FROM 
        vessel_rh_machinery rhm
        INNER JOIN machinery_tasks mt ON rhm.task_id = mt.task_id
        INNER JOIN equipment_component ec ON rhm.equipment_component_id = ec.equipment_component_id
        INNER JOIN equipment_name en ON ec.equipment_name_id = en.equipment_name_id
        INNER JOIN equipment_category c ON en.equipment_category_id = c.equipment_category_id
        
    WHERE 
        rhm.vessel_id = ? AND 
        (rhm.status = 'Due Soon' OR rhm.status = 'Over Due')
";

    // $queryStatus = "SELECT equipment_component_id, status FROM vessel_rh_machinery WHERE vessel_id = ? AND (status = 'Due Soon' OR status = 'Over Due')";
    $stmtStatus = $conn->prepare($queryStatus);
    $stmtStatus->bind_param("i", $id); // $id mula sa existing code
    $stmtStatus->execute();
    $resultStatus = $stmtStatus->get_result();

    if ($resultStatus->num_rows > 0) {
        while ($rowStatus = $resultStatus->fetch_assoc()) {
            // Magdagdag ng bawat status item sa array
            $statusItems[] = $rowStatus;
        }
    }

    // Tandaan na isara ang statement pagkatapos gamitin
    $stmtStatus->close();

    // Kunin ang current date at threshold date (60 days from today)
    $current_date = date('Y-m-d');
    $threshold_date = date('Y-m-d', strtotime("+60 days")); // Expiring soon kung ≤ 60 days na lang

    // ✅ Update "Perpetual" (walang expiry date o may "0000-00-00")
    $updatePerpetual = "UPDATE vesselcertificates 
                        SET Status = 'Perpetual' 
                        WHERE ExpiryDate IS NULL OR ExpiryDate = '0000-00-00'";
    $stmtPerpetual = $conn->prepare($updatePerpetual);
    $stmtPerpetual->execute();

    // ✅ Update "Expired" (kapag lumampas na sa kasalukuyang araw)
    $updateExpired = "UPDATE vesselcertificates 
                    SET Status = 'Expired' 
                    WHERE ExpiryDate IS NOT NULL 
                    AND ExpiryDate != '0000-00-00' 
                    AND ExpiryDate <= ?";
    $stmtExpired = $conn->prepare($updateExpired);
    $stmtExpired->bind_param("s", $current_date);
    $stmtExpired->execute();

    // ✅ Update "Expiring Soon" (kapag 60 days o mas kaunti bago mag-expire)
    $updateExpiringSoon = "UPDATE vesselcertificates 
                            SET Status = 'Expiring Soon' 
                            WHERE ExpiryDate IS NOT NULL 
                            AND ExpiryDate != '0000-00-00' 
                            AND ExpiryDate > ? 
                            AND ExpiryDate <= ?";
    $stmtExpiringSoon = $conn->prepare($updateExpiringSoon);
    $stmtExpiringSoon->bind_param("ss", $current_date, $threshold_date);
    $stmtExpiringSoon->execute();

    // ✅ Kunin lang ang Expiring Soon at Expired na certificates
    $sql = "SELECT CertificateName, ExpiryDate, Status FROM vesselcertificates 
            WHERE VesselID = ? AND (Status = 'Expiring Soon' OR Status = 'Expired')
            ORDER BY ExpiryDate ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $expiring_certificates = [];
    while ($rowCert = $result->fetch_assoc()) {
        $expiring_certificates[] = $rowCert;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vessel Maintenance | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/vessel_maintenance_styles.css">
    <link rel="stylesheet" href="styles/minimalist_style.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/modal.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- Optional: uniform with dashboard.php -->
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Custom style override -->
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        .dropdown-menu {
            font-size: 13px !important;
            min-width: 220px;
        }

        .dropdown-menu .dropdown-item {
            padding-top: 4px;
            padding-bottom: 4px;
            white-space: normal;
        }

        .btn-overview {
            background-color: #ff9800; 
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-overview:hover {
            background-color: #e68900; 
        }

        .nc-clickable:hover { background: #f2f2f2; cursor:pointer; }

    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold"><?php echo htmlspecialchars($row['vessel_name']); ?></h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="mb-3">
                    <img src="<?php echo htmlspecialchars($row['imahe']); ?>" alt="Vessel Image" class="img-fluid rounded shadow w-100">
                </div>
                <div class="cards-container mb-4">
                    <!-- Equipment Card -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <span class="fw-bold fs-5 mb-2 d-block">Equipment</span>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'SuperAdmin'): ?>
                                    <button type="button" class="btn btn-outline-success btn-sm" 
                                    data-bs-toggle="tooltip" title="Add Equipment"
                                    onclick="window.location.href='add_equipment_vessel.php?vessel_id=<?php echo $row['id']; ?>'">
                                    <i class="material-icons">add</i>
                                    </button>
                                <?php endif; ?>

                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="tooltip" title="Equipment Inspection Overview"
                                    onclick="window.location.href='equipment_overview.php?id=<?php echo $row['id']; ?>'">
                                    <i class="material-icons">visibility</i>
                                </button>

                                <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'SuperAdmin'): ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm"
                                    data-bs-toggle="tooltip" title="Edit Equipment"
                                    onclick="window.location.href='edit_equipment_vessel.php?vessel_id=<?php echo $row['id']; ?>'">
                                    <i class="material-icons">edit</i>
                                    </button>
                                <?php endif; ?>

                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-toggle="tooltip" title="Vessel Inspection Date"
                                    onclick="window.location.href='vessel_maint_details.php?id=<?php echo $row['id']; ?>'">
                                    <i class="material-icons">date_range</i>
                                </button>

                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="tooltip" title="Print Form"
                                    onclick="window.location.href='print_form.php?id=<?php echo $row['id']; ?>'">
                                    <i class="material-icons">print</i>
                                </button>

                                <!-- Reports Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-outline-danger btn-sm dropdown-toggle" type="button" id="dropdownReports1"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
                                    <i class="material-icons">report</i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownReports1">
                                    <li><a class="dropdown-item" href="compliance_report_for_inspection.php?id=<?php echo $row['id']; ?>">Compliance Report</a></li>
                                    <li><a class="dropdown-item" href="person_in_charge_report.php?id=<?php echo $row['id']; ?>">Person-In-Charge Report</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Machineries Card -->
                    <div class="card">
                        <div class="card-body">
                            <span class="fw-bold fs-5 mb-2 d-block">Machineries</span>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'SuperAdmin'): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-success btn-sm dropdown-toggle" type="button" id="dropdownMachinery1"
                                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true" title="Link">
                                            <i class="material-icons">link</i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMachinery1">
                                            <li><a class="dropdown-item" href="link_to_machinery.php?vessel_id=<?php echo $row['id']; ?>">Link Machinery</a></li>
                                            <li><a class="dropdown-item" href="add_ancillary_vessel.php?vessel_id=<?php echo $row['id']; ?>">Add / Edit Ancillary to Vessel</a></li>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'SuperUser'): ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="tooltip" title="Machinery Running Hours Overview"
                                        onclick="window.location.href='machinery_overview.php?vessel_id=<?php echo $row['id']; ?>'">
                                        <i class="material-icons">visibility</i>
                                    </button>
                                <?php endif; ?>

                                <!-- Parameters Entry Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownParameters1"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true" title="Parameters Entry">
                                    <i class="material-icons">date_range</i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownParameters1">
                                    <li><a class="dropdown-item" href="runninghours_entry.php?id=<?php echo $row['id']; ?>">Running Hours Entry</a></li>
                                    <li><a class="dropdown-item" href="vessel_link_ancillary.php?id=<?php echo $row['id']; ?>">Machinery Inspection</a></li>
                                    </ul>
                                </div>

                                <button type="button" class="btn btn-outline-info btn-sm"
                                    data-bs-toggle="tooltip" title="Vessel Summary"
                                    onclick="window.location.href='vessel_summary.php?id=<?php echo $row['id']; ?>'">
                                    <i class="material-icons">list_alt</i>
                                </button>

                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="tooltip" title="Print Form"
                                    onclick="window.location.href='printrhform.php?id=<?php echo $row['id']; ?>'">
                                    <i class="material-icons">print</i>
                                </button>

                                <!-- Reports Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-outline-danger btn-sm dropdown-toggle" type="button" id="dropdownReports2"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true" title="Reports">
                                    <i class="material-icons">report</i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownReports2">
                                    <li><a class="dropdown-item" href="compliance_report_for_inspection_ancillary.php?id=<?php echo $row['id']; ?>">Compliance Report for Ancillary</a></li>
                                    <li><a class="dropdown-item" href="vessel_list_rh_details.php?id=<?php echo $row['id']; ?>">Running Hours List</a></li>
                                    <li><a class="dropdown-item" href="status_page.php?id=<?php echo $row['id']; ?>">Running Hours Status and Maintenance Task</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back & Weekly Reports -->
                <div class="d-flex gap-2 mb-4">
                    <a class="btn btn-secondary btn-sm" href="index.php"><i class="material-icons align-middle">arrow_back</i> Back</a>
                    <?php if (in_array($_SESSION['role'], ['SuperAdmin', 'Admin', 'SuperUser', 'User'])): ?>
                    <button class="btn btn-primary btn-sm" onclick="openWeeklyReportModal(<?php echo htmlspecialchars($row['id']); ?>)">
                        📂 Upload / View Weekly Reports
                    </button>
                    <?php endif; ?>
                </div>

            </div>
            
            <div class="col-lg-6">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <tbody>
                            <tr><td><strong>Vessel Name</strong></td><td><?php echo $row['vessel_name']; ?></td></tr>
                            <tr><td><strong>Official Number</strong></td><td><?php echo $row['official_number']; ?></td></tr>
                            <tr><td><strong>Owner</strong></td><td><?php echo $row['owner']; ?></td></tr>
                            <tr><td><strong>IMO Number</strong></td><td><?php echo $row['IMO_number']; ?></td></tr>
                            <tr><td><strong>Classification Number</strong></td><td><?php echo $row['classification_number']; ?></td></tr>
                            <tr><td><strong>Ship Type</strong></td><td><?php echo $row['ship_type']; ?></td></tr>
                            <tr><td><strong>Home Port</strong></td><td><?php echo $row['home_port']; ?></td></tr>
                            <tr><td><strong>Gross Tonnage</strong></td><td><?php echo $row['gross_tonnage']; ?></td></tr>
                            <tr><td><strong>Net Tonnage</strong></td><td><?php echo $row['net_tonnage']; ?></td></tr>
                            <tr><td><strong>Bollard Pull</strong></td><td><?php echo $row['bollard_pull']; ?></td></tr>
                            <tr><td><strong>Length Overall</strong></td><td><?php echo $row['length_overall']; ?></td></tr>
                            <tr><td><strong>Breadth</strong></td><td><?php echo $row['breadth']; ?></td></tr>
                            <tr><td><strong>Depth</strong></td><td><?php echo $row['depth']; ?></td></tr>
                            <tr><td><strong>Year Built</strong></td><td><?php echo $row['year_built']; ?></td></tr>
                            <tr><td><strong>Main Engine Make</strong></td><td><?php echo $row['main_engine_make']; ?></td></tr>
                            <tr><td><strong>Main Engine Model</strong></td><td><?php echo $row['main_engine_model']; ?></td></tr>
                            <tr><td><strong>Main Engine Number</strong></td><td><?php echo $row['main_engine_number']; ?></td></tr>
                            <tr><td><strong>Engine Power</strong></td><td><?php echo $row['engine_power']; ?></td></tr>
                            <tr><td><strong>Auxiliary Engine Make</strong></td><td><?php echo $row['aux_engine_make']; ?></td></tr>
                            <tr><td><strong>Auxiliary Engine Model</strong></td><td><?php echo $row['aux_engine_model']; ?></td></tr>
                            <tr><td><strong>Auxiliary Engine Number</strong></td><td><?php echo $row['aux_engine_number']; ?></td></tr>
                            <tr><td><strong>Auxiliary Engine Power</strong></td><td><?php echo $row['aux_engine_power']; ?></td></tr>
                            <tr><td><strong>Portable Generator</strong></td><td><?php echo $row['aux_make']; ?></td></tr>
                            <tr><td><strong>Gearbox Make</strong></td><td><?php echo $row['gearbox_make']; ?></td></tr>
                            <tr><td><strong>Gearbox Model</strong></td><td><?php echo $row['gearbox_model']; ?></td></tr>
                            <tr><td><strong>Drive</strong></td><td><?php echo $row['drive']; ?></td></tr>
                            <tr><td><strong>Flag</strong></td><td><?php echo $row['flag']; ?></td></tr>
                            <tr><td><strong>Builder</strong></td><td><?php echo $row['builder']; ?></td></tr>
                            <tr><td><strong>Trading Area</strong></td><td><?php echo $row['trading_area']; ?></td></tr>
                            <tr><td><strong>Hull Material</strong></td><td><?php echo $row['hull_material']; ?></td></tr>
                            <tr><td><strong>Max Speed</strong></td><td><?php echo $row['max_speed']; ?></td></tr>
                            <tr><td><strong>Insurance</strong></td><td><?php echo $row['insurance']; ?></td></tr>
                            <tr>
                                <td><strong>ISPS Compliance</strong></td>
                                <td><input class="form-check-input" type="checkbox" <?php if ($row['ISPS_compliance'] == 1) echo 'checked'; ?> disabled></td>
                            </tr>
                            <tr>
                                <td><strong>ISO 9001:2015</strong></td>
                                <td><input class="form-check-input" type="checkbox" <?php if ($row['ISO_9001_2015'] == 1) echo 'checked'; ?> disabled></td>
                            </tr>
                            <tr>
                                <td><strong>Superintendent</strong></td>
                                <td>
                                    <?php 
                                    if (!empty($row['superintendent_fullname']) && !empty($row['superintendent_email'])) {
                                        echo $row['superintendent_fullname'] . ' (' . $row['superintendent_email'] . ')';
                                    } elseif (!empty($row['superintendent_fullname'])) {
                                        echo $row['superintendent_fullname'];
                                    } else {
                                        echo "No superintendent assigned.";
                                    }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- <div id="nonCompliantModal" class="modal"> -->
    <div class="modal fade" id="nonCompliantModal" tabindex="-1" aria-labelledby="nonCompliantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="nonCompliantModalLabel">Non-Compliant Items</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Inspection Type</th>
                            <th>PIC</th>
                            <th class="thstatus">Last Inspection Date</th>
                            <th class="thstatus">Next Inspection Date</th>
                            <th class="thstatus">Status</th>
                            </tr>
                        </thead>
                        <tbody id="nonCompliantTableBody">
                            <?php $counter = 1; foreach ($jsonItems as $item): ?>
                            <tr class="nc-clickable"
                                style="cursor:pointer;"
                                data-vessel-id="<?= htmlspecialchars($item['vessel_id']) ?>"
                                data-equipment-id="<?= htmlspecialchars($item['equipment_id']) ?>"
                                data-inspection-meta-id="<?= htmlspecialchars($item['inspection_meta_id']) ?>"
                                data-item-name="<?= htmlspecialchars($item['name']) ?>"
                                data-inspection-type="<?= htmlspecialchars($item['type']) ?>"
                                data-person-in-charge="<?= htmlspecialchars($item['person_in_charge']) ?>"
                                data-date1="<?= htmlspecialchars($item['date1']) ?>"
                                data-date2="<?= htmlspecialchars($item['date2']) ?>"
                                data-status="<?= htmlspecialchars($item['status']) ?>"
                            >

                                <td><?php echo $counter++; ?></td> <!-- Echo and increment the counter -->
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['type']); ?></td>
                                <td><?php echo htmlspecialchars($item['person_in_charge']); ?></td>
                                <td class="tdate"><?php echo htmlspecialchars($item['date1']); ?></td>
                                <td class="tdate"><?php echo htmlspecialchars($item['date2']); ?></td>
                                <td class="vStatus"><?php echo htmlspecialchars($item['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Status Alert Modal -->
    <div class="modal fade" id="statusAlertModal" tabindex="-1" aria-labelledby="statusAlertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="statusAlertModalLabel">Status Alerts</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Equipment Name</th>
                        <th>Component Name</th>
                        <th>Task</th>
                        <th class="thstatus">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; foreach ($statusItems as $item): ?>
                    <tr>
                            <td><?php echo $counter++; ?></td> <!-- Echo and increment the counter -->
                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['equipment_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['component_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['task_description']); ?></td>
                            <td class="rhStatus 
                                <?php echo ($item['status'] == 'Over Due') ? 'over-due' : ''; ?>
                                <?php echo ($item['status'] == 'Due Soon') ? 'due-soon' : ''; ?>">
                                <?php echo htmlspecialchars($item['status']); ?>
                            </td>
                            <!-- <td class="vStatus"><?php echo htmlspecialchars($item['status']); ?></td> -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>


    <!-- Weekly Report Modal -->
    <div class="modal fade" id="weeklyReportModal" tabindex="-1" aria-labelledby="weeklyReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="weeklyReportModalLabel">Weekly Reports</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_vessel_id">
                <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superuser'): ?>
                <form id="uploadForm" enctype="multipart/form-data" class="mb-3">
                    <input type="hidden" name="vessel_id" id="modal_vessel_id_form" value="<?php echo htmlspecialchars($row['id']); ?>">
                    <div class="input-group mb-2">
                    <input type="file" class="form-control" name="weekly_report" required>
                    <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
                <div id="uploadStatus"></div>
                <?php endif; ?>
                <table id="weeklyReportsTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                    <th>#</th>
                    <th>File Name</th>
                    <th>Uploaded By</th>
                    <th>Upload Date</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic Content Here -->
                </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>


    <!-- Expiring Certificates Modal -->
    <div class="modal fade" id="expiringCertificatesModal" tabindex="-1" aria-labelledby="expiringCertificatesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="expiringCertificatesModalLabel">⚠️ Expiring & Expired Certificates</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>These certificates are either <strong>Expiring Soon (≤ 60 days left)</strong> or <strong>Expired</strong>:</p>
                <table class="table table-hover align-middle">
                <thead>
                    <tr>
                    <th>#</th>
                    <th>Certificate Name</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; foreach ($expiring_certificates as $cert): ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($cert['CertificateName']); ?></td>
                        <td><?php echo htmlspecialchars($cert['ExpiryDate']); ?></td>
                        <td class="<?php echo ($cert['Status'] == 'Expired') ? 'text-danger fw-bold' : 'text-warning fw-bold'; ?>">
                        <?php echo htmlspecialchars($cert['Status']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>

    <!-- Inspection Entry Modal -->
	<div class="modal fade" id="inspectionEntryModal" tabindex="-1" aria-labelledby="inspectionEntryModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-md modal-dialog-centered">
			<div class="modal-content">
			<form id="inspectionEntryForm">
				<div class="modal-header">
				<h5 class="modal-title" id="inspectionEntryModalLabel">Enter Inspection Date</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
				<input type="hidden" id="ie_vessel_id" name="vessel_id">
				<input type="hidden" id="ie_equipment_id" name="equipment_id">
				<input type="hidden" id="ie_inspection_meta_id" name="inspection_meta_id">
				<div class="mb-2">
					<label class="form-label mb-0"><b>Item</b></label>
					<input type="text" class="form-control" id="ie_item_name" readonly>
				</div>
				<div class="mb-2">
					<label class="form-label mb-0"><b>Inspection Type</b></label>
					<input type="text" class="form-control" id="ie_inspection_type" readonly>
				</div>
				<div class="mb-2">
					<label class="form-label mb-0"><b>PIC</b></label>
					<input type="text" class="form-control" id="ie_pic" readonly>
				</div>
				<div class="mb-2">
					<label class="form-label mb-0"><b>Inspection Date</b></label>
					<input type="date" class="form-control" id="ie_inspection_date" name="date" required>
				</div>
				<div class="mb-2">
					<label class="form-label mb-0"><b>Remarks</b></label>
					<input type="text" class="form-control" id="ie_remarks" name="remarks">
				</div>
				<div class="mb-2 form-check">
					<input type="checkbox" class="form-check-input" id="ie_needs_repair" name="needs_repair" value="1">
					<label class="form-check-label" for="ie_needs_repair">Needs Repair</label>
				</div>
				<div id="inspectionEntryFeedback" class="mt-2"></div>
				</div>
				<div class="modal-footer">
				<button type="submit" class="btn btn-success">Save</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
			</div>
		</div>
	</div>


    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
    <script src="scripts/add_equipment.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'toastr_handler.php'; ?>
    <script src="scripts/header.js" defer></script>

    
    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            // --- TOOLTIP CODE ---
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // --- CHAINED MODALS CODE ---
            var showNonCompliant = <?php echo !empty($allNonCompliantItems) ? 'true' : 'false'; ?>;
            var showStatusAlert  = <?php echo !empty($statusItems) ? 'true' : 'false'; ?>;
            var showExpiringCert = <?php echo !empty($expiring_certificates) ? 'true' : 'false'; ?>;

            var nonCompliantModal = document.getElementById('nonCompliantModal');
            var statusAlertModal = document.getElementById('statusAlertModal');
            var expiringModal = document.getElementById('expiringCertificatesModal');

            var modalSequence = [];
            if (showNonCompliant) modalSequence.push(nonCompliantModal);
            if (showStatusAlert)  modalSequence.push(statusAlertModal);
            if (showExpiringCert) modalSequence.push(expiringModal);

            function showNextModal(index) {
                if (index < modalSequence.length) {
                    var modalInstance = new bootstrap.Modal(modalSequence[index]);
                    modalInstance.show();

                    modalSequence[index].addEventListener('hidden.bs.modal', function handler() {
                        modalSequence[index].removeEventListener('hidden.bs.modal', handler);
                        showNextModal(index + 1);
                    });
                }
            }
            if (modalSequence.length > 0) {
                showNextModal(0);
            }

            // --- UPLOAD FORM HANDLER ---
            let uploadForm = document.getElementById('uploadForm');
            if (!uploadForm) {
                console.warn("⚠️ Warning: Upload form not found! Hindi ka admin o superuser.");
                return; // Iwas error kapag user lang ang role
            }

            uploadForm.addEventListener('submit', function(event) {
                event.preventDefault();

                let formData = new FormData(uploadForm);

                fetch('upload_weekly_report.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    let uploadStatus = document.getElementById('uploadStatus');
                    if (data.trim() === 'success') {
                        uploadStatus.innerHTML = "<div class='alert alert-success p-2 mb-2'>✅ Upload successful!</div>";
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        uploadStatus.innerHTML = "<div class='alert alert-danger p-2 mb-2'>❌ " + data + "</div>";
                    }
                })
                .catch(error => console.error('❌ Error:', error));
            });

            // --- (ILAGAY MO RIN DITO LAHAT NG IBA MO PANG DOM READY CODE) ---

            // Gawing clickable ang bawat non-compliant row
			document.querySelectorAll('#nonCompliantTableBody .nc-clickable').forEach(function(row) {
				row.addEventListener('click', function() {
					// Kunin lahat ng data attributes
					document.getElementById('ie_vessel_id').value = this.getAttribute('data-vessel-id');
					document.getElementById('ie_equipment_id').value = this.getAttribute('data-equipment-id');
					document.getElementById('ie_inspection_meta_id').value = this.getAttribute('data-inspection-meta-id');
					document.getElementById('ie_item_name').value = this.getAttribute('data-item-name');
					document.getElementById('ie_inspection_type').value = this.getAttribute('data-inspection-type');
					document.getElementById('ie_pic').value = this.getAttribute('data-person-in-charge');
					document.getElementById('ie_inspection_date').value = '';
					document.getElementById('ie_remarks').value = '';
					document.getElementById('ie_needs_repair').checked = false;
					document.getElementById('inspectionEntryFeedback').innerHTML = '';

					var inspectionEntryModal = new bootstrap.Modal(document.getElementById('inspectionEntryModal'));
					inspectionEntryModal.show();
				});
			});

			// Submit handler for inspection entry
			document.getElementById('inspectionEntryForm').addEventListener('submit', function(e) {
				e.preventDefault();
				var feedback = document.getElementById('inspectionEntryFeedback');
				feedback.innerHTML = '';

				// Gather data
				var data = {
					vessel_id: document.getElementById('ie_vessel_id').value,
					equipment_id: document.getElementById('ie_equipment_id').value,
					inspection_meta_id: document.getElementById('ie_inspection_meta_id').value,
					date: document.getElementById('ie_inspection_date').value,
					remarks: document.getElementById('ie_remarks').value,
					needs_repair: document.getElementById('ie_needs_repair').checked ? 1 : 0
				};

				// AJAX request
				fetch('save_inspection_date.php', {
					method: 'POST',
					headers: {'Content-Type': 'application/json'},
					body: JSON.stringify(data)
				})
				.then(response => response.json())
				.then(res => {
					if(res.status === "success") {
						feedback.innerHTML = "<div class='alert alert-success p-2 mb-2'>Inspection saved!</div>";
						setTimeout(function() { location.reload(); }, 1000);
					} else if(res.status === "duplicate") {
						feedback.innerHTML = "<div class='alert alert-warning p-2 mb-2'>Duplicate: " + res.message + "</div>";
					} else {
						feedback.innerHTML = "<div class='alert alert-danger p-2 mb-2'>" + res.message + "</div>";
					}
				})
				.catch(error => {
					feedback.innerHTML = "<div class='alert alert-danger p-2 mb-2'>Error: " + error + "</div>";
				});
			});


        });



        function openWeeklyReportModal(vesselId) {
            console.log("Debug: Fetching weekly reports for vessel_id =", vesselId);

            let modalElement = document.getElementById('weeklyReportModal');
            if (!modalElement) {
                console.error("❌ Error: weeklyReportModal not found in DOM!");
                return;
            }

            let modalInput = document.getElementById('modal_vessel_id');
            if (modalInput) {
                modalInput.value = vesselId;
            } else {
                console.warn("⚠️ Warning: modal_vessel_id input field not found! Skipping value update.");
            }

            fetch('fetch_weekly_reports.php?vessel_id=' + vesselId)
                .then(response => response.json())
                .then(data => {
                    console.log("Debug: Response Data =", data);
                    let tableBody = document.querySelector("#weeklyReportsTable tbody");

                    if (!tableBody) {
                        console.error("❌ Error: Table body element (#weeklyReportsTable tbody) not found!");
                        return;
                    }

                    tableBody.innerHTML = ""; // Clear previous content

                    if (data.length === 0) {
                        tableBody.innerHTML = "<tr><td colspan='5'>No reports found</td></tr>";
                    } else {
                        data.forEach((report, index) => {
                            let newRow = document.createElement("tr");

                            let tdIndex = document.createElement("td");
                            tdIndex.textContent = index + 1;
                            newRow.appendChild(tdIndex);

                            let tdFileName = document.createElement("td");
                            tdFileName.textContent = report.file_name;
                            newRow.appendChild(tdFileName);

                            let tdFullname = document.createElement("td");
                            tdFullname.textContent = report.fullname;
                            newRow.appendChild(tdFullname);

                            let tdUploadDate = document.createElement("td");
                            tdUploadDate.textContent = report.upload_date;
                            newRow.appendChild(tdUploadDate);

                            let tdAction = document.createElement("td");

                            // Download button
                            let downloadButton = document.createElement("a");
                            downloadButton.href = report.file_path;
                            downloadButton.setAttribute("download", "");
                            downloadButton.className = "btn btn-sm btn-primary me-1";
                            downloadButton.innerHTML = '<i class="material-icons align-middle">file_download</i> Download';
                            tdAction.appendChild(downloadButton);

                            // DELETE BUTTON (visible only for admin/superuser)
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superuser'): ?>
                            let deleteButton = document.createElement("button");
                            deleteButton.className = "btn btn-sm btn-danger";
                            deleteButton.innerHTML = '<i class="material-icons align-middle">delete</i> Delete';
                            deleteButton.onclick = function() {
                                if (confirm("Are you sure you want to delete this report?")) {
                                    fetch('delete_weekly_report.php', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                        body: 'report_id=' + encodeURIComponent(report.id)
                                    })
                                    .then(response => response.text())
                                    .then(data => {
                                        if (data.trim() === 'success') {
                                            newRow.remove();
                                        } else {
                                            alert("Failed to delete: " + data);
                                        }
                                    });
                                }
                            };
                            tdAction.appendChild(deleteButton);
                            <?php endif; ?>

                            newRow.appendChild(tdAction);
                            tableBody.appendChild(newRow);
                        });

                    }

                    // BOOTSTRAP 5 MODAL: Open Modal
                    let modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modalInstance.show();
                })
                .catch(error => console.error("❌ Error Fetching Reports:", error));
        }
    </script>
</body>
</html>
