<?php
include('session_config.php'); 
include 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role']; 
    $company = $_POST['company'];
    $two_factor_enabled = isset($_POST['two_factor_enabled']) ? 1 : 0;
    $is_superintendent = isset($_POST['is_superintendent']) ? 1 : 0;

    // Validate required fields
    if (empty($fullname) || empty($username) || empty($email) || empty($role) || empty($company)) {
        $_SESSION['toastMessage'] = "All fields are required.";
        $_SESSION['toastType'] = "error";
        header("Location: user_details.php?id=" . $user_id);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toastMessage'] = "Invalid email format.";
        $_SESSION['toastType'] = "error";
        header("Location: user_details.php?id=" . $user_id);
        exit;
    }

    $profile_picture = '';

    // --- SECURE PROFILE PICTURE UPLOAD ---
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = mime_content_type($file_tmp);

        // Sanitize filename (prevent double extensions)
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $base_name = preg_replace('/[^A-Za-z0-9_\-]/', '', pathinfo($file_name, PATHINFO_FILENAME));

        if (!in_array($ext, $allowed_exts) || !in_array($file_type, $allowed_types)) {
            $_SESSION['toastMessage'] = "Invalid file type. Only JPG, PNG, GIF allowed.";
            $_SESSION['toastType'] = "error";
            header("Location: user_details.php?id=" . $user_id); exit;
        }
        if ($file_size > $max_size) {
            $_SESSION['toastMessage'] = "File size exceeds limit (5MB).";
            $_SESSION['toastType'] = "error";
            header("Location: user_details.php?id=" . $user_id); exit;
        }
        // Check image is real
        if (!getimagesize($file_tmp)) {
            $_SESSION['toastMessage'] = "The file is not a valid image.";
            $_SESSION['toastType'] = "error";
            header("Location: user_details.php?id=" . $user_id); exit;
        }

        // Optional: remove old picture (huwag burahin pag default/no previous)
        $old_picture = '';
        $q = $conn->prepare("SELECT profile_picture FROM users WHERE user_id=? LIMIT 1");
        $q->bind_param('i', $user_id);
        $q->execute();
        $res = $q->get_result();
        if ($row = $res->fetch_assoc()) {
            $old_picture = $row['profile_picture'];
        }

        $new_name = uniqid('profile_', true) . "." . $ext;
        $upload_dir = 'uploads/profile_pictures/';
        $upload_path = $upload_dir . $new_name;

        if (move_uploaded_file($file_tmp, $upload_path)) {
            $profile_picture = $new_name;

            // Remove old picture if it's not default and exists
            if ($old_picture && $old_picture !== "default_profile.png") {
                $old_path = $upload_dir . $old_picture;
                if (file_exists($old_path)) @unlink($old_path);
            }
        } else {
            $_SESSION['toastMessage'] = "Failed to upload the profile picture.";
            $_SESSION['toastType'] = "error";
            header("Location: user_details.php?id=" . $user_id); exit;
        }
    }

    // Prepare update query
    if (!empty($profile_picture)) {
        $query = "UPDATE users SET fullname=?, username=?, email=?, role=?, company=?, is_superintendent=?, two_factor_enabled=?, profile_picture=? WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssisi", $fullname, $username, $email, $role, $company, $is_superintendent, $two_factor_enabled, $profile_picture, $user_id);
    } else {
        $query = "UPDATE users SET fullname=?, username=?, email=?, role=?, company=?, is_superintendent=?, two_factor_enabled=? WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssiii", $fullname, $username, $email, $role, $company, $is_superintendent, $two_factor_enabled, $user_id);
    }

    if ($stmt) {
        if (mysqli_stmt_execute($stmt)) {
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
                if (!empty($profile_picture)) {
                    $_SESSION['profile_picture'] = $profile_picture;
                } else {
                    // Get latest from DB in case may existing na
                    $pic_stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
                    $pic_stmt->bind_param("i", $user_id);
                    $pic_stmt->execute();
                    $pic_result = $pic_stmt->get_result();
                    if ($pic_row = $pic_result->fetch_assoc()) {
                        $_SESSION['profile_picture'] = $pic_row['profile_picture'] ?? 'default_profile.png';
                    }
                    $pic_stmt->close();
                }
            }

            $_SESSION['toastMessage'] = "User details updated successfully!";
            $_SESSION['toastType'] = "success";
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $_SESSION['user_id'] != $user_id) {
                header("Location: user_management.php");
            } else {
                header("Location: user_details.php?id=" . $user_id);
            }
            exit;
        } else {
            $_SESSION['toastMessage'] = "Failed to update user details. Please try again.";
            $_SESSION['toastType'] = "error";
            header("Location: user_details.php?id=" . $user_id); exit;
        }
    } else {
        $_SESSION['toastMessage'] = "Failed to prepare the query. Please try again.";
        $_SESSION['toastType'] = "error";
        header("Location: user_details.php?id=" . $user_id); exit;
    }
} else {
    header("Location: user_management.php"); exit;
}
?>
