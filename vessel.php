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
   <title>Ships | Planned Maintenance System</title>
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
      <h4 class="mb-4">Vessel Management Dashboard</h4>
      <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between mb-3">
            <a href="dashboard.php" class="btn btn-secondary">🏠 Home</a>
        </div>
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'SuperAdmin')) { ?>
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                <h5>Enter New Vessel</h5>
                <form action="check_vessel.php" method="post">
                    <div class="mb-3">
                        <label for="vessel_name" class="form-label">Vessel Name</label>
                        <input type="text" id="vessel_name" name="vessel_name" class="form-control" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label for="imo_number" class="form-label">IMO Number</label>
                        <input type="text" id="imo_number" name="imo_number" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Check</button>
                </form>
                </div>
                <div class="col-md-6">
                <h5>Existing Vessels</h5>
                <div class="vessels-list">
                    <?php include 'existing_vessels.php'; ?>
                </div>
                </div>
            </div> 
        <?php } else { ?>
         <div class="row">
            <div class="col-12">
               <h5>Existing Vessels</h5>
               <div class="vessels-list">
                  <?php include 'existing_vessels.php'; ?>
               </div>
            </div>
         </div>
         <?php } ?>
      </div>
   </div>

   <?php include 'footer.php'; ?>

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
   <script src="scripts/toastr_settings.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <?php include 'toastr_handler.php'; ?>
   <script src="scripts/header.js" defer></script>
   <?php if (isset($_SESSION['toastMessage'])): ?>
   <script>
       document.addEventListener('DOMContentLoaded', function () {
           var toastEl = document.querySelector('.toast');
           if (toastEl) new bootstrap.Toast(toastEl).show();
       });
   </script>
   <?php unset($_SESSION['toastMessage'], $_SESSION['toastType']); endif; ?>
</body>
</html>
