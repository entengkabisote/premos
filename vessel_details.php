<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'db_connect.php';

$id = $_GET['id'] ?? null;

$sql = "
    SELECT v.*, u.email as superintendent_email, u.fullname as superintendent_fullname
    FROM vessels v
    LEFT JOIN users u ON v.superintendent_id = u.user_id
    WHERE v.id = ?
";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Error preparing statement: ' . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vessel Details | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        body { padding-bottom: 60px; }
        .vessel-image-big {
            width: 100%; max-width: 800px; height: 550px; object-fit: cover; border-radius: 8px;
            box-shadow: 0 2px 12px #0001; background: #eaeaea;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
    <h4 class="mb-4 text-center">Vessel Details</h4>

    <div class="card p-4 shadow-sm mb-4">
        <div class="row g-4">
            <!-- VESSEL IMAGE + BUTTONS -->
            <div class="col-lg-5 d-flex flex-column align-items-center">
                <img
                    src="<?= htmlspecialchars($row['imahe']) ?>"
                    class="vessel-image-big mb-3"
                    alt="Vessel Image">

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-1">
                    <a class="btn btn-success btn-sm px-4" href="vessel.php"><i class="bi bi-arrow-left"></i> Back</a>
                    <?php if (in_array(strtolower($_SESSION['role']), ['admin', 'superadmin'])): ?>

                        <a class="btn btn-outline-primary btn-sm px-4" href="edit_vessel.php?id=<?= $row['id'] ?>"><i class="bi bi-pencil"></i> Edit</a>
                        <form method="POST" action="delete_vessel.php?id=<?= $row['id'] ?>" onsubmit="return confirm('Are you sure?');">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm px-4">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                        <button id="populateEquipment" class="btn btn-outline-success btn-sm px-4"><i class="bi bi-plus-circle"></i> Populate Equipment</button>
                    <?php endif; ?>
                    <a class="btn btn-outline-dark btn-sm px-4" href="view_certificates.php?id=<?= $row['id'] ?>"><i class="bi bi-file-earmark-text"></i> View Certificates</a>
                </div>
            </div>

            <!-- DETAILS TABLE -->
            <div class="col-lg-7">
                <table class="table table-bordered table-striped align-middle">
                    <tbody>
                        <tr><th>Vessel Name</th><td><?= htmlspecialchars($row['vessel_name']) ?></td></tr>
                        <tr><th>Official Number</th><td><?= htmlspecialchars($row['official_number']) ?></td></tr>
                        <tr><th>Owner</th><td><?= htmlspecialchars($row['owner']) ?></td></tr>
                        <tr><th>IMO Number</th><td><?= htmlspecialchars($row['IMO_number']) ?></td></tr>
                        <tr><th>Classification Number</th><td><?= htmlspecialchars($row['classification_number']) ?></td></tr>
                        <tr><th>Ship Type</th><td><?= htmlspecialchars($row['ship_type']) ?></td></tr>
                        <tr><th>Home Port</th><td><?= htmlspecialchars($row['home_port']) ?></td></tr>
                        <tr><th>Gross Tonnage</th><td><?= htmlspecialchars($row['gross_tonnage']) ?></td></tr>
                        <tr><th>Net Tonnage</th><td><?= htmlspecialchars($row['net_tonnage']) ?></td></tr>
                        <tr><th>Bollard Pull</th><td><?= htmlspecialchars($row['bollard_pull']) ?></td></tr>
                        <tr><th>Length Overall</th><td><?= htmlspecialchars($row['length_overall']) ?></td></tr>
                        <tr><th>Breadth</th><td><?= htmlspecialchars($row['breadth']) ?></td></tr>
                        <tr><th>Depth</th><td><?= htmlspecialchars($row['depth']) ?></td></tr>
                        <tr><th>Year Built</th><td><?= htmlspecialchars($row['year_built']) ?></td></tr>
                        <tr><th>Main Engine Make</th><td><?= htmlspecialchars($row['main_engine_make']) ?></td></tr>
                        <tr><th>Main Engine Model</th><td><?= htmlspecialchars($row['main_engine_model']) ?></td></tr>
                        <tr><th>Main Engine Number</th><td><?= htmlspecialchars($row['main_engine_number']) ?></td></tr>
                        <tr><th>Engine Power</th><td><?= htmlspecialchars($row['engine_power']) ?></td></tr>
                        <tr><th>Auxiliary Engine Make</th><td><?= htmlspecialchars($row['aux_engine_make']) ?></td></tr>
                        <tr><th>Auxiliary Engine Model</th><td><?= htmlspecialchars($row['aux_engine_model']) ?></td></tr>
                        <tr><th>Auxiliary Engine Number</th><td><?= htmlspecialchars($row['aux_engine_number']) ?></td></tr>
                        <tr><th>Auxiliary Engine Power</th><td><?= htmlspecialchars($row['aux_engine_power']) ?></td></tr>
                        <tr><th>Portable Generator</th><td><?= htmlspecialchars($row['aux_make']) ?></td></tr>
                        <tr><th>Gearbox Make</th><td><?= htmlspecialchars($row['gearbox_make']) ?></td></tr>
                        <tr><th>Gearbox Model</th><td><?= htmlspecialchars($row['gearbox_model']) ?></td></tr>
                        <tr><th>Drive</th><td><?= htmlspecialchars($row['drive']) ?></td></tr>
                        <tr><th>Flag</th><td><?= htmlspecialchars($row['flag']) ?></td></tr>
                        <tr><th>Builder</th><td><?= htmlspecialchars($row['builder']) ?></td></tr>
                        <tr><th>Trading Area</th><td><?= htmlspecialchars($row['trading_area']) ?></td></tr>
                        <tr><th>Hull Material</th><td><?= htmlspecialchars($row['hull_material']) ?></td></tr>
                        <tr><th>Max Speed</th><td><?= htmlspecialchars($row['max_speed']) ?></td></tr>
                        <tr><th>Insurance</th><td><?= htmlspecialchars($row['insurance']) ?></td></tr>
                        <tr>
                            <th>ISPS Compliance</th>
                            <td><input class="form-check-input" type="checkbox" <?= ($row['ISPS_compliance'] == 1) ? 'checked' : '' ?> disabled></td>
                        </tr>
                        <tr>
                            <th>ISO 9001:2015</th>
                            <td><input class="form-check-input" type="checkbox" <?= ($row['ISO_9001_2015'] == 1) ? 'checked' : '' ?> disabled></td>
                        </tr>
                        <tr>
                            <th>Superintendent</th>
                            <td>
                            <?php 
                                if (!empty($row['superintendent_fullname']) && !empty($row['superintendent_email'])) {
                                    echo htmlspecialchars($row['superintendent_fullname']) . ' (' . htmlspecialchars($row['superintendent_email']) . ')';
                                } elseif (!empty($row['superintendent_fullname'])) {
                                    echo htmlspecialchars($row['superintendent_fullname']);
                                } else {
                                    echo "No superintendent assigned.";
                                }
                            ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- end .row -->
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'toastr_handler.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('populateEquipment')?.addEventListener('click', function() {
        var vesselId = <?= json_encode($row['id']) ?>;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "populate_specific_equipment.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (this.status === 200) {
                toastr.success("Equipment populated!");
            } else {
                toastr.error('Error populating equipment.');
            }
        };
        xhr.send("vesselId=" + vesselId);
    });
});
</script>
<script src="scripts/header.js" defer></script>
</body>
</html>
