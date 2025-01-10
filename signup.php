<?php
session_start();
include_once("connection/connect.php");
$conn = connection();
$rand = uniqid('user_', true);
$hashedRand = hash('sha256', $rand);

// Add PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Function to send OTP
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
        $mail->Subject = 'Email Verification OTP';
        $mail->Body = "Your OTP for email verification is: <b>$otp</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if(isset($_POST['action']) && $_POST['action'] === 'send_otp') {
    $email = $_POST['email'];
    
    // Check if email already exists
    $checkEmailQuery = "SELECT * FROM `user` WHERE `email`='$email'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['signup_otp'] = $otp;
    $_SESSION['signup_otp_time'] = time();
    $_SESSION['signup_email'] = $email;

    if (sendOTP($email, $otp)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
    }
    exit;
}

if(isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $entered_otp = $_POST['otp'];
    
    if (!isset($_SESSION['signup_otp']) || !isset($_SESSION['signup_otp_time'])) {
        echo json_encode(['success' => false, 'message' => 'No OTP found. Please request a new one.']);
        exit;
    }
    
    if ((time() - $_SESSION['signup_otp_time']) > 600) {
        unset($_SESSION['signup_otp']);
        unset($_SESSION['signup_otp_time']);
        echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        exit;
    }
    
    if ($entered_otp == $_SESSION['signup_otp']) {
        // OTP is valid
        $_SESSION['otp_verified'] = true;
        unset($_SESSION['signup_otp']);
        unset($_SESSION['signup_otp_time']);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
    }
    exit;
}

if(isset($_POST['submit'])) {
    // Check if OTP was verified
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'Please verify your email with OTP first!',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit();
    }

    // Clear OTP verification flag
    unset($_SESSION['otp_verified']);
    
    // Retrieve form data
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $user = $_POST['user'];
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $account = "user";

    // Check if passwords match
    if($pass !== $confirm_pass) {
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'Passwords don\'t match!',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit();
    }

    $hashedPassword = hash('sha256', $pass);

    $checkEmailQuery = "SELECT * FROM `user` WHERE `email`='$email'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'Email already exists. Please try another email.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit();
    }

    // Proceed with inserting the new user
    $sql = "INSERT INTO `user`(`fname`,`mname`,`lname`, `email`, `user`, `password`, `account`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $fname, $mname, $lname, $email, $user, $hashedPassword, $account);
    
    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: 'Success',
                text: 'Your account has been successfully created!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>";
        exit();
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: 'Failed to create account: " . $conn->error . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="img/sbmo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SABAT MO</title>
    <!-- Include SweetAlert -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    
