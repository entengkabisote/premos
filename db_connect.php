<?php
// include the configuration file
require_once 'config.php'; // Include the config file to use SMTP credentials

// Create connection using defined constants
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Set the charset for the connection
$conn->set_charset("utf8mb4");

// Set the timezone for the MySQL session
// $conn->query("SET time_zone = 'Asia/Manila';");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>