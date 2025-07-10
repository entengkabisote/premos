<?php
include('session_config.php');
include_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch superintendents
$superintendent_query = "SELECT user_id, email FROM users WHERE role = 'superuser' AND is_superintendent = 1";
$superintendent_result = $conn->query($superintendent_query);

$vessel_name = isset($_SESSION['vessel_name']) ? strtoupper($_SESSION['vessel_name']) : '';
$imo_number  = isset($_SESSION['imo_number']) ? strtoupper($_SESSION['imo_number']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF check
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['toastMessage'] = "Invalid CSRF token.";
        $_SESSION['toastType'] = "danger";
        header("Location: enter_vessel_detail.php");
        exit;
    }

    $superintendent_id = $_POST['superintendent_id'] ?? null;
    $vessel_name = strtoupper(trim($_POST['vessel_name']));
    $imo_number  = strtoupper(trim($_POST['imo_number']));
    $official_number = trim($_POST['official_number']);
    $owner = trim($_POST['owner']);
    $classification_number = trim($_POST['classification_number']);
    $ship_type = trim($_POST['ship_type']);
    $home_port = trim($_POST['home_port']);
    $gross_tonnage = trim($_POST['gross_tonnage']);
    $net_tonnage = trim($_POST['net_tonnage']);
    $bollard_pull = trim($_POST['bollard_pull']);
    $length_overall = trim($_POST['length_overall']);
    $breadth = trim($_POST['breadth']);
    $depth = trim($_POST['depth']);
    $year_built = trim($_POST['year_built']);
    $main_engine_make = trim($_POST['main_engine_make']);
    $main_engine_model = trim($_POST['main_engine_model']);
    $main_engine_number = trim($_POST['main_engine_number']);
    $engine_power = trim($_POST['engine_power']);
    $aux_engine_make = trim($_POST['aux_engine_make']);
    $aux_engine_model = trim($_POST['aux_engine_model']);
    $aux_engine_number = trim($_POST['aux_engine_number']);
    $aux_engine_power = trim($_POST['aux_engine_power']);
    $aux_make = trim($_POST['aux_make']);
    $gearbox_make = trim($_POST['gearbox_make']);
    $gearbox_model = trim($_POST['gearbox_model']);
    $flag = trim($_POST['flag']);
    $builder = trim($_POST['builder']);
    $trading_area = trim($_POST['trading_area']);
    $hull_material = trim($_POST['hull_material']);
    $drive = trim($_POST['drive']);
    $max_speed = trim($_POST['max_speed']);
    $insurance = trim($_POST['insurance']);
    $isps_compliance = isset($_POST['isps_compliance']) ? 1 : 0;
    $iso_9001_2015 = isset($_POST['iso_9001_2015']) ? 1 : 0;

    $imahe = "";
    if (isset($_FILES['imahe']) && $_FILES['imahe']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowed_exts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024;
        $tmp_name = $_FILES['imahe']['tmp_name'];
        $file_name = $_FILES['imahe']['name'];
        if (is_valid_image($tmp_name, $file_name, $allowed_types, $allowed_exts, $max_size)) {
            $dir_upload = 'uploads/';
            if (!is_dir($dir_upload)) mkdir($dir_upload, 0755, true);
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_image_name = uniqid('vessel_', true) . '.' . $ext;
            $imahe = $dir_upload . $new_image_name;
            if (!move_uploaded_file($tmp_name, $imahe)) {
                $_SESSION['toastMessage'] = "Failed to upload vessel image!";
                $_SESSION['toastType'] = "danger";
                header("Location: enter_vessel_detail.php");
                exit;
            }
        } else {
            $_SESSION['toastMessage'] = "Invalid file! (Type, size, or not a real image)";
            $_SESSION['toastType'] = "danger";
            header("Location: enter_vessel_detail.php");
            exit;
        }
    }

    $sql = "INSERT INTO vessels (vessel_name, official_number, owner, IMO_number, ship_type, classification_number, home_port, 
    gross_tonnage, net_tonnage, length_overall, breadth, depth, year_built, main_engine_make, main_engine_model, main_engine_number, 
    engine_power, drive, flag, builder, trading_area, hull_material, max_speed, insurance, ISPS_compliance, ISO_9001_2015, imahe, bollard_pull, 
    aux_engine_make, aux_engine_model, aux_engine_number, aux_engine_power, aux_make, gearbox_make, gearbox_model, superintendent_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['toastMessage'] = "Error preparing statement: " . $conn->error;
        $_SESSION['toastType'] = "danger";
        header("Location: enter_vessel_details.php");
        exit;
    }

    $stmt->bind_param(
        "sssssssdddddssssssssssdssssssssssssi",
        $vessel_name, $official_number, $owner, $imo_number, $ship_type, $classification_number, $home_port,
        $gross_tonnage, $net_tonnage, $length_overall, $breadth, $depth, $year_built, $main_engine_make, $main_engine_model, $main_engine_number,
        $engine_power, $drive, $flag, $builder, $trading_area, $hull_material, $max_speed, $insurance, $isps_compliance, $iso_9001_2015, $imahe, $bollard_pull,
        $aux_engine_make, $aux_engine_model, $aux_engine_number, $aux_engine_power, $aux_make, $gearbox_make, $gearbox_model, $superintendent_id
    );

    if ($stmt->execute()) {
        $_SESSION['toastMessage'] = "Vessel added successfully!";
        $_SESSION['toastType'] = "success";
        header("Location: vessel.php");
        exit;
    } else {
        $_SESSION['toastMessage'] = "Error: " . $stmt->error;
        $_SESSION['toastType'] = "danger";
        header("Location: enter_vessel_details.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vessel | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        body { padding-bottom: 60px; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'toastr_handler.php'; ?>

<div class="container py-4">
    <h4 class="mb-4 text-center">Add Vessel Details</h4>
    <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between mb-3">
            <a href="dashboard.php" class="btn btn-secondary"><i class="fa fa-home"></i> Home</a>
            <a href="vessel.php" class="btn btn-outline-dark"><i class="fa fa-ship"></i> Vessel List</a>
        </div>
        <form action="enter_vessel_details.php" method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="vessel_name" class="form-label">Vessel Name</label>
                    <input type="text" class="form-control" id="vessel_name" name="vessel_name" value="<?= htmlspecialchars($vessel_name); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="imo_number" class="form-label">IMO Number</label>
                    <input type="text" class="form-control" id="imo_number" name="imo_number" value="<?= htmlspecialchars($imo_number); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="owner" class="form-label">Owner</label>
                    <input type="text" class="form-control" id="owner" name="owner">
                </div>
                <div class="col-md-6">
                    <label for="official_number" class="form-label">Official Number</label>
                    <input type="text" class="form-control" id="official_number" name="official_number">
                </div>
                <div class="col-md-6">
                    <label for="ship_type" class="form-label">Ship Type</label>
                    <input type="text" class="form-control" id="ship_type" name="ship_type">
                </div>
                <div class="col-md-6">
                    <label for="classification_number" class="form-label">Classification Number</label>
                    <input type="text" class="form-control" id="classification_number" name="classification_number">
                </div>
                <div class="col-md-6">
                    <label for="home_port" class="form-label">Home Port</label>
                    <input type="text" class="form-control" id="home_port" name="home_port">
                </div>
                <div class="col-md-6">
                    <label for="gross_tonnage" class="form-label">Gross Tonnage</label>
                    <input type="text" class="form-control" id="gross_tonnage" name="gross_tonnage">
                </div>
                <div class="col-md-6">
                    <label for="net_tonnage" class="form-label">Net Tonnage</label>
                    <input type="text" class="form-control" id="net_tonnage" name="net_tonnage">
                </div>
                <div class="col-md-6">
                    <label for="bollard_pull" class="form-label">Bollard Pull</label>
                    <input type="text" class="form-control" id="bollard_pull" name="bollard_pull">
                </div>
                <div class="col-md-6">
                    <label for="length_overall" class="form-label">Length Overall</label>
                    <input type="text" class="form-control" id="length_overall" name="length_overall">
                </div>
                <div class="col-md-6">
                    <label for="breadth" class="form-label">Breadth</label>
                    <input type="text" class="form-control" id="breadth" name="breadth">
                </div>
                <div class="col-md-6">
                    <label for="depth" class="form-label">Depth</label>
                    <input type="text" class="form-control" id="depth" name="depth">
                </div>
                <div class="col-md-6">
                    <label for="year_built" class="form-label">Year Built</label>
                    <input type="text" class="form-control" id="year_built" name="year_built">
                </div>
                <div class="col-md-6">
                    <label for="main_engine_make" class="form-label">Main Engine Make</label>
                    <input type="text" class="form-control" id="main_engine_make" name="main_engine_make">
                </div>
                <div class="col-md-6">
                    <label for="main_engine_model" class="form-label">Main Engine Model</label>
                    <input type="text" class="form-control" id="main_engine_model" name="main_engine_model">
                </div>
                <div class="col-md-6">
                    <label for="main_engine_number" class="form-label">Main Engine Number</label>
                    <input type="text" class="form-control" id="main_engine_number" name="main_engine_number">
                </div>
                <div class="col-md-6">
                    <label for="engine_power" class="form-label">Engine Power</label>
                    <input type="text" class="form-control" id="engine_power" name="engine_power">
                </div>
                <div class="col-md-6">
                    <label for="aux_engine_make" class="form-label">Auxiliary Engine Make</label>
                    <input type="text" class="form-control" id="aux_engine_make" name="aux_engine_make">
                </div>
                <div class="col-md-6">
                    <label for="aux_engine_model" class="form-label">Auxiliary Engine Model</label>
                    <input type="text" class="form-control" id="aux_engine_model" name="aux_engine_model">
                </div>
                <div class="col-md-6">
                    <label for="aux_engine_number" class="form-label">Auxiliary Engine Number</label>
                    <input type="text" class="form-control" id="aux_engine_number" name="aux_engine_number">
                </div>
                <div class="col-md-6">
                    <label for="aux_engine_power" class="form-label">Auxiliary Engine Power</label>
                    <input type="text" class="form-control" id="aux_engine_power" name="aux_engine_power">
                </div>
                <div class="col-md-6">
                    <label for="aux_make" class="form-label">Portable Generator</label>
                    <input type="text" class="form-control" id="aux_make" name="aux_make">
                </div>
                <div class="col-md-6">
                    <label for="gearbox_make" class="form-label">Gearbox Make</label>
                    <input type="text" class="form-control" id="gearbox_make" name="gearbox_make">
                </div>
                <div class="col-md-6">
                    <label for="gearbox_model" class="form-label">Gearbox Model</label>
                    <input type="text" class="form-control" id="gearbox_model" name="gearbox_model">
                </div>
                <div class="col-md-6">
                    <label for="drive" class="form-label">Drive</label>
                    <input type="text" class="form-control" id="drive" name="drive">
                </div>
                <div class="col-md-6">
                    <label for="flag" class="form-label">Flag</label>
                    <input type="text" class="form-control" id="flag" name="flag">
                </div>
                <div class="col-md-6">
                    <label for="builder" class="form-label">Builder</label>
                    <input type="text" class="form-control" id="builder" name="builder">
                </div>
                <div class="col-md-6">
                    <label for="trading_area" class="form-label">Trading Area</label>
                    <input type="text" class="form-control" id="trading_area" name="trading_area">
                </div>
                <div class="col-md-6">
                    <label for="hull_material" class="form-label">Hull Material</label>
                    <input type="text" class="form-control" id="hull_material" name="hull_material">
                </div>
                <div class="col-md-6">
                    <label for="max_speed" class="form-label">Maximum Speed</label>
                    <input type="text" class="form-control" id="max_speed" name="max_speed">
                </div>
                <div class="col-md-6">
                    <label for="insurance" class="form-label">Insurance</label>
                    <input type="text" class="form-control" id="insurance" name="insurance">
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="isps_compliance" name="isps_compliance">
                        <label class="form-check-label" for="isps_compliance">ISPS Compliance</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="iso_9001_2015" name="iso_9001_2015">
                        <label class="form-check-label" for="iso_9001_2015">ISO 9001:2015</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="imahe" class="form-label">Vessel Image</label>
                    <input class="form-control" type="file" id="imahe" name="imahe" accept="image/*">
                </div>
                <div class="col-md-6">
                    <label for="superintendent" class="form-label">Assign Superintendent</label>
                    <select class="form-select" name="superintendent_id" id="superintendent">
                        <option value="" disabled selected>Select Superintendent</option>
                        <?php
                        mysqli_data_seek($superintendent_result, 0);
                        while ($row = $superintendent_result->fetch_assoc()) { ?>
                            <option value="<?php echo $row['user_id']; ?>"><?php echo htmlspecialchars($row['email']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-12 mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i> Add Vessel</button>
                    <a href="vessel.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
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
</body>
</html>
