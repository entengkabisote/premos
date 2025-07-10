<?php
if (!isset($_SESSION)) session_start();
require_once 'db_connect.php';
require_once 'functions.php';

// $fullname = $_SESSION['fullname'] ?? 'User';
$loggedin_fullname = $_SESSION['fullname'] ?? 'User';

$avatar = $_SESSION['profile_picture'] ?? '';
$companyLogo = getSetting($conn, 'client_logo'); // ex: uploads/client_logo.png
$smsLogo = getSetting($conn, 'sms_logo');         // ex: uploads/sms_logo.png
$clientName = getSetting($conn, 'client_name');   // ex: SMSCorp
?>

<div class="header" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
    <?php if ($companyLogo): ?>
        <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="Client Logo" style="height: 40px; width: auto;">
    <?php else: ?>
        <span class="client-name"><?php echo htmlspecialchars($clientName); ?></span>
    <?php endif; ?>

    <!-- Center: PREMOS logo + title -->
    <div style="text-align: center;">
        <img src="images/premos.png" alt="PREMOS Logo" style="height: 35px; width: auto;">
        <div style="font-size: 16px; font-weight: bold; color: #1e4d2b;">SHIP MAINTENANCE SUPPORT</div>
    </div>
    
    <?php if ($smsLogo): ?>
        <img src="<?php echo htmlspecialchars($smsLogo); ?>" alt="SMS Logo" style="height: 40px; width: auto;">
        <!-- <img src="<?php echo htmlspecialchars($smsLogo); ?>" alt="SMS Logo"> -->
    <?php else: ?>
        <span class="client-name">SMSCorp</span>
    <?php endif; ?>
</div>