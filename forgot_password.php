<?php
include_once("connection/connect.php");
session_start();
date_default_timezone_set('Asia/Manila'); // Set Philippine timezone

// Add PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Function to send OTP via PHPMailer
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'undiaxinus@gmail.com'; // Your email
        $mail->Password = 'ptjihcapoaqbrily'; // Your app password
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
                    <p>Your OTP for password reset is: <span class='otp'>$otp</span></p>
                    <p>This OTP will expire in 5 minutes.</p>
                    <p class='warning'>If you didn't request this, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br>SABAT MO Team</p>
                </div>
            </body>
            </html>
        ";
        $mail->AltBody = "Your OTP for password reset is: $otp\n\nThis OTP will expire in 5 minutes.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nSABAT MO Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$conn = connection();
$message = '';

if(isset($_POST['submit'])) {
    $email = $_POST['email'];
    
    // Check if email exists
    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1) {
        // Generate OTP
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $currentDateTime = new DateTime();
        $expiry = $currentDateTime->modify('+5 minutes')->format('Y-m-d H:i:s');
        
        // Store OTP in database
        $updateSql = "UPDATE user SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sss", $otp, $expiry, $email);
        
        if($updateStmt->execute()) {
            // Send OTP via PHPMailer
            if(sendOTP($email, $otp)) {
                $_SESSION['reset_email'] = $email;
                header("Location: verify_otp.php");
                exit();
            } else {
                $message = "Failed to send OTP. Please try again.";
            }
        } else {
            $message = "Failed to generate OTP. Please try again.";
        }
    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Forgot Password - SABAT MO</title>
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

    form {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 40px;
        padding-right: 60px;
        border-radius: 20px;
        width: 320px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        transform: translateY(-30px);
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
        margin-bottom: 25px;
        position: relative;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: #fff;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
        outline: none;
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
        width: 100%;
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
        margin-left: 15px;
    }

    .signup-link a {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .signup-link a:hover {
        color: #4776E6;
    }

    /* Animation for form movement */
    form.up {
        transform: translateY(-50px);
    }

    /* Floating animation for jeepney */
    @keyframes float {
        0%, 100% { transform: translateY(30px); }
        50% { transform: translateY(20px); }
    }

    .jeep {
        animation: float 3s ease-in-out infinite;
    }

    /* Responsive Design */
    @media (max-width: 480px) {
        form {
            width: 280px;
            padding: 30px;
        }
        .form-group{
            width: 90%;
        }
        .btn{
            width: 90%;
        }
        .signup-link{
            margin-left: -2px;
        }
        
        .jeep {
            width: 250px;
        }
    }

    .forgot-password {
        text-align: right;
        margin-bottom: 15px;
        margin-right: 15px;
    }

    .forgot-password a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .forgot-password a:hover {
        color: #4776E6;
    }
</style>
<body>
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
    <form action="" method="post">
        <h1>Forgot Password</h1>
        <div class="form-group">
            <input type="email" name="email" required="required" class="form-control" placeholder="Enter your email"/>
            <label class="form-label">Email</label>
        </div>
        <button class="btn" type="submit" name="submit">Send OTP</button>
        <div class="signup-link">
            <a href="login.php">Back to Login</a>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        <?php if($message): ?>
            Swal.fire({
                icon: '<?php echo strpos($message, "sent") !== false ? "success" : "error" ?>',
                title: '<?php echo $message ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>

        // Eye animation code from login.php
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
    </script>
</body>
</html> 