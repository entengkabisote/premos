<?php
require_once 'session_config.php';

$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  	<meta charset="UTF-8">
  	<title>PREMOS - Preventive Maintenance System</title>
  	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  	<link rel="stylesheet" href="styles/index.css">
	<!-- Toastr CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
	<!-- jQuery (required by toastr) -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<!-- Toastr JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
	<!-- Custom style override -->
	<link rel="stylesheet" href="styles/toastr_custom.css">
	<script src="scripts/toastr_settings.js"></script>
</head>
<body>
  	<div class="container landing-container">
    	<h1 class="mb-4">⚙️ PREMOS</h1>
    	<p class="lead mb-5">Preventive Maintenance System for Maritime Equipment and Machinery</p>

    	<div class="btn-container">
      		<?php if (!$isLoggedIn): ?>
        		<a href="login.php" class="btn btn-primary">Login</a>
      		<?php else: ?>
        		<a href="dashboard.php" class="btn btn-success me-2">Go to Dashboard</a>
        		<a href="logout.php" class="btn btn-danger">Logout</a>
      		<?php endif; ?>
    	</div>
  	</div>

    <?php include 'toastr_handler.php'; ?>
</body>
</html>
