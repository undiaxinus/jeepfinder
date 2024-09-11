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
<style type="text/css">
    .jeep img{
        width: 20%;
        top: 0;
        z-index: 100%;
        filter: drop-shadow();
    }
    .jeep {
        position: relative;
    }
    .eye-image{
        margin-left: 40%;
    }
    .eye-ball img{
        
        position: absolute;
        top: -10px;
        
        margin-left: 40%;
         z-index: 100%;
    }

  @import url(https://fonts.googleapis.com/css?family=Dancing+Script|Roboto);
*, *:after, *:before {
  box-sizing: border-box;
}

body {
  background:rgba(180.46, 179.70, 217.81, 0.37);
  text-align: center;
  font-family: 'Roboto', sans-serif;
   overflow: hidden; 
}

.jeep{
  position: relative;
  width: 200px;
  margin: 50px auto;
  top: -100px;
}

.face img{
  width: 300px;
  height: 300px;
  margin: 50px auto;
  left: -25%;
  z-index: 50;
  position: relative;
}

.ear, .ear:after {
  position: absolute;
  width: 80px;
  height: 80px;
  background: #000;
  z-index: 5;
  border: 10px solid #fff;
  left: -15px;
  top: -15px;
  border-radius: 100%;
}
.ear:after {
  content: '';
  left: 125px;
}

.eye-shade {
  background: #000;
  width: 50px;
  height: 80px;
  margin: 10px;
  position: absolute;
  top: 35px;
  left: 25px;
  transform: rotate(220deg);
  border-radius: 25px/20px 30px 35px 40px;
}
.eye-shade.rgt {
  transform: rotate(140deg);
  left: 105px;
}

.eye-white {
  position: absolute;
  width: 50px;
  height: 50px;
  border-radius: 100%;
  
  z-index: 500;
  left: 35px;
  top: 160px;
  overflow: hidden;
}
.eye-white.rgt {
  right: 35px;
  left: auto;
}

.eye-ball {
  position: absolute;
  width: 0px;
  height: 0px;
  left: 30px;
  top: 30px;
  max-width: 30px;
  max-height: 30px;
  transition: 0.1s;
}
.eye-ball:after {
  content: '';
  background: #000;
  position: absolute;
  border-radius: 100%;
  right: 0;
  bottom: 0px;
  width: 20px;
  height: 20px;
}



form {
  display: none;
  max-width: 400px;
  padding: 20px 40px;
  background: #fff;
  height: 350px;
  margin: auto;
  display: block;
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
  transition: 0.3s;
  position: relative;
  transform: translateY(-250px);
  z-index: 500;
  border: 1px solid #eee;
  border-radius: 20px;
}
form.up {
  transform: translateY(-380px);
}

h1 {
  color: #02007B;
  font-family: sans-serif;
}

.btn {
  background: #fff;
  padding: 5px;
  width: 150px;
  height: 35px;
  border: 1px solid #02007B;
  margin-top: 25px;
  cursor: pointer;
  transition: 0.3s;
  box-shadow: 0 50px #02007B inset;
  color: #fff;
}
.btn:hover {
  box-shadow: 0 0 #02007B inset;
  color: #02007B;
}
.btn:focus {
  outline: none;
}

.form-group {
  position: relative;
  font-size: 15px;
  color: #02007B;
}
.form-group + .form-group {
  margin-top: 30px;
}
.form-group .form-label {
  position: absolute;
  z-index: 1;
  left: 0;
  top: 5px;
  transition: 0.3s;
}
.form-group .form-control {
  width: 100%;
  position: relative;
  z-index: 3;
  height: 35px;
  background: none;
  border: none;
  padding: 5px 0;
  transition: 0.3s;
  border-bottom: 1px solid #777;
  color: #555;
}
.form-group .form-control:invalid {
  outline: none;
}
.form-group .form-control:focus, .form-group .form-control:valid {
  outline: none;
  box-shadow: 0 1px #02007B;
  border-color: #02007B;
}
.form-group .form-control:focus + .form-label, .form-group .form-control:valid + .form-label {
  font-size: 12px;
  color: #02007B;
  transform: translateY(-15px);
}
.wrong-entry {
  -webkit-animation: wrong-log 0.3s;
  animation: wrong-log 0.3s;
}
.wrong-entry .alert {
  opacity: 1;
  transform: scale(1, 1);
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
  <div class="hand"></div>
  <div class="hand rgt"></div>
  <h1>Login</h1>
  <div class="form-group">
    <input type="text" name="email" required="required" class="form-control"/>
    <label class="form-label">Email</label>
  </div>
  <div class="form-group">
    <input id="password" type="password" name="password" required="required" class="form-control"/>
    <label class="form-label">Password</label>
    <button class="btn" type="submit" name="submit">Login</button>
  </div>
  <div class="form-group">
    <b><a href="signup.php" style="text-decoration: none; color: #02007B;">Sign Up</a></b>
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

</html>
