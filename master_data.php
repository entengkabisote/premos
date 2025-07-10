<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Setup | Planned Maintenance System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="styles/dashboard.css"><!-- Optional uniformity -->

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🔧 Master Data Setup</h4>
        <a href="dashboard.php" class="btn btn-outline-secondary">🏠 Back to Dashboard</a>
    </div>

    <!-- Rank -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light">🧑‍✈️ Rank</div>
        <div class="card-body">
            <form id="rankForm" class="row g-2 mb-4">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="rankName" name="rankName" placeholder="Enter rank..." required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">Add Rank</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th>
                            <th>Rank Name</th>
                            <th style="width: 200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ranks = $conn->query("SELECT * FROM ranks ORDER BY rank_name ASC");
                        $counter = 1;
                        while ($row = $ranks->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($row['rank_name']) ?></td>
                            <td class="text-nowrap">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning edit-rank" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['rank_name']) ?>">✏️ Edit</button>
                                    <button class="btn btn-sm btn-danger delete-rank" data-id="<?= $row['id'] ?>">🗑️ Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Interval Setup -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light">📆 Interval Setup</div>
        <div class="card-body">
            <form id="intervalForm" class="row g-2 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="intervalName" placeholder="Enter interval name..." required>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" id="intervalDays" placeholder="Days (e.g. 30)" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary w-100">Add Interval</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Interval Name</th>
                            <th>Days</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        $intervals = $conn->query("SELECT * FROM interval_table ORDER BY days ASC");
                        while ($row = $intervals->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['days'] ?></td>
                            <td class="text-nowrap">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning edit-interval" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>" data-days="<?= $row['days'] ?>">✏️ Edit</button>
                                    <button class="btn btn-sm btn-danger delete-interval" data-id="<?= $row['id'] ?>">🗑️ Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Criticality Level -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light">🔥 Criticality Level</div>
        <div class="card-body">
            <form id="criticalityForm" class="row g-2 mb-4">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="criticalityLevel" placeholder="Enter level (e.g., High, Low)" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-danger w-100">Add Level</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Level</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        $crit = $conn->query("SELECT * FROM criticality_table ORDER BY id ASC");
                        while ($row = $crit->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($row['level']) ?></td>
                            <td class="text-nowrap">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning edit-criticality" data-id="<?= $row['id'] ?>" data-level="<?= htmlspecialchars($row['level']) ?>">✏️ Edit</button>
                                    <button class="btn btn-sm btn-danger delete-criticality" data-id="<?= $row['id'] ?>">🗑️ Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Equipment Type -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light">⚙️ Equipment Type</div>
        <div class="card-body">
            <form id="equipmentTypeForm" class="row g-2 mb-4">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="equipmentType" placeholder="Enter equipment type..." required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-success w-100">Add Type</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        $types = $conn->query("SELECT * FROM equipment_type ORDER BY type ASC");
                        while ($row = $types->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td class="text-nowrap">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning edit-equipment-type" data-id="<?= $row['id'] ?>" data-type="<?= htmlspecialchars($row['type']) ?>">✏️ Edit</button>
                                    <button class="btn btn-sm btn-danger delete-equipment-type" data-id="<?= $row['id'] ?>">🗑️ Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Ancillary Type -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light">🔧 Ancillary Type</div>
        <div class="card-body">
            <form id="ancillaryTypeForm" class="row g-2 mb-4">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="ancillaryType" placeholder="Enter ancillary type..." required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-dark w-100">Add Type</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        $types = $conn->query("SELECT * FROM ancillary_type ORDER BY type ASC");
                        while ($row = $types->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td class="text-nowrap">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning edit-ancillary-type" data-id="<?= $row['id'] ?>" data-type="<?= htmlspecialchars($row['type']) ?>">✏️ Edit</button>
                                    <button class="btn btn-sm btn-danger delete-ancillary-type" data-id="<?= $row['id'] ?>">🗑️ Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    <!-- Machinery Category -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-light">🛠️ Machinery Category</div>
        <div class="card-body">
            <form id="machineryCategoryForm" class="row g-2 mb-4">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="machineryCategory" placeholder="Enter machinery category..." required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary w-100">Add Category</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Category Name</th>
                            <th style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        $machineryCats = $conn->query("SELECT * FROM equipment_category ORDER BY category_name ASC");
                        while ($row = $machineryCats->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                            <td class="text-nowrap">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning edit-machinery-category" data-id="<?= $row['equipment_category_id'] ?>" data-name="<?= htmlspecialchars($row['category_name']) ?>">✏️ Edit</button>
                                    <button class="btn btn-sm btn-danger delete-machinery-category" data-id="<?= $row['equipment_category_id'] ?>">🗑️ Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>

<!-- JS scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>

<!-- Placeholder for JS handlers -->
<script src="scripts/master_data.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'toastr_handler.php'; ?>
<script src="scripts/header.js" defer></script>

<button onclick="scrollToTop()" id="topBtn" title="Go to top" class="btn btn-secondary position-fixed" style="bottom: 30px; right: 30px; display: none;">
    ⬆️ Top
</button>

<script>
    const topBtn = document.getElementById("topBtn");

    window.onscroll = () => {
        topBtn.style.display = (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100)
            ? "block" : "none";
    };

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

</body>
</html>
