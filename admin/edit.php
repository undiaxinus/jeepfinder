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
                background: rgba(17, 16, 29, 0.95);
                font-family: 'Poppins', sans-serif;
            }

            .home-section {
                max-width: 100%;
                padding: 20px;
                min-height: 100vh;
                display: flex;
                align-items: center;
            }

            .form-container {
                background: rgba(255, 255, 255, 0.05);
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
                backdrop-filter: blur(4px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                max-width: 500px;
                margin: auto;
                width: 100%;
            }

            .form-container h1 {
                text-align: center;
                margin-bottom: 30px;
                font-size: 28px;
                color: #fff;
                font-weight: 600;
                letter-spacing: 1px;
            }

            .form-group {
                margin-bottom: 25px;
                position: relative;
            }

            .form-label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #fff;
                font-size: 14px;
                letter-spacing: 0.5px;
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

            select.form-control {
                appearance: none;
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                cursor: pointer;
            }

            select.form-control option {
                background: rgba(17, 16, 29, 0.95);
                color: #fff;
                padding: 12px;
                font-size: 14px;
            }

            select.form-control option[hidden] {
                display: none;
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
                margin-top: 10px;
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(71, 118, 230, 0.3);
            }

            .btn:active {
                transform: translateY(0);
            }

            /* Password field icon */
            .password-field {
                position: relative;
            }

            .password-toggle {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: rgba(255, 255, 255, 0.5);
                cursor: pointer;
            }

            /* Mobile Responsiveness */
            @media (max-width: 768px) {
                .home-section {
                    padding: 15px;
                }

                .form-container {
                    padding: 25px;
                }

                .form-container h1 {
                    font-size: 24px;
                }

                .btn {
                    padding: 10px 15px;
                    font-size: 15px;
                }
            }

            /* Add smooth animations */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .form-container {
                animation: fadeIn 0.5s ease-out;
            }

            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.1);
            }

            ::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.2);
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            .password-message {
                color: #ff3e3e;
                font-size: 12px;
                margin-top: 5px;
                display: block;
            }

            /* Style for when passwords match */
            .password-match {
                color: #00ff00 !important;
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
            <?php if ($updateSuccess): ?>
                Swal.fire({
                    title: "Success",
                    text: "User information has been successfully updated.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "users.php?id=<?php echo $id ?>";
                    }
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
