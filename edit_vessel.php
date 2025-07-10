<?php
include('session_config.php');
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

// Fetch vessel details if id is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM vessels WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $vessel = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    header("Location: vessel.php");
    exit;
}

// Fetch all superusers who are also marked as superintendents
$superintendent_query = "SELECT user_id, email FROM users WHERE role = 'superuser' AND is_superintendent = 1";
$superintendent_result = $conn->query($superintendent_query);

// Fetch current superintendent
$current_superintendent_id = $vessel['superintendent_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $superintendent_id = $_POST['superintendent_id'];
    $vessel_name = $_POST['vessel_name'];
    $official_number = $_POST['official_number'];
    $owner = $_POST['owner'];
    $IMO_number = $_POST['imo_number'];
    $classification_number = $_POST['classification_number'];
    $ship_type = $_POST['ship_type'];
    $home_port = $_POST['home_port'];
    $gross_tonnage = $_POST['gross_tonnage'];
    $net_tonnage = $_POST['net_tonnage'];
    $bollard_pull = $_POST['bollard_pull'];
    $length_overall = $_POST['length_overall'];
    $breadth = $_POST['breadth'];
    $depth = $_POST['depth'];
    $year_built = $_POST['year_built'];
    $main_engine_make = $_POST['main_engine_make'];
    $main_engine_model = $_POST['main_engine_model'];
    $main_engine_number = $_POST['main_engine_number'];
    $engine_power = $_POST['engine_power'];
    $aux_engine_make = $_POST['aux_engine_make'];
    $aux_make = $_POST['aux_make'];
    $aux_engine_model = $_POST['aux_engine_model'];
    $aux_engine_number = $_POST['aux_engine_number'];
    $aux_engine_power = $_POST['aux_engine_power'];
    $gearbox_make = $_POST['gearbox_make'];
    $gearbox_model = $_POST['gearbox_model'];
    $flag = $_POST['flag'];
    $builder = $_POST['builder'];
    $trading_area = $_POST['trading_area'];
    $hull_material = $_POST['hull_material'];
    $drive = $_POST['drive'];
    $max_speed = $_POST['max_speed'];
    $insurance = $_POST['insurance'];
    $isps_compliance = isset($_POST['ISPS_compliance']) ? 1 : 0;
    $iso_9001_2015 = isset($_POST['ISO_9001_2015']) ? 1 : 0;

    if (isset($_FILES['imahe']) && $_FILES['imahe']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024;
        if (!is_valid_image($_FILES['imahe']['tmp_name'], $_FILES['imahe']['name'], $allowed_types, $allowed_exts, $max_size)) {
            $_SESSION['toastMessage'] = "Invalid image file! Please upload a valid image only (JPEG, PNG, GIF, WEBP).";
            $_SESSION['toastType'] = "error";
            header("Location: edit_vessel.php?id=$id");
            exit;
        }
        $dir_upload = 'uploads/';
        $ext = strtolower(pathinfo($_FILES['imahe']['name'], PATHINFO_EXTENSION));
        $new_image_name = time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $complete_image_path = $dir_upload . $new_image_name;
        if (move_uploaded_file($_FILES['imahe']['tmp_name'], $complete_image_path)) {
            $imahe = $complete_image_path;
        } else {
            $_SESSION['toastMessage'] = "Upload failed!";
            $_SESSION['toastType'] = "error";
            header("Location: edit_vessel.php?id=$id");
            exit;
        }
    } else {
        $imahe = $vessel['imahe'];
    }

    $sql = "UPDATE vessels 
        SET vessel_name=?, official_number=?, owner=?, flag=?, year_built=?, builder=?, IMO_number=?, classification_number=?, trading_area=?, ship_type=?, home_port=?, gross_tonnage=?, net_tonnage=?, bollard_pull=?, hull_material=?, length_overall=?, breadth=?, depth=?, main_engine_make=?, engine_power=?, main_engine_model=?, main_engine_number=?, aux_engine_make=?, aux_engine_model=?, aux_engine_number=?, aux_engine_power=?, aux_make=?, gearbox_make=?, gearbox_model=?, drive=?, max_speed=?, insurance=?, ISPS_compliance=?, ISO_9001_2015=?, imahe=?, superintendent_id=? 
        WHERE id=?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("sssssssssssddssdddssssssssssssdssssii", 
        $vessel_name, $official_number, $owner, $flag, $year_built, $builder, $IMO_number, $classification_number, $trading_area, $ship_type, $home_port, $gross_tonnage, $net_tonnage, $bollard_pull, $hull_material, $length_overall, $breadth, $depth, $main_engine_make, $engine_power, $main_engine_model, $main_engine_number, $aux_engine_make, $aux_engine_model, $aux_engine_number, $aux_engine_power, $aux_make, $gearbox_make, $gearbox_model, $drive, $max_speed, $insurance, $isps_compliance, $iso_9001_2015, $imahe, $superintendent_id, $id
    );

    if ($stmt->execute()) {
        $_SESSION['toastMessage'] = "Vessel details updated successfully!";
        $_SESSION['toastType'] = "success";
        header("Location: vessel_details.php?id=$id");
        exit;
    } else {
        $_SESSION['toastMessage'] = "Error updating vessel details: " . $stmt->error;
        $_SESSION['toastType'] = "error";
        header("Location: edit_vessel.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vessel | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        body { padding-bottom: 60px; }
        .img-preview-vessel { max-width:220px; max-height:160px; object-fit:cover; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
    <h4 class="mb-4">Edit Vessel Details</h4>
    <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between mb-3">
            <!-- <a href="dashboard.php" class="btn btn-secondary"><i class="fa fa-home"></i> Home</a> -->
            <a class="btn btn-outline-dark" href="vessel_details.php?id=<?php echo $id; ?>"><i class="fa fa-arrow-left"></i> Back to Details</a>
        </div>
        <?php if (!empty($vessel['imahe'])): ?>
        <div class="mb-4 text-center">
            <img src="<?= htmlspecialchars($vessel['imahe']); ?>" class="img-preview-vessel rounded shadow" alt="Current Vessel Image">
            <div class="form-text">Current Vessel Image</div>
        </div>
        <?php endif; ?>
        <form action="edit_vessel.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <?php // COLUMN 1 ?>
                    <div class="mb-2">
                        <label for="vessel_name" class="form-label">Vessel Name</label>
                        <input type="text" class="form-control" id="vessel_name" name="vessel_name" value="<?= htmlspecialchars($vessel['vessel_name']); ?>" required>
                    </div>
                    <div class="mb-2">
                        <label for="official_number" class="form-label">Official Number</label>
                        <input type="text" class="form-control" id="official_number" name="official_number" value="<?= htmlspecialchars($vessel['official_number']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="owner" class="form-label">Owner</label>
                        <input type="text" class="form-control" id="owner" name="owner" value="<?= htmlspecialchars($vessel['owner']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="imo_number" class="form-label">IMO Number</label>
                        <input type="text" class="form-control" id="imo_number" name="imo_number" value="<?= htmlspecialchars($vessel['IMO_number']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="classification_number" class="form-label">Classification Number</label>
                        <input type="text" class="form-control" id="classification_number" name="classification_number" value="<?= htmlspecialchars($vessel['classification_number']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="ship_type" class="form-label">Ship Type</label>
                        <input type="text" class="form-control" id="ship_type" name="ship_type" value="<?= htmlspecialchars($vessel['ship_type']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="home_port" class="form-label">Home Port</label>
                        <input type="text" class="form-control" id="home_port" name="home_port" value="<?= htmlspecialchars($vessel['home_port']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="gross_tonnage" class="form-label">Gross Tonnage</label>
                        <input type="text" class="form-control" id="gross_tonnage" name="gross_tonnage" value="<?= htmlspecialchars($vessel['gross_tonnage']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="net_tonnage" class="form-label">Net Tonnage</label>
                        <input type="text" class="form-control" id="net_tonnage" name="net_tonnage" value="<?= htmlspecialchars($vessel['net_tonnage']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="bollard_pull" class="form-label">Bollard Pull</label>
                        <input type="text" class="form-control" id="bollard_pull" name="bollard_pull" value="<?= htmlspecialchars($vessel['bollard_pull']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="length_overall" class="form-label">Length Overall</label>
                        <input type="text" class="form-control" id="length_overall" name="length_overall" value="<?= htmlspecialchars($vessel['length_overall']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="breadth" class="form-label">Breadth</label>
                        <input type="text" class="form-control" id="breadth" name="breadth" value="<?= htmlspecialchars($vessel['breadth']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="depth" class="form-label">Depth</label>
                        <input type="text" class="form-control" id="depth" name="depth" value="<?= htmlspecialchars($vessel['depth']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="year_built" class="form-label">Year Built</label>
                        <input type="text" class="form-control" id="year_built" name="year_built" value="<?= htmlspecialchars($vessel['year_built']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="main_engine_make" class="form-label">Main Engine Make</label>
                        <input type="text" class="form-control" id="main_engine_make" name="main_engine_make" value="<?= htmlspecialchars($vessel['main_engine_make']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="main_engine_model" class="form-label">Main Engine Model</label>
                        <input type="text" class="form-control" id="main_engine_model" name="main_engine_model" value="<?= htmlspecialchars($vessel['main_engine_model']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="main_engine_number" class="form-label">Main Engine Number</label>
                        <input type="text" class="form-control" id="main_engine_number" name="main_engine_number" value="<?= htmlspecialchars($vessel['main_engine_number']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="engine_power" class="form-label">Engine Power</label>
                        <input type="text" class="form-control" id="engine_power" name="engine_power" value="<?= htmlspecialchars($vessel['engine_power']); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <?php // COLUMN 2 ?>
                    <div class="mb-2">
                        <label for="aux_engine_make" class="form-label">Auxiliary Engine Make</label>
                        <input type="text" class="form-control" id="aux_engine_make" name="aux_engine_make" value="<?= htmlspecialchars($vessel['aux_engine_make']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="aux_engine_model" class="form-label">Auxiliary Engine Model</label>
                        <input type="text" class="form-control" id="aux_engine_model" name="aux_engine_model" value="<?= htmlspecialchars($vessel['aux_engine_model']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="aux_engine_number" class="form-label">Auxiliary Engine Number</label>
                        <input type="text" class="form-control" id="aux_engine_number" name="aux_engine_number" value="<?= htmlspecialchars($vessel['aux_engine_number']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="aux_engine_power" class="form-label">Auxiliary Engine Power</label>
                        <input type="text" class="form-control" id="aux_engine_power" name="aux_engine_power" value="<?= htmlspecialchars($vessel['aux_engine_power']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="aux_make" class="form-label">Portable Generator</label>
                        <input type="text" class="form-control" id="aux_make" name="aux_make" value="<?= htmlspecialchars($vessel['aux_make']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="gearbox_make" class="form-label">Gearbox Make</label>
                        <input type="text" class="form-control" id="gearbox_make" name="gearbox_make" value="<?= htmlspecialchars($vessel['gearbox_make']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="gearbox_model" class="form-label">Gearbox Model</label>
                        <input type="text" class="form-control" id="gearbox_model" name="gearbox_model" value="<?= htmlspecialchars($vessel['gearbox_model']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="drive" class="form-label">Drive</label>
                        <input type="text" class="form-control" id="drive" name="drive" value="<?= htmlspecialchars($vessel['drive']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="flag" class="form-label">Flag</label>
                        <input type="text" class="form-control" id="flag" name="flag" value="<?= htmlspecialchars($vessel['flag']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="builder" class="form-label">Builder</label>
                        <input type="text" class="form-control" id="builder" name="builder" value="<?= htmlspecialchars($vessel['builder']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="trading_area" class="form-label">Trading Area</label>
                        <input type="text" class="form-control" id="trading_area" name="trading_area" value="<?= htmlspecialchars($vessel['trading_area']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="hull_material" class="form-label">Hull Material</label>
                        <input type="text" class="form-control" id="hull_material" name="hull_material" value="<?= htmlspecialchars($vessel['hull_material']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="max_speed" class="form-label">Maximum Speed</label>
                        <input type="text" class="form-control" id="max_speed" name="max_speed" value="<?= htmlspecialchars($vessel['max_speed']); ?>">
                    </div>
                    <div class="mb-2">
                        <label for="insurance" class="form-label">Insurance</label>
                        <input type="text" class="form-control" id="insurance" name="insurance" value="<?= htmlspecialchars($vessel['insurance']); ?>">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="ISPS_compliance" name="ISPS_compliance" <?= $vessel['ISPS_compliance'] == 1 ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="ISPS_compliance">ISPS Compliance</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="ISO_9001_2015" name="ISO_9001_2015" <?= $vessel['ISO_9001_2015'] == 1 ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="ISO_9001_2015">ISO 9001:2015</label>
                    </div>
                    <div class="mb-2">
                        <label for="superintendent" class="form-label">Assign Superintendent</label>
                        <select name="superintendent_id" id="superintendent" class="form-select">
                            <option value="">Select Superintendent</option>
                            <?php
                            mysqli_data_seek($superintendent_result, 0);
                            while ($row = $superintendent_result->fetch_assoc()) { ?>
                                <option value="<?php echo $row['user_id']; ?>" <?= ($row['user_id'] == $current_superintendent_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="imahe" class="form-label">Vessel Image (Optional)</label>
                        <input type="file" class="form-control" id="imahe" name="imahe" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button class="btn btn-primary" type="submit"><i class="fa fa-save me-1"></i>Update</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts/header.js" defer></script>
<?php include 'toastr_handler.php'; ?>
</body>
</html>
