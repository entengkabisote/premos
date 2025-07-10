<?php
require_once 'session_config.php';
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 3 && !isset($_SESSION['captcha_question'])) {
    $a = rand(1, 9);
    $b = rand(1, 9);
    $_SESSION['captcha_question'] = "$a + $b";
    $_SESSION['captcha_answer'] = strval($a + $b);
}


include 'db_connect.php';
require_once 'config.php';
require 'vendor/autoload.php';
require_once 'utils.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($_SESSION['login_attempts'] >= 3) {
    $captcha = $_POST['captcha'] ?? '';
    if (trim($captcha) !== $_SESSION['captcha_answer']) {
        $_SESSION['toastMessage'] = 'CAPTCHA failed.';
        $_SESSION['toastType'] = 'error';
        unset($_SESSION['captcha_question'], $_SESSION['captcha_answer']);
        header("Location: login.php");
        exit;
    }
}

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $ip = getClientIp();
    $agent = $_SERVER['HTTP_USER_AGENT'];

    if ($user && password_verify($password, $user['password'])) {
        if ($user['two_factor_enabled'] == 0) {
            unset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['temp_user']); // <--- ADD THIS
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['captcha_question'], $_SESSION['captcha_answer']);

            // Add this block to update last_login
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();

            $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, username, status, ip_address, user_agent) VALUES (?, ?, 'success', ?, ?)");
            $log_stmt->bind_param("isss", $user['user_id'], $username, $ip, $agent);
            $log_stmt->execute();
            $log_stmt->close();
            $_SESSION['toastMessage'] = 'Login successful!';
            $_SESSION['toastType'] = 'success';
            header('Location: dashboard.php');
            exit;
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_time'] = time();
            $_SESSION['temp_user'] = $user;
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['captcha_question'], $_SESSION['captcha_answer']);

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
                $mail->Subject = 'Your OTP Code';
                $mail->Body = "Hello {$user['username']},<br>Your OTP is: <strong>{$otp}</strong>";

                $mail->send();
                header('Location: verify_otp.php');
                exit;
            } catch (Exception $e) {
                session_unset();
                session_destroy();
                require_once 'session_config.php'; // restart session properly
                $_SESSION['toastMessage'] = "OTP sending failed: {$mail->ErrorInfo}";
                $_SESSION['toastType'] = "error";
                header('Location: login.php');
                exit;
            }
        }
    } else {
        $_SESSION['login_attempts'] += 1;

        // Log failed attempt
        $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, username, status, ip_address, user_agent) VALUES (NULL, ?, 'failed', ?, ?)");
        $log_stmt->bind_param("sss", $username, $ip, $agent);
        $log_stmt->execute();
        $log_stmt->close();
        
        $_SESSION['toastMessage'] = 'Invalid username or password.';
        $_SESSION['toastType'] = 'error';
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | PREMOS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
</head>
<body>
    <div class="login-form">
        <h4 class="mb-4 text-center">PREMOS Login</h4>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <?php if ($_SESSION['login_attempts'] >= 3): ?>
                <div class="mb-3">
                <label for="captcha" class="form-label">What is <?= $_SESSION['captcha_question']; ?>?</label>
                    <input type="text" class="form-control" name="captcha" required>
                </div>
            <?php endif; ?>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>

    <!-- JS includes -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
    <?php include 'toastr_handler.php'; ?>

</body>
</html>
