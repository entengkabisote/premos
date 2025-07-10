<?php
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rank = trim($_POST['rank_name']);
    if ($rank !== '') {
        $stmt = $conn->prepare("INSERT INTO ranks (rank_name) VALUES (?)");
        $stmt->bind_param("s", $rank);
        $stmt->execute();
        $stmt->close();
    }
}
