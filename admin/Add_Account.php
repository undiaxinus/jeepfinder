<?php 
session_start();
if ($_SESSION['Role'] != 'admin') {
    header('Location: ../index.html?error=Access denied'); 
    exit();
}


//signup.php function
include_once("../connection/connect.php");
$conn = connection();

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
    $account = $_POST['account'];

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
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SABAT MO</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draggable/dist/leaflet-draggable.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://unpkg.com/leaflet-draggable/dist/leaflet-draggable.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/osmbuildings@4.0.0/dist/OSMBuildings-Leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .home-section {
            max-width: 100%;
            padding: 40px;
        }
        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: auto;
        }
        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            width: 100%;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include "navigation.php" ?>
    <section class="home-section">
        <div class="form-container">
            <h1>Sign In</h1>
            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" required="required" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="mname" placeholder="(Optional)" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" required="required" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" required="required" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="account" required="required" class="form-control">
                        <option hidden></option>
                        <option>user</option>
                        <option>admin</option>
                    </select>
                </div>
                <?php
                $random = rand();
                $rand = hash('sha256', $random);
                ?>
                <div class="form-group">
                    <input type="hidden" name="user" value="<?php echo $rand;?>" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input id="password" type="password" name="password" required="required" class="form-control"/>
                </div>
                <button class="btn" type="submit" name="submit">Submit</button>
            </form>
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        $('#password').focusin(function(){
            $('form').addClass('up')
        });
        $('#password').focusout(function(){
            $('form').removeClass('up')
        });

        // Display SweetAlert if email already exists
        <?php if ($emailExists): ?>
            swal({
                title: "Error",
                text: "Email already exists. Please try another email.",
                icon: "error",
                confirmButtonText: "OK",
            }).then(function() {
                window.location.href = "Add_Account.php?id=<?php echo $id ?>";
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
                window.location.href = "users.php?id=<?php echo $id ?>";
            });
        <?php endif; ?>
    </script>
    <script>
        document.addEventListener('contextmenu', function (event) {
            event.preventDefault();
        });
    </script>
</body>
</html>
