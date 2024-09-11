<?php
//signup.php function
include_once("connection/connect.php");
$conn = connection();

$sql = "SELECT * FROM user";
$employees = $conn->query($sql) or die($conn->error);
$row = $employees->fetch_assoc();

if(isset($_POST['submit'])) {
    // Retrieve form data
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $user = $_POST['user'];
    $pass = $_POST['password'];
    $account = "user";


    $hashedPassword = hash('sha256', $pass);

    $checkEmailQuery = "SELECT * FROM `user` WHERE `email`='$email' AND `user`='$user'";
    $result = $conn->query($checkEmailQuery);

    if ($result->num_rows > 0) {
        header('Location: signup.php?error=Email already exists');
        exit(); 
    }

    $sql = "INSERT INTO `user`(`fname`,`mname`,`lname`, `email`, `user`, `password`, `account`) VALUES ('$fname','$mname','$lname','$email','$user','$hashedPassword','$account')";
    $conn->query($sql) or die ($conn->error);

    header('Location: signup.php?success=Record added');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Real-Time Location Tracking</title>
    
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
  height: 550px;
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
  <h1>Verify Code</h1>
  
  <div class="form-group">
    <input type="text" name="code" required="required" class="form-control"/>
    <label class="form-label">Send code</label>
  </div>
  
  <div class="form-group">
    
    <button class="btn" type="submit" name="submit" >Sign In</button>
  </div>
  <div class="form-group">
    <b><a href="login.php" style="text-decoration: none;color: #02007B;">Login</a></b>
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
</script>
</html>
