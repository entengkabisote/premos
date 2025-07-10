<?php
include('session_config.php');
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
include 'db_connect.php';

$query = "SELECT role_id, role_name FROM roles";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User | Planned Maintenance System</title>
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
    <h4 class="mb-4">User Management - Add User</h4>

    <div class="card p-4 shadow-sm mb-4">
        <form id="addUserForm" action="process_add_user.php" method="POST" novalidate>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input id="username" name="username" type="text" class="form-control" required onblur="checkUsername(this.value)">
                    <div id="username_error" class="text-danger small mt-1"></div>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" class="form-control" required onblur="checkEmail(this.value)">
                    <div id="email_error" class="text-danger small mt-1"></div>
                </div>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="" selected>Select Role...</option>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <option value="<?= htmlspecialchars($row['role_name']) ?>">
                            <?= htmlspecialchars($row['role_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <a href="user_management.php" class="btn btn-secondary">🔙 Back</a>
                <button id="submit-btn" type="button" class="btn btn-primary" onclick="validateForm()">➕ Add User</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="scripts/toastr_settings.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'toastr_handler.php'; ?>
<script src="scripts/header.js" defer></script>

<script>
    let isUsernameTaken = false;
    let isEmailTaken = false;

    function displayError(inputId, errorId, message) {
        document.getElementById(inputId).classList.add('is-invalid');
        let err = document.getElementById(errorId);
        err.textContent = message;
        err.classList.add('visible');
    }

    function clearError(inputId, errorId) {
        document.getElementById(inputId).classList.remove('is-invalid');
        let err = document.getElementById(errorId);
        err.textContent = '';
        err.classList.remove('visible');
    }

    function checkUsername(username) {
        if (username.length === 0) {
            isUsernameTaken = false;
            clearError('username', 'username_error');
            updateSubmitButton();
            return;
        }
        const xhr = new XMLHttpRequest();
        xhr.onload = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText.includes("taken")) {
                    isUsernameTaken = true;
                    displayError('username', 'username_error', 'Username is already taken');
                } else {
                    isUsernameTaken = false;
                    clearError('username', 'username_error');
                }
                updateSubmitButton();
            }
        };
        xhr.open("GET", "check_username.php?username=" + encodeURIComponent(username), true);
        xhr.send();
    }

    function checkEmail(email) {
        if (email.length === 0) {
            isEmailTaken = false;
            clearError('email', 'email_error');
            updateSubmitButton();
            return;
        }
        const xhr = new XMLHttpRequest();
        xhr.onload = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText.includes("registered")) {
                    isEmailTaken = true;
                    displayError('email', 'email_error', 'Email is already registered');
                } else {
                    isEmailTaken = false;
                    clearError('email', 'email_error');
                }
                updateSubmitButton();
            }
        };
        xhr.open("GET", "check_email.php?email=" + encodeURIComponent(email), true);
        xhr.send();
    }

    function updateSubmitButton() {
        const btn = document.getElementById("submit-btn");
        btn.disabled = isUsernameTaken || isEmailTaken;
        btn.innerHTML = btn.disabled
            ? '<i class="fa-solid fa-ban"></i> Cannot Add'
            : '<i class="fa-solid fa-user-plus"></i> Add User';
    }

    function validateForm() {
        if (isUsernameTaken || isEmailTaken) {
            toastr.error('Please provide unique username and email address before proceeding.');
            if (isUsernameTaken) document.getElementById('username').focus();
            else if (isEmailTaken) document.getElementById('email').focus();
            return false;
        } else {
            document.getElementById('addUserForm').submit();
        }
    }
</script>
</body>
</html>
