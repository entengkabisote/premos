<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $type = trim($_POST['type']);
    if ($id && $type !== '') {
        $stmt = $conn->prepare("UPDATE equipment_type SET type = ? WHERE id = ?");
        $stmt->bind_param("si", $type, $id);
        $stmt->execute();
        $stmt->close();
    }
}
