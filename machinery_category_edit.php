<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['category_name']);
    if ($id && $name !== '') {
        $stmt = $conn->prepare("UPDATE equipment_category SET category_name = ? WHERE equipment_category_id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
    }
}
