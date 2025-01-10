<?php 
session_start();
if ($_SESSION['Role'] != 'admin') {
    header('Location: ../index.html?error=Access denied'); 
    exit();
}

include_once("../connection/connect.php");
$conn = connection();
$id = $_GET['id'];
$userData = [];

if ($id) {
    $userQuery = "SELECT * FROM `user` WHERE `user`='$id'";
    $result = $conn->query($userQuery);

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
}

$updateSuccess = false; 

// File handling for profile picture
if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['name'] != '') {
    $target_dir = "../img/c_avatar/";
    $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension; // Generate unique filename
    $target_file = $target_dir . $new_filename;
    
    // Check file type
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    if(!in_array($file_extension, $allowed_types)) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        exit();
    }
    
    // Check file size (limit to 5MB)
    if ($_FILES['profile_picture']['size'] > 5000000) {
        echo "Sorry, your file is too large.";
        exit();
    }
    
    // Upload file
    if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        $profilePic = $new_filename;
    } else {
        echo "Sorry, there was an error uploading your file.";
        exit();
    }
} else {
    // Keep existing profile picture if no new one was uploaded
    $profilePic = $userData['profile'];
}

// Update form handling
if (isset($_POST['submit'])) {
    // Sanitize input data
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $mname = mysqli_real_escape_string($conn, $_POST['mname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $hashedPassword = hash('sha256', $password);
    } else {
        $hashedPassword = $userData['password'];
    }

    $updateQuery = "UPDATE `user` SET 
        `fname`=?, 
        `mname`=?, 
        `lname`=?, 
        `email`=?, 
        `password`=?,
        `profile`=?
        WHERE `user`=?";

    // Use prepared statement
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssss", $fname, $mname, $lname, $email, $hashedPassword, $profilePic, $id);
    
    if ($stmt->execute()) {
        $updateSuccess = true;
        // Add this line to prevent form resubmission
        header("Location: settings.php?id=" . $id . "&success=true");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SABAT MO</title>
        <link rel="icon" type="image/png" href="../img/sbmo.png" sizes="32x32">
    <link rel="shortcut icon" type="image/png" href="../img/sbmo.png">
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
                background: linear-gradient(135deg, #1a1f4c, #1b1b2f);
                font-family: 'Poppins', sans-serif;
                color: #fff;
            }

            .home-section {
                max-width: 100%;
                padding: 40px 20px;
                min-height: 100vh;
                display: flex;
                align-items: center;
            }

            .form-container {
                background: rgba(255, 255, 255, 0.08);
                padding: 40px;
                border-radius: 24px;
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                max-width: 600px;
                margin: auto;
                width: 100%;
            }

            .form-container h1 {
                text-align: center;
                margin-bottom: 40px;
                font-size: 32px;
                color: #fff;
                font-weight: 600;
                letter-spacing: 1.5px;
                position: relative;
            }

            .form-container h1:after {
                content: '';
                position: absolute;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
                width: 60px;
                height: 3px;
                background: linear-gradient(90deg, #4776E6, #8E54E9);
                border-radius: 3px;
            }

            .form-group {
                margin-bottom: 30px;
                position: relative;
            }

            .form-label {
                display: block;
                margin-bottom: 10px;
                font-weight: 500;
                color: #fff;
                font-size: 15px;
                letter-spacing: 0.5px;
            }

            .form-control {
                width: 100%;
                padding: 15px;
                background: rgba(255, 255, 255, 0.05);
                border: 2px solid rgba(255, 255, 255, 0.1);
                border-radius: 12px;
                color: #fff;
                font-size: 15px;
                transition: all 0.3s ease;
            }

            .form-control:focus {
                background: rgba(255, 255, 255, 0.1);
                border-color: #4776E6;
                box-shadow: 0 0 20px rgba(71, 118, 230, 0.2);
            }

            .profile-picture-container {
                text-align: center;
                margin-bottom: 30px;
                padding: 10px;
                background-color: rgba(255, 255, 255, 0.05);
                border-radius: 16px;
            }

            #profilePreview {
                width: 120px;
                height: 120px;
                object-fit: cover;
                border-radius: 50%;
                border: 3px solid #4776E6;
                margin: 15px auto;
                display: block;
                transition: transform 0.3s ease;
                background-color: rgba(255, 255, 255, 0.1);
            }

            #profilePreview:hover {
                transform: scale(1.05);
            }

            .file-input-wrapper {
                margin-top: 15px;
                position: relative;
            }

            .file-input-wrapper input[type="file"] {
                padding: 12px;
                background: rgba(255, 255, 255, 0.08);
                border: 2px dashed rgba(71, 118, 230, 0.5);
                border-radius: 12px;
                color: #fff;
                font-size: 14px;
                transition: all 0.3s ease;
                cursor: pointer;
                width: 100%;
            }

            .file-input-wrapper input[type="file"]::-webkit-file-upload-button {
                background: linear-gradient(45deg, #4776E6, #8E54E9);
                color: white;
                padding: 8px 16px;
                border: none;
                border-radius: 8px;
                margin-right: 10px;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .file-input-wrapper input[type="file"]::-webkit-file-upload-button:hover {
                background: linear-gradient(45deg, #5885f7, #9d63fa);
                transform: translateY(-1px);
            }

            .file-input-wrapper input[type="file"]:hover {
                border-color: #4776E6;
                background: rgba(255, 255, 255, 0.12);
            }

            .file-input-wrapper input[type="file"]:focus {
                outline: none;
                border-color: #4776E6;
                box-shadow: 0 0 15px rgba(71, 118, 230, 0.2);
            }

            .btn {
                background: linear-gradient(45deg, #4776E6, #8E54E9);
                color: #fff;
                border: none;
                padding: 15px 25px;
                border-radius: 12px;
                cursor: pointer;
                width: 100%;
                font-size: 16px;
                font-weight: 600;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                text-transform: uppercase;
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(71, 118, 230, 0.4);
                background: linear-gradient(45deg, #5885f7, #9d63fa);
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .form-container {
                    padding: 30px 20px;
                }

                .form-container h1 {
                    font-size: 26px;
                }

                #profilePreview {
                    width: 100px;
                    height: 100px;
                }
            }
        </style>
    </head>
    <body>
        <?php include "navigation.php" ?>
        <section class="home-section">
            <div class="form-container">
                <h1>Profile Settings</h1>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="profile-picture-container">
                        <img src="../img/c_avatar/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" id="profilePreview">
                        <div class="file-input-wrapper">
                            <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(this);" class="form-control">
                        </div>
                    </div>
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
                        <label class="form-label">Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current password" class="form-control"/>
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
            if (new URLSearchParams(window.location.search).get('success') === 'true') {
                swal({
                    title: "Success",
                    text: "User information has been successfully updated.",
                    icon: "success",
                    confirmButtonText: "OK"
                });
            }
        </script>
        <script>
            document.addEventListener('contextmenu', function (event) {
                event.preventDefault();
            });
        </script>
        <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>
        <script type="text/javascript">
            // Check URL parameter and show alert
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('success') === 'true') {
                    Swal.fire({
                        title: "Success!",
                        text: "User information has been successfully updated.",
                        icon: "success",
                        confirmButtonText: "OK"
                    });
                }
            });
        </script>
    </body>
</html>
