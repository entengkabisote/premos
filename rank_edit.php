<?php
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $rank = trim($_POST['rank_name']);
    if ($id && $rank !== '') {
        $stmt = $conn->prepare("UPDATE ranks SET rank_name = ? WHERE id = ?");
        $stmt->bind_param("si", $rank, $id);
        $stmt->execute();
        $stmt->close();
    }
}
