<?php
include_once("connection/connect.php");
session_start();
date_default_timezone_set('Asia/Manila'); // Set Philippine timezone

// Add PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if(!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$conn = connection();
$message = '';

// Function to send OTP via PHPMailer
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'undiaxinus@gmail.com';
        $mail->Password = 'ptjihcapoaqbrily';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('undiaxinus@gmail.com', 'SABAT MO');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { padding: 20px; }
                    .otp { font-size: 24px; font-weight: bold; color: #4776E6; }
                    .warning { color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Password Reset Request</h2>
                    <p>Your new OTP for password reset is: <span class='otp'>$otp</span></p>
                    <p>This OTP will expire in 5 minutes.</p>
                    <p class='warning'>If you didn't request this, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br>SABAT MO Team</p>
                </div>
            </body>
            </html>
        ";
        $mail->AltBody = "Your new OTP for password reset is: $otp\n\nThis OTP will expire in 5 minutes.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nSABAT MO Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle OTP verification
if(isset($_POST['submit'])) {
    $otp = $_POST['otp'];
    $email = $_SESSION['reset_email'];
    
    // Verify OTP
    $sql = "SELECT * FROM user WHERE email = ? AND reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $message = "Invalid or expired OTP. Please try again.";
    }
}

// Handle resend OTP
if(isset($_POST['resend'])) {
    $email = $_SESSION['reset_email'];
    
    // Generate new OTP
    $otp = sprintf("%06d", mt_rand(0, 999999));
    $currentDateTime = new DateTime();
    $expiry = $currentDateTime->modify('+5 minutes')->format('Y-m-d H:i:s');
    
    // Update database with new OTP
    $updateSql = "UPDATE user SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sss", $otp, $expiry, $email);
    
    if($updateStmt->execute() && sendOTP($email, $otp)) {
        $message = "New OTP has been sent to your email.";
        $messageType = "success";
    } else {
        $message = "Failed to send new OTP. Please try again.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - SABAT MO</title>
    <link rel="icon" type="image/png" href="img/sbmo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #24243e, #302b63, #0f0c29);
            margin: 0;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .jeep {
            position: relative;
            width: 300px;
            margin: 0 auto;
            transform: translateY(30px);
        }

        .face img {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.3));
            transition: transform 0.3s ease;
        }

        .eye-white {
            position: absolute;
            width: 60px;
            height: 60px;
            background: #fff;
            border-radius: 50%;
            top: 43%;
            left: 27%;
            transform: translateY(-50%);
            box-shadow: 
                inset 0 0 10px rgba(0,0,0,0.2),
                inset 2px 2px 4px rgba(0,0,0,0.3),
                inset -2px -2px 4px rgba(255,255,255,0.8),
                0 0 5px rgba(0,0,0,0.1);
            overflow: hidden;
            background: radial-gradient(
                circle at 30% 30%,
                #ffffff 0%,
                #f0f0f0 50%,
                #e0e0e0 100%
            );
        }

        .eye-white.rgt {
            left: 53%;
        }

        .eye-ball {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #000;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.1s ease;
        }

        .form-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .main-form {
            background: rgba(17, 16, 29, 0.95);
            border-radius: 20px;
            padding: 40px;
            width: 340px;
            box-shadow: 0 15px 25px rgba(0,0,0,0.6);
            margin-top: 20px;
        }

        h1 {
            color: #fff;
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            margin-left: 15px;
        }

        .form-group {
            position: relative;
            margin-bottom: 30px;
            margin-left: 15px;
            width: 90%;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            color: #fff;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            outline: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4776E6;
        }

        .form-label {
            position: absolute;
            left: 15px;
            top: -10px;
            background: rgba(17, 16, 29, 0.95);
            padding: 0 5px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            border-radius: 5px;
        }

        .btn {
            background: linear-gradient(45deg, #4776E6, #8E54E9);
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            width: 90%;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin-top: 20px;
            margin-left: 15px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(71, 118, 230, 0.3);
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
        }

        .signup-link form {
            display: inline;
            margin: 0;
            padding: 0;
        }

        .resend-btn {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .resend-btn:hover {
            color: #4776E6;
        }

        .signup-link a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .signup-link a:hover {
            color: #4776E6;
        }

        /* Update OTP input styles */
        .otp-input {
            letter-spacing: 12px;
            text-align: center;
            font-size: 20px;
            padding-left: 20px !important;
        }

        /* Fix the info text */
        .info-text {
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            padding: 0 20px;
        }

        @media (max-width: 480px) {
            .main-form {
                width: 280px;
                padding: 30px;
            }
            .form-group {
                width: 90%;
            }
            .btn {
                width: 90%;
            }
            .otp-input {
                letter-spacing: 8px;
                font-size: 18px;
                padding-left: 15px !important;
            }
        }

        /* Additional OTP-specific styles */
        .otp-input {
            letter-spacing: 8px;
            text-align: center;
            font-size: 18px;
        }

        .info-text {
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            opacity: 0.8;
        }

        /* Add floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(30px); }
            50% { transform: translateY(20px); }
        }

        .jeep {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="jeep">
        <div class="face">
            <img src="img/jeepnoeye.png">
            <div class="eye-white">
                <div class="eye-ball"></div>
            </div>
            <div class="eye-white rgt">
                <div class="eye-ball"></div>
            </div>
        </div>
    </div>

    <div class="form-container">
        <form action="" method="post" class="main-form">
            <h1>Enter OTP</h1>
            <p class="info-text">
                Please enter the OTP sent to your email
            </p>
            <div class="form-group">
                <input type="text" name="otp" required="required" 
                       class="form-control otp-input" 
                       placeholder="000000" maxlength="6" 
                       pattern="\d{6}"
                       title="Please enter 6 digits"
                       autocomplete="off"/>
                <label class="form-label">OTP</label>
            </div>
            <button class="btn" type="submit" name="submit">Verify OTP</button>
            <div class="signup-link">
                <form action="" method="post" style="display: inline;">
                    <button type="submit" name="resend" class="resend-btn">Resend OTP</button>
                </form>
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        <?php if($message): ?>
            Swal.fire({
                icon: '<?php echo isset($messageType) ? $messageType : "error" ?>',
                title: '<?php echo $message ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        // Eye animation code
        document.addEventListener('mousemove', function(event) {
            const eyes = document.querySelectorAll('.eye-ball');
            eyes.forEach(function(eye) {
                const rect = eye.getBoundingClientRect();
                const eyeCenterX = rect.left + (rect.width / 2);
                const eyeCenterY = rect.top + (rect.height / 2);
                
                const angle = Math.atan2(event.clientY - eyeCenterY, event.clientX - eyeCenterX);
                const distance = 5;
                const moveX = Math.cos(angle) * distance;
                const moveY = Math.sin(angle) * distance;
                
                eye.style.transform = `translate(${moveX}px, ${moveY}px)`;
            });
        });

        // Only allow numbers in OTP input
        document.querySelector('.otp-input').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html> 