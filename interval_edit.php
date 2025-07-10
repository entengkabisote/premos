<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $days = intval($_POST['days']);

    if ($id && $name !== '' && $days > 0) {
        $stmt = $conn->prepare("UPDATE interval_table SET name = ?, days = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $days, $id);
        $stmt->execute();
        $stmt->close();
    }
}
