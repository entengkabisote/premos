<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    if ($type !== '') {
        $stmt = $conn->prepare("INSERT INTO equipment_type (type) VALUES (?)");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $stmt->close();
    }
}
