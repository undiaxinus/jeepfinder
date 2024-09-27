
<?php 
session_start();
if ($_SESSION['Role'] != 'admin') {
    header('Location: ../index.html?error=Access denied'); 
    exit();
}

include_once("../connection/connect.php");
$conn = connection();
$id = $_GET['id'];
$userId = $_GET['ids'];
$userData = [];

if ($userId) {
    $userQuery = "SELECT * FROM `user` WHERE `id`='$userId'";
    $result = $conn->query($userQuery);

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
}

$updateSuccess = false; 

if (isset($_POST['submit'])) {
    // Retrieve form data
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $user = $_POST['user'];
    $account = $_POST['account'];

    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $hashedPassword = hash('sha256', $password); 
    } else {
        $hashedPassword = $userData['password'];
    }
    $updateQuery = "UPDATE `user` SET 
        `fname`='$fname', 
        `mname`='$mname', 
        `lname`='$lname', 
        `email`='$email', 
        `user`='$user', 
        `account`='$account',
        `password`='$hashedPassword' 
        WHERE `id`='$userId'";

    if ($conn->query($updateQuery) === TRUE) {
        $updateSuccess = true;
    } else {
        echo "Error: " . $updateQuery . "<br>" . $conn->error;
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
            <h1>Edit User</h1>
            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="fname" value="<?php echo $userData['fname']; ?>" required="required" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="mname" value="<?php echo $userData['mname']; ?>" placeholder="(Optional)" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lname" value="<?php echo $userData['lname']; ?>" required="required" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" value="<?php echo $userData['email']; ?>" required="required" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">User ID</label>
                    <input type="text" name="user" value="<?php echo $userData['user']; ?>" readonly class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current password" class="form-control"/>
                </div>


                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="account" required="required" class="form-control">
                        <option hidden></option>
                        <option value="user" <?php echo ($userData['account'] == 'user') ? 'selected' : ''; ?>>user</option>
                        <option value="admin" <?php echo ($userData['account'] == 'admin') ? 'selected' : ''; ?>>admin</option>
                    </select>
                </div>
                <button class="btn" type="submit" name="submit">Update</button>
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

        // Display SweetAlert if update was successful
        <?php if ($updateSuccess): ?>
            swal({
                title: "Success",
                text: "User information has been successfully updated.",
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
