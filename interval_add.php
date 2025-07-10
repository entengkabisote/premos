<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $days = intval($_POST['days']);

    if ($name !== '' && $days > 0) {
        $stmt = $conn->prepare("INSERT INTO interval_table (name, days) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $days);
        $stmt->execute();
        $stmt->close();
    }
}
