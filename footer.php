<?php
require_once 'db_connect.php';
require_once 'functions.php';

if (!isset($_SESSION)) session_start();

$clientName = getSetting($conn, 'client_name', 'Client Company');
$fullname = $_SESSION['fullname'] ?? 'User';
$avatar = 'uploads/profile_pictures/' . ($_SESSION['profile_picture'] ?? 'default_profile.png');

?>

<!-- Regular footer content -->
<div class="footer" style="text-align: center; padding: 10px; background-color: #f8f9fa; border-top: 1px solid #ccc;">
    <strong><?php echo htmlspecialchars($clientName); ?></strong> &copy; <?php echo date('Y'); ?> | Strategic Maritime Solutions Corp.
</div>

<!-- Draggable Floating Avatar -->
<div id="draggableAvatar" style="position: fixed; top: 50%; left: 20px; z-index: 999; cursor: move;">
    <img src="<?php echo htmlspecialchars($avatar) . '?v=' . time(); ?>" class="user-avatar" style="height: 40px; border-radius: 50%; cursor: pointer;" onclick="toggleMenu()">
    <div id="userDropdown" class="dropdown-menu" style="display: none; position: absolute; left: 50px; top: 0; background: #fff; border: 1px solid #ccc; padding: 5px 10px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); min-width: 160px;">
        <a href="user_details.php?id=<?= htmlspecialchars($_SESSION['user_id']) ?>" style="display: block; padding: 5px 10px; text-decoration: none;">👤 <?php echo htmlspecialchars($fullname); ?></a>
        <a href="javascript:void(0);" onclick="resetAvatarPosition()" style="display: block; padding: 5px 10px; text-decoration: none;">🔄 Reset Avatar</a>
        <a href="logout.php" style="display: block; padding: 5px 10px; text-decoration: none;">🚪 Logout</a>
    </div>
</div>
