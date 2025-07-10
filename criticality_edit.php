<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $level = trim($_POST['level']);
    if ($id && $level !== '') {
        $stmt = $conn->prepare("UPDATE criticality_table SET level = ? WHERE id = ?");
        $stmt->bind_param("si", $level, $id);
        $stmt->execute();
        $stmt->close();
    }
}
