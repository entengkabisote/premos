<?php
require_once 'db_connect.php';

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$email = 'noscire.somar@gmail.com';
$role = 'admin';
$fullname = 'System Administrator';
$is_verified = 1;
$two_factor_enabled = 1;

$sql = "INSERT INTO users (username, password, email, role, fullname, is_verified, two_factor_enabled)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssii", $username, $password, $email, $role, $fullname, $is_verified, $two_factor_enabled);

if ($stmt->execute()) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
