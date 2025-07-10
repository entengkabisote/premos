<?php
include 'db_connect.php';
include('session_config.php');

$user_id = $_SESSION['user_id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'User';

// Get vessels
if ($role === 'Admin' || $role === 'SuperAdmin' || $role === 'SuperUser') {
    $sql = "SELECT * FROM vessels ORDER BY vessel_name";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "
        SELECT v.*
        FROM vessels v
        INNER JOIN users_vessels uv ON v.id = uv.vessel_id
        WHERE uv.user_id = ?
        ORDER BY v.vessel_name
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<div class="row">
<?php
while($row = $result->fetch_assoc()) {
?>
    <div class="col-12 col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <a href="vessel_details.php?id=<?php echo $row['id']; ?>">
                <img src="<?php echo htmlspecialchars($row['imahe']); ?>" alt="Vessel Image" class="card-img-top">
            </a>
            <div class="card-body text-center">
                <a href="vessel_details.php?id=<?php echo $row['id']; ?>" title="<?php echo htmlspecialchars($row['vessel_name']); ?>" class="text-decoration-none">
                    <p class="mb-0 small text-truncate"><?php echo htmlspecialchars($row['vessel_name']); ?></p>
                </a>
            </div>
        </div>
    </div>
<?php
}
?>
</div>
