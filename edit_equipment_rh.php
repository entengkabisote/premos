<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['role'] === 'SuperUser') {
    // Optional: Display read-only version, or
    // header("Location: machinery.php");
    // exit;
    $readonly = true;
} else {
    $readonly = false;
}

include "db_connect.php";

// Fetch all equipment categories for the modal dropdown
$categories = [];
$category_query = "SELECT equipment_category_id, category_name FROM equipment_category";
$category_result = $conn->query($category_query);
if ($category_result->num_rows > 0) {
    while ($cat_row = $category_result->fetch_assoc()) {
        $categories[] = $cat_row;
    }
}

if (isset($_GET['id'])) {
    $equipment_id = $_GET['id'];

    $query = "SELECT ec.category_name 
              FROM equipment_name en
              JOIN equipment_category ec ON en.equipment_category_id = ec.equipment_category_id
              WHERE en.equipment_name_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $equipment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $category_name = $row['category_name'];
        } else {
            echo "No matching category found for the equipment.";
            exit;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit;
    }
} else {
    echo "Equipment ID is not set.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $equipment_name = $_POST['equipment_name'];
    $equipment_category_id = $_POST['equipment_category_id'];
    $equipment_id = $_POST['equipment_id'];

    $stmt = $conn->prepare("UPDATE equipment_name SET equipment_name=?, equipment_category_id=? WHERE equipment_name_id=?");
    $stmt->bind_param("sii", $equipment_name, $equipment_category_id, $equipment_id);

    if ($stmt->execute()) {
        $_SESSION['toastMessage'] = "Equipment updated successfully!";
        $_SESSION['toastType'] = "success";
    } else {
        $_SESSION['toastMessage'] = "Error updating equipment: " . $conn->error;
        $_SESSION['toastType'] = "error";
    }
    header("Location: machinery.php");
    exit;

        $stmt->close();
    }

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM equipment_name WHERE equipment_name_id = $id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $equipment = $result->fetch_assoc();
        echo "<script>document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('selected_category_id').value = " . $equipment['equipment_category_id'] . ";
        });</script>";
    } else {
        die("Equipment not found with ID: $id");
    }
} else {
    die("ID is required to edit the equipment.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment | Planned Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        /* Make sure card looks exactly like equipment.php */
        .main-card {
            border-radius: 18px;
            border: 1.5px solid #eee;
            box-shadow: 0 2px 8px 0 #0001;
            background: #fff;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container py-4">
    <h4 class="mb-4 text-center">Edit Equipment</h4>
    <div class="card p-4 shadow-sm mb-4">
        <form action="" method="POST">
            <input type="hidden" name="equipment_id" value="<?php echo $equipment['equipment_name_id']; ?>">
            <div class="mb-3">
                <label for="equipment_name" class="form-label">Equipment Name</label>
                <!-- <input id="equipment_name" type="text" class="form-control" name="equipment_name" value="<?php echo htmlspecialchars($equipment['equipment_name']); ?>" required> -->
                <input id="equipment_name" type="text" class="form-control" name="equipment_name" value="<?php echo htmlspecialchars($equipment['equipment_name']); ?>" <?= $readonly ? 'readonly' : '' ?> required>
            </div>
            <div class="mb-3">
                <label for="current_category_name" class="form-label">Category</label>
                <div class="input-group">
                    <input type="text" id="current_category_name" class="form-control" value="<?php echo isset($category_name) ? htmlspecialchars($category_name) : ''; ?>" readonly>
                    <?php if ($_SESSION['role'] !== 'SuperUser'): ?>
                        <button type="button" class="btn btn-primary" id="edit_category_btn">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <input type="hidden" id="selected_category_id" name="equipment_category_id" value="">
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <?php if ($_SESSION['role'] !== 'SuperUser'): ?>
                    <button class="btn btn-success" type="submit" name="action">
                        <i class="fa fa-sync"></i> Update Equipment
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary"
                    onclick="location.href='add_component.php?equipment_name_id=<?php echo $id; ?>'">
                    <i class="fa fa-plus-circle"></i> Manage Components
                </button>
                <a href="machinery.php" class="btn btn-danger">
                    <i class="fa fa-times-circle"></i> Cancel
                </a>
            </div>


            <!-- <div class="d-flex gap-2 flex-wrap justify-content-end">
                <button class="btn btn-success" type="submit" name="action">
                    <i class="fa fa-sync"></i> Update Equipment
                </button>
                <button type="button" class="btn btn-secondary" onclick="location.href='add_component.php?equipment_name_id=<?php echo $id; ?>'">
                    <i class="fa fa-plus-circle"></i> Manage Components
                </button>
                <a href="machinery.php" class="btn btn-danger">
                    <i class="fa fa-times-circle"></i> Cancel
                </a>
            </div> -->
        </form>
    </div>
</div>


<!-- Bootstrap Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" onsubmit="return false;">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Choose Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select id="category_select" class="form-select" required>
                    <option value="" disabled selected>Select Category...</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo trim($category['equipment_category_id']); ?>">
                            <?php echo trim($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="save_category_btn" data-bs-dismiss="modal">Save</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'toastr_handler.php'; ?>
<script src="scripts/header.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedCategoryId = "<?php echo isset($equipment['equipment_category_id']) ? $equipment['equipment_category_id'] : ''; ?>";
    if (selectedCategoryId) {
        document.getElementById('selected_category_id').value = selectedCategoryId;
    }
});

document.getElementById('edit_category_btn').addEventListener('click', function() {
    var currentCategoryText = document.getElementById('current_category_name').value.trim();
    var select = document.getElementById('category_select');
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].text.trim() === currentCategoryText) {
            select.selectedIndex = i;
            break;
        }
    }
    var myModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    myModal.show();
});

document.getElementById('save_category_btn').addEventListener('click', function() {
    var select = document.getElementById('category_select');
    var selectedCategoryID = select.value;
    document.getElementById('selected_category_id').value = selectedCategoryID;
    var selectedCategoryText = select.options[select.selectedIndex].text;
    document.getElementById('current_category_name').value = selectedCategoryText;
});
</script>
</body>
</html>
