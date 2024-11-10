<?php
//login.php
include_once("connection/connect.php");

session_start(); // Simulan ang session

// I-check kung mayroon nang naka-login, kung gayon, i-redirect sa tamang page
if(isset($_SESSION['login'])) {
    if($_SESSION['Role'] == 'admin') {
        header('Location: admin/dashboard.php?id=' . $_SESSION['login']);
        exit();
    } else if($_SESSION['Role'] == 'user') {
        header('Location: user/map.php?id=' . $_SESSION['login']);
        exit();
    }
}
$conn = connection();

// Check if user_email cookie is set, and if it is, automatically login
if(isset($_COOKIE["user_email"])) {
    $saved_email = $_COOKIE["user_email"];
    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $saved_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['login'] = $row['id'];
        $_SESSION['Role'] = $row['account'];
        
        if ($_SESSION['Role'] == 'admin') {
            header('Location: admin/dashboard.php?id=' . $_SESSION['login']);
            exit();
        } else if ($_SESSION['Role'] == 'user') {
            header('Location: user/map.php?id=' . $_SESSION['login']);
            exit();
        }
    }
}

// Your login logic here

$conn = connection();

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $status = 'online';
    $currentDateTime = date('Y-m-d H:i:s');


    $hashedPassword = hash('sha256', $password);
/*
    $sqlUpdate = "UPDATE `user` SET `conpass`= ? WHERE email = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ss", $hashedPassword, $email);
    $stmtUpdate->execute();
*/
    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
    // User found in the database
    $row = $result->fetch_assoc();
    $hashedPasswordFromDB = $row['password'];
    //$passwords = $row['conpass'];

    if ($hashedPassword === $hashedPasswordFromDB) {
        // Password is correct
        $_SESSION['login'] = $row['id'];
        $_SESSION['Role'] = $row['account'];

        if ($_SESSION['Role'] == 'admin') {
          $sqlUpdates = "UPDATE `user` SET `status`= ?,`login_time` = ?  WHERE email = ?";
            $stmtUpdates = $conn->prepare($sqlUpdates);
            $stmtUpdates->bind_param("sss", $status,$currentDateTime, $email);
            $stmtUpdates->execute();
            header('Location: admin/dashboard.php?id='.$row['user']);
            exit();
        } else if ($_SESSION['Role'] == 'user') {
            $sqlUpdates = "UPDATE `user` SET `status`= ?,`login_time` = ?  WHERE email = ?";
            $stmtUpdates = $conn->prepare($sqlUpdates);
            $stmtUpdates->bind_param("sss", $status,$currentDateTime, $email);
            $stmtUpdates->execute();

            $_SESSION['username'] = $email;
            header('Location: user/map.php?id='.$row['user']);
            exit();
        }
    } else {
        // Password is incorrect
        $errorMessage = 'Wrong email or password';
    }
} else {
    // User not found in the database
    $errorMessage = 'User not found';
}

    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SABAT MO</title>

     <!-- Include SweetAlert library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
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

        .jeep {
            width: 250px;
        }
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
    <h1>Welcome Back</h1>
    <div class="form-group">
        <input type="text" name="email" required="required" class="form-control" placeholder="Enter your email"/>
        <label class="form-label">Email</label>
    </div>
    <div class="form-group">
        <input id="password" type="password" name="password" required="required" class="form-control" placeholder="Enter your password"/>
        <label class="form-label">Password</label>
    </div>
    <button class="btn" type="submit" name="submit">Login</button>
    <div class="signup-link">
        <a href="signup.php">Don't have an account? Sign Up</a>
    </div>
</form>
<!-- partial -->
  <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
  // Check internet connectivity
function checkInternetConnection() {
    var xhr = new XMLHttpRequest();
    var file = "login.php"; // Replace with a URL that you know will respond
    var randomNum = Math.round(Math.random() * 10000);

    xhr.open('HEAD', file + "?rand=" + randomNum, true);

    xhr.timeout = 5000; // Timeout in milliseconds

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status >= 200 && xhr.status < 304) {
                // Connection established, do nothing
            } else {
                // No connection, redirect to nointernet.html
                window.location.href = 'nointernet.html';
            }
        }
    };

    xhr.ontimeout = function () {
        // No connection, redirect to nointernet.html
        window.location.href = 'nointernet.html';
    };

    try {
        xhr.send();
    } catch (error) {
        // No connection, redirect to nointernet.html
        window.location.href = 'nointernet.html';
    }
}

// Call the function to check internet connectivity
checkInternetConnection();

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


</script>
<script type="text/javascript">
        $(document).ready(function() {
            <?php
            if (isset($errorMessage)) {
              if ($errorMessage == 'Wrong email or password') {
                echo 'Swal.fire({';
                echo '  icon: "error",';
                echo '  title: "'.$errorMessage.'",';
                echo '  text: "The email or password you entered is incorrect!",';
                echo '})';
              } else {
                echo 'Swal.fire({';
                echo '  icon: "error",';
                echo '  title: "'.$errorMessage.'",';
                echo '  text: "The email you entered does not exist!",';
                echo '})';
              }
            }
            ?>
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
</html>
