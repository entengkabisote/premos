<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bootstrap Dropdown Test</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
  <div class="container py-5">
    <!-- Reports Dropdown -->
    <div class="dropdown">
      <button class="btn btn-outline-danger btn-sm dropdown-toggle" type="button" id="dropdownReports1"
        data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
        <i class="material-icons">report</i>
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdownReports1">
        <li><a class="dropdown-item" href="#">Compliance Report</a></li>
        <li><a class="dropdown-item" href="#">Person-In-Charge Report</a></li>
      </ul>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
