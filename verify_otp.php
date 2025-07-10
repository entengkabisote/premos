<?php
require_once 'session_config.php';
require_once 'utils.php';
require_once 'db_connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = $_POST['otp'] ?? '';
    $correct_otp = $_SESSION['otp'] ?? null;
    $otp_time = $_SESSION['otp_time'] ?? 0;

    // Check if OTP expired (after 5 minutes)
    if (time() - $otp_time > 300) {
        unset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['temp_user']);
        $_SESSION['toastMessage'] = "OTP has expired. Please login again.";
        $_SESSION['toastType'] = "error";
        header("Location: login.php");
        exit;
    }

    if ($input_otp && $correct_otp && $input_otp == $correct_otp) {
        // ✅ Valid OTP, continue login
        $user = $_SESSION['temp_user'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['profile_picture'] = $user['profile_picture'];
        $_SESSION['loggedin'] = true;

        unset($_SESSION['temp_user'], $_SESSION['otp'], $_SESSION['otp_time']);

        $_SESSION['toastMessage'] = 'OTP verified successfully. Welcome!';
        $_SESSION['toastType'] = 'success';
        $ip = getClientIp();
        $agent = $_SERVER['HTTP_USER_AGENT'];

        $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id, username, status, ip_address, user_agent) VALUES (?, ?, 'success', ?, ?)");
        $log_stmt->bind_param("isss", $user['user_id'], $user['username'], $ip, $agent);
        $log_stmt->execute();
        $log_stmt->close();

        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $update_stmt->bind_param("i", $user['user_id']);
        $update_stmt->execute();
        $update_stmt->close();


        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['toastMessage'] = "Invalid OTP. Please try again.";
        $_SESSION['toastType'] = "error";
        header("Location: verify_otp.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification | PREMOS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/verify_otp.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- jQuery (required by toastr) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Custom style override -->
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <script src="scripts/toastr_settings.js"></script>
</head>
<body>
    <div class="otp-form">
        <h4 class="mb-3 text-center">OTP Verification</h4>

        <form method="POST" action="verify_otp.php">
            <div class="mb-3">
                <label for="otp" class="form-label">Enter the OTP sent to your email</label>
                <input type="text" class="form-control" id="otp" name="otp" required>
            </div>

            <div class="mb-3 text-center">
                <span id="otp-timer" class="text-muted" style="font-size: 0.9rem;">
                    OTP valid for 5:00
                </span>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" id="verify-btn">Verify</button>
                <button type="button" class="btn btn-outline-secondary" id="resend-btn" onclick="resendOtp()" disabled>Resend OTP</button>
            </div>
        </form>


    </div>

    <script>
        $(document).ready(function() {
            let remaining = 300;
            const display = $('#otp-timer');
            const verifyBtn = $('#verify-btn');
            const resendBtn = $('#resend-btn');

            const timer = setInterval(() => {
                let minutes = Math.floor(remaining / 60);
                let seconds = remaining % 60;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.text(`OTP valid for ${minutes}:${seconds}`);

                if (remaining <= 0) {
                    clearInterval(timer);
                    display.text("OTP expired. Please resend OTP.");
                    verifyBtn.prop('disabled', true);
                    resendBtn.prop('disabled', false);
                }

                remaining--;
            }, 1000);
        });

        function resendOtp() {
            window.location.href = 'resend_otp.php';
        }
    </script>

    <?php include 'toastr_handler.php'; ?>



</body>
</html>
