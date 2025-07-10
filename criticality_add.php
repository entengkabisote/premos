<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = trim($_POST['level']);
    if ($level !== '') {
        $stmt = $conn->prepare("INSERT INTO criticality_table (level) VALUES (?)");
        $stmt->bind_param("s", $level);
        $stmt->execute();
        $stmt->close();
    }
}
