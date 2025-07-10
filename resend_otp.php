<?php
require_once 'session_config.php';
require_once 'config.php';
require 'db_connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if temp_user still exists
if (!isset($_SESSION['temp_user'])) {
    $_SESSION['toastMessage'] = "Session expired. Please login again.";
    $_SESSION['toastType'] = "error";
    header("Location: login.php");
    exit;
}

$user = $_SESSION['temp_user'];
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_time'] = time();

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SEC;
    $mail->Port = SMTP_PORT;

    $mail->setFrom(SMTP_USER, 'PREMOS System');
    $mail->addAddress($user['email'], $user['username']);
    $mail->isHTML(true);
    $mail->Subject = 'Your New OTP Code';
    $mail->Body = "Hello {$user['username']},<br>Your new OTP is: <strong>{$otp}</strong>";

    $mail->send();
    $_SESSION['toastMessage'] = "A new OTP has been sent to your email.";
    $_SESSION['toastType'] = "success";

    // Log resend OTP attempt
    require_once 'utils.php'; // if you use getClientIp()
    $user_id = $user['user_id'];
    $username = $user['username'];
    $ip = getClientIp(); // or $_SERVER['REMOTE_ADDR']
    $agent = $_SERVER['HTTP_USER_AGENT'];

    $log_stmt = $conn->prepare("INSERT INTO otp_resend_logs (user_id, username, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("isss", $user_id, $username, $ip, $agent);
    $log_stmt->execute();
    $log_stmt->close();

} catch (Exception $e) {
    $_SESSION['toastMessage'] = "Failed to resend OTP: {$mail->ErrorInfo}";
    $_SESSION['toastType'] = "error";
}

header("Location: verify_otp.php");
exit;
?>
