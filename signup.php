<?php
//signup.php function
include_once("connection/connect.php");
$conn = connection();
$rand = uniqid('user_', true);
$hashedRand = hash('sha256', $rand);
$emailExists = false; // Flag to check if email already exists
$insertSuccess = false; // Flag to check if insertion was successful

if(isset($_POST['submit'])) {
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
            swal({
                title: 'Error',
                text: 'Passwords don\'t match!',
                icon: 'error',
                button: 'OK',
            });
        </script>";
        exit();
    }

    $hashedPassword = hash('sha256', $pass);

    $checkEmailQuery = "SELECT * FROM `user` WHERE `email`='$email'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        // Set flag to true if email already exists
        $emailExists = true;
    } else {
        // Proceed with inserting the new user if email doesn't exist
        $sql = "INSERT INTO `user`(`fname`,`mname`,`lname`, `email`, `user`, `password`, `account`) VALUES ('$fname','$mname','$lname','$email','$user','$hashedPassword','$account')";
        
        if ($conn->query($sql) === TRUE) {
            // Set flag to true if insertion was successful
            $insertSuccess = true;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
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

// Password validation
$(document).ready(function() {
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

    // Form submission validation
    $('form').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            e.preventDefault();
            swal({
                title: "Error",
                text: "Passwords don't match!",
                icon: "error",
                button: "OK",
            });
        }
    });
});

password.onchange = validatePassword;
confirm_password.onkeyup = validatePassword;

// Update your form submission
$('form').on('submit', function(e) {
    if(password.value != confirm_password.value) {
        e.preventDefault();
        swal({
            title: "Error",
            text: "Passwords don't match!",
            icon: "error",
            button: "OK",
        });
    }
});

// Form validation for all inputs
$(document).ready(function() {
    // Name validation function
    function validateName(input) {
        const value = input.val();
        const messageElement = input.siblings('.form-message');
        
        if (value === '' && input.prop('required')) {
            messageElement.text('Please fill out this field.');
            input.removeClass('valid').addClass('invalid');
            return false;
        } else if (value.length < 2 && value !== '') {
            messageElement.text('Must be at least 2 characters');
            input.removeClass('valid').addClass('invalid');
            return false;
        } else if (!/^[a-zA-Z\s]*$/.test(value) && value !== '') {
            messageElement.text('Only letters allowed');
            input.removeClass('valid').addClass('invalid');
            return false;
        } else if (value !== '') {
            messageElement.text('');
            input.removeClass('invalid').addClass('valid');
            return true;
        } else {
            messageElement.text('');
            input.removeClass('valid invalid');
            return true;
        }
    }

    // Email validation function
    function validateEmail(input) {
        const value = input.val();
        const messageElement = input.siblings('.form-message');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value === '') {
            messageElement.text('Please fill out this field');
            input.removeClass('valid').addClass('invalid');
            return false;
        } else if (!emailRegex.test(value)) {
            messageElement.text('Please include an \'@\' in the email address');
            input.removeClass('valid').addClass('invalid');
            return false;
        } else {
            messageElement.text('');
            input.removeClass('invalid').addClass('valid');
            return true;
        }
    }

    // Attach validation to input events
    $('#fname, #lname').on('input', function() {
        validateName($(this));
    });

    $('#mname').on('input', function() {
        if ($(this).val() !== '') {
            validateName($(this));
        } else {
            $(this).siblings('.form-message').text('');
            $(this).removeClass('valid invalid');
        }
    });

    $('#email').on('input', function() {
        validateEmail($(this));
    });

    // Add blur event for empty field validation
    $('input[required]').on('blur', function() {
        if ($(this).val() === '') {
            $(this).addClass('invalid');
            $(this).siblings('.form-message').text('Please fill out this field.');
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
</html>
