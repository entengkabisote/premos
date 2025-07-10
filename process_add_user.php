<?php
include('session_config.php');
include 'db_connect.php';

require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $token = bin2hex(random_bytes(50));

    // Dynamic base url, local vs production
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        $base_url = "http://localhost/premos";
    } else {
        $base_url = "https://premos.smscorp.ph";
    }
    $verification_link = $base_url . "/verify_email.php?token=$token";

    // Insert user
    $query = "INSERT INTO users (username, email, role, is_verified, verification_token) VALUES (?, ?, ?, 0, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $role, $token);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) == 1) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SEC;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_USER, 'Predictive Maintenance System');
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body = '
                <div style="font-family:Arial,sans-serif;max-width:420px;padding:20px;background:#f8f9fa;border-radius:8px;">
                    <h2 style="color:#0d6efd;margin-bottom:20px;">Email Verification</h2>
                    <p>Hi <strong>' . htmlspecialchars($username) . '</strong>,</p>
                    <p>Please verify your email address by clicking the button below:</p>
                    <a href="' . $verification_link . '" style="background:#0d6efd;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;margin:18px 0;">Verify Email</a>
                    <p>If you did not request this, you can safely ignore this email.</p>
                    <hr style="margin-top:24px;margin-bottom:10px;">
                    <small>This is an automated message from Planned Maintenance System.</small>
                </div>';
            $mail->AltBody = 'Please click on this link to verify your email: ' . $verification_link;

            $mail->send();
            $_SESSION['success_message'] = 'Verification email has been sent.';
            header('Location: user_management.php');
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: User not added.";
    }
}
?>
