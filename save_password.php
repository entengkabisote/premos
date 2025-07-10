<?php
// save_password.php
include('session_config.php');
include 'db_connect.php';

// Check if there is a valid session user_id
if (!isset($_SESSION['temp_user_id'])) {
    // Handle error, no valid session
    header('Location: error_page.php'); // Redirect to error page
    exit();
}

$user_id = $_SESSION['temp_user_id']; // Use the user_id from the session

if (isset($_POST['password'], $_POST['confirm_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Make sure password and confirm_password are the same
    if ($password !== $confirm_password) {
        // Handle error, passwords do not match
        // Set the error message to the session flash message
        $_SESSION['error_message'] = 'Passwords do not match.';
        header('Location: set_password.php'); // Redirect back to the password set page
        exit();
    }

    // Password strength check
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        // Password doesn't meet the requirements
        $_SESSION['error_message'] = 'Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.';
        header('Location: set_password.php');
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update the user record in the database
    $query = "UPDATE users SET password = ?, is_verified = 1 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $user_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) == 1) {
        // Success, user_id is cleared from the session
        unset($_SESSION['temp_user_id']);
        // Set the success message to the session flash message
        $_SESSION['success_message'] = 'Password has been set successfully.';
        header('Location: login.php'); // Redirect to login page
        exit();
    } else {
        // Handle error
        $_SESSION['error_message'] = 'An error occurred while setting the password.';
        header('Location: set_password.php'); // Redirect back to the password set page
        exit();
    }
} else {
    // Handle error, walang POST data
    $_SESSION['error_message'] = 'Required data is missing.';
    header('Location: set_password.php'); // Redirect back to the password set page
    exit();
}

?>