</head>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

    body {
        background: linear-gradient(135deg, #24243e, #302b63, #0f0c29) fixed;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        overflow-y: auto;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px 0;
    }

    /* Add custom scrollbar styling */
    body::-webkit-scrollbar {
        width: 8px;
    }

    body::-webkit-scrollbar-track {
        background: transparent;
    }

    body::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    body::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
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
        width: 15px;
        height: 15px;
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

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
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

    .login-link {
        text-align: center;
        margin-top: 20px;
        margin-left: 15px;
    }

    .login-link a {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .login-link a:hover {
        color: #4776E6;
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

        .jeep {
            width: 250px;
        }
        .form-group{
            width: 90%;
        }
        .btn{
            width: 90%;
        }
        .login-link{
            margin-left: -2px;
        }
    }

    /* Add these styles */
    .password-message {
        position: absolute;
        bottom: -20px;
        left: 0;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .password-match {
        color: #4CAF50;
    }

    .password-mismatch {
        color: #f44336;
    }

    .form-control.valid {
        border-color: rgba(76, 175, 80, 0.5);
    }

    .form-control.invalid {
        border-color: rgba(244, 67, 54, 0.5);
    }

    /* Add these validation styles */
    .form-message {
        position: absolute;
        bottom: -20px;
        left: 0;
        font-size: 12px;
        color: #f44336;
        transition: all 0.3s ease;
    }

    .input-valid {
        color: #4CAF50;
    }

    .input-invalid {
        color: #f44336;
    }

    .form-control.valid {
        border-color: rgba(76, 175, 80, 0.5) !important;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
    }

    .form-control.invalid {
        border-color: rgba(244, 67, 54, 0.5) !important;
        box-shadow: 0 0 5px rgba(244, 67, 54, 0.2);
    }

    /* Add transition for smooth border color change */
    .form-control {
        transition: all 0.3s ease;
    }

    /* Update validation styles to match confirm password */
    .form-message {
        position: absolute;
        bottom: -20px;
        left: 0;
        font-size: 12px;
        color: #f44336;
        transition: all 0.3s ease;
    }

    .form-control {
        transition: all 0.3s ease;
    }

    .form-control.valid {
        border-color: rgba(76, 175, 80, 0.5) !important;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
    }

    .form-control.invalid {
        border-color: rgba(244, 67, 54, 0.5) !important;
        box-shadow: 0 0 5px rgba(244, 67, 54, 0.2);
    }

    /* Add these styles for the warning text */
    .warning-text {
        color: #f44336;
        font-size: 12px;
        margin-top: 5px;
    }
</style>
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
    <h1>Create Account</h1>
    <div class="form-group">
        <input type="text" name="fname" id="fname" required="required" class="form-control" placeholder="Enter your first name"/>
        <label class="form-label">First Name</label>
        <div class="form-message"></div>
    </div>
    <div class="form-group">
        <input type="text" name="mname" id="mname" class="form-control" placeholder="Optional"/>
        <label class="form-label">Middle Name</label>
        <div class="form-message"></div>
    </div>
    <div class="form-group">
        <input type="text" name="lname" id="lname" required="required" class="form-control" placeholder="Enter your last name"/>
        <label class="form-label">Last Name</label>
        <div class="form-message"></div>
    </div>
    <div class="form-group">
        <input type="email" name="email" id="email" required="required" class="form-control" placeholder="Enter your email"/>
        <label class="form-label">Email</label>
        <div class="form-message"></div>
    </div>
    <div class="form-group">
        <input type="hidden" name="user" value="<?php echo $hashedRand; ?>"/>
    </div>
    <div class="form-group">
        <input id="password" type="password" name="password" required="required" class="form-control" placeholder="Enter your password"/>
        <label class="form-label">Password</label>
        <div class="form-message"></div>
    </div>
    <div class="form-group">
        <input id="confirm_password" type="password" name="confirm_password" required="required" class="form-control" placeholder="Confirm your password"/>
        <label class="form-label">Confirm Password</label>
        <div class="password-message"></div>
    </div>
    <button class="btn" type="submit" name="submit">Sign Up</button>
    <div class="login-link">
        <a href="login.php">Already have an account? Login</a>
    </div>
</form>
<!-- partial -->
  <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
$('#password').focusin(function(){
  $('form').addClass('up')
});
$('#password').focusout(function(){
  $('form').removeClass('up')
});

// Panda Eye move
$(document).on( "mousemove", function( event ) {
  var dw = $(document).width() / 15;
  var dh = $(document).height() / 15;
  var x = event.pageX/ dw;
  var y = event.pageY/ dh;
  $('.eye-ball').css({
    width : x,
    height : y
  });
});

// validation


$('.btn').click(function(){
  $('form').addClass('wrong-entry');
    setTimeout(function(){ 
       $('form').removeClass('wrong-entry');
     },3000 );
});
// Display SweetAlert if email already exists
<?php if ($emailExists): ?>
    swal({
        title: "Error",
        text: "Email already exists. Please try another email.",
        icon: "error",
        confirmButtonText: "OK",
    }).then(function() {
        window.location.href = "signup.php";
    });
<?php endif; ?>

// Display SweetAlert if insertion was successful
<?php if ($insertSuccess): ?>
    swal({
        title: "Success",
        text: "Congratulations, your account has been successfully created.",
        icon: "success",
        confirmButtonText: "OK",
    }).then(function(){
        window.location.href = "login.php";
    });
<?php endif; ?>

// Replace all existing form submission handlers with this single one
$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate passwords first
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            Swal.fire({
                title: "Error",
                text: "Passwords don't match!",
                icon: "error",
                confirmButtonText: "OK"
            });
            return false;
        }

        const email = $('#email').val();
        const form = this;

        // Show loading state
        Swal.fire({
            title: 'Sending OTP',
            text: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send OTP
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'send_otp',
                'email': email
            }).toString()
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if (data.success) {
                // Ask for OTP
                Swal.fire({
                    title: 'Enter OTP',
                    text: 'Please enter the OTP sent to your email',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off',
                        maxlength: 6,
                        autocomplete: 'off',
                        pattern: '[0-9]*'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Verify',
                    showLoaderOnConfirm: true,
                    backdrop: true,
                    preConfirm: async (otp) => {
                        if (!otp) {
                            Swal.showValidationMessage('Please enter OTP');
                            return false;
                        }
                        try {
                            const response = await fetch(window.location.href, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams({
                                    'action': 'verify_otp',
                                    'otp': otp
                                }).toString()
                            });
                            const data = await response.json();
                            if (!data.success) {
                                throw new Error(data.message || 'Invalid OTP');
                            }
                            return data;
                        } catch (error) {
                            Swal.showValidationMessage(error.message);
                            return false;
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading while submitting form
                        Swal.fire({
                            title: 'Creating Account',
                            text: 'Please wait...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Create FormData from the form
                        const formData = new FormData(form);
                        formData.append('submit', '1');

                        // Submit form data using fetch
                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Check if registration was successful
                            if (html.includes('successfully created')) {
                                Swal.fire({
                                    title: 'Success',
                                    text: 'Your account has been successfully created!',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = 'login.php';
                                });
                            } else {
                                // Show error if registration failed
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to create account. Please try again.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error',
                                text: 'An error occurred while creating your account',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to send OTP'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while sending OTP'
            });
        });
    });

    // Password validation
    $('#confirm_password').on('keyup', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        const messageElement = $('.password-message');
        
        if (confirmPassword === '') {
            messageElement.text('');
            $(this).removeClass('valid invalid');
        } else if (password === confirmPassword) {
            messageElement.text('Passwords match').removeClass('password-mismatch').addClass('password-match');
            $(this).removeClass('invalid').addClass('valid');
        } else {
            messageElement.text('Passwords do not match').removeClass('password-match').addClass('password-mismatch');
            $(this).removeClass('valid').addClass('invalid');
        }
    });

    // Also check when password is changed
    $('#password').on('keyup', function() {
        if ($('#confirm_password').val() !== '') {
            $('#confirm_password').trigger('keyup');
        }
    });
});
</script>
<script>
    document.addEventListener('contextmenu', function (event) {
        event.preventDefault();
    });
</script>
<script>
// Eye tracking animation
document.addEventListener('mousemove', function(event) {
    const eyes = document.querySelectorAll('.eye-ball');
    eyes.forEach(function(eye) {
        const rect = eye.parentElement.getBoundingClientRect(); // Get eye-white boundaries
        const eyeCenterX = rect.left + (rect.width / 2);
        const eyeCenterY = rect.top + (rect.height / 2);
        
        const angle = Math.atan2(event.clientY - eyeCenterY, event.clientX - eyeCenterX);
        const distance = Math.min(rect.width / 4, 10); // Limit movement radius
        const moveX = Math.cos(angle) * distance;
        const moveY = Math.sin(angle) * distance;
        
        eye.style.transform = `translate(calc(-50% + ${moveX}px), calc(-50% + ${moveY}px))`;
    });
});
</script>
<!-- Add SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</html>
