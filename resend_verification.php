<?php
include 'session_config.php';
require 'db_connect.php';
require_once 'functions.php';
require_once 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? 0;

    // Fetch user details
    $stmt = $conn->prepare("SELECT username, email, fullname FROM users WHERE user_id = ? AND is_verified = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['toastMessage'] = "User not found or already verified.";
        $_SESSION['toastType'] = "error";
        header("Location: login.php");
        exit;
    }

    // Generate new token + expiry
    $newToken = bin2hex(random_bytes(16));
    $expiration = date('Y-m-d H:i:s', strtotime('+1 minute'));

    // Update token
    $update = $conn->prepare("UPDATE users SET verification_token = ?, token_expiration = ? WHERE user_id = ?");
    $update->bind_param("ssi", $newToken, $expiration, $user_id);
    $update->execute();
    $update->close();

    // Send email
    $mail = new PHPMailer(true);
    try {
        $baseUrl = ($_SERVER['HTTP_HOST'] === 'localhost') 
            ? 'http://localhost/premos' 
            : 'https://' . $_SERVER['HTTP_HOST'];

        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SEC;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USER, 'PREMOS System');
        $mail->addAddress($user['email']);
        $mail->isHTML(true);
        $mail->Subject = 'PREMOS - New Verification Link';
        $link = "{$baseUrl}/verify_email.php?token={$newToken}";
        $mail->Body = "
            Hi {$user['fullname']},<br><br>
            You requested a new verification link.<br><br>
            <a href='{$link}'>Click here to verify your account</a><br><br>
            This link will expire in 1 minute.
        ";

        $mail->send();
        $_SESSION['toastMessage'] = "Verification link has been resent.";
        $_SESSION['toastType'] = "success";
    } catch (Exception $e) {
        $_SESSION['toastMessage'] = "Failed to resend email: {$mail->ErrorInfo}";
        $_SESSION['toastType'] = "error";
    }

    header("Location: login.php");
    exit;
}
?>
