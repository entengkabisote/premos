<?php
date_default_timezone_set('Asia/Manila');

require 'vendor/autoload.php'; // PHPMailer autoload path

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$logPath = "G:/router_load_log.txt";

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'mail.smscorp.ph';
    $mail->SMTPAuth = true;
    $mail->Username = 'ericson.ramos@smscorp.ph';
    $mail->Password = 'Enteng1972';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('ericson.ramos@smscorp.ph', 'Router Reminder');
    $mail->addAddress('ericson.ramos@smscorp.ph');
    $mail->Subject = 'Router Load Reminder';
    $mail->Body    = 'Reminder: Mag-load na sa router ngayon!';
    $mail->send();

    file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] Email sent successfully.\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logPath, "[" . date('Y-m-d H:i:s') . "] Email failed: {$mail->ErrorInfo}\n", FILE_APPEND);
}
