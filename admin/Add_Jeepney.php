<?php 
session_start();
if ($_SESSION['Role'] != 'admin') {
    header('Location: ../index.html?error=Access denied'); 
    exit();
}
include_once("../connection/connect.php");
$conn = connection();
$insertSuccess = false; 

if(isset($_POST['submit'])) {
    $name = $_POST['name'];
    $number = $_POST['number'];
    $address = $_POST['street_number'] . ', ' . $_POST['floor_unit'] . ', ' . $_POST['street_name'] . ', ' . $_POST['city'] . ', ' . $_POST['province'] . ', ' . $_POST['postal_code'];
    $pnumber = $_POST['plate_number'];
    $route = $_POST['route'];
    $company_name = $_POST['company_name'];
    $jeepicons = ['jeeps2.png','jeeep.png','jeepsv.png','jeepsy.png','jeepsy1.png','jeepsy2.png','jeepsy3.png','jeepsys.png','jeepsy4.png'];
    $jeepicon = $jeepicons[array_rand($jeepicons)];
    $sql = "INSERT INTO `locate`(`drivername`, `cnumber`, `platenumber`, `route`, `jeepicon`, `address`, `company_name`) VALUES ('$name','$number','$pnumber','$route','$jeepicon','$address','$company_name')";
    
    if ($conn->query($sql) === TRUE) {
        $insertSuccess = true;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
                <h1>Form</h1>
                <form action="" method="post">
                    <div class="form-group">
                        <label class="form-label">Driver's Name</label>
                        <input type="text" name="name" required="required" placeholder="First name, Middle name, Last name" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="street_name" required="required" placeholder="Street name" class="form-control"/>
                            </div><br>
                            <div class="col-md-4">
                                <input type="text" name="street_number" required="required" placeholder="Number" class="form-control"/>
                            </div><br>
                            <div class="col-md-4">
                                <input type="text" name="floor_unit" placeholder="Floor, unit..." class="form-control"/>
                            </div>
                        </div><br>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <input type="text" name="postal_code" required="required" placeholder="Postal code" class="form-control"/>
                            </div><br>
                            <div class="col-md-4">
                                <input type="text" name="city" required="required" placeholder="City" class="form-control"/>
                            </div>
                        </div><br>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <input type="text" name="province" required="required" placeholder="Provice" class="form-control"/>
                            </div><br>  
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" placeholder="(Optional)" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone number</label>
                        <input type="text" name="number" required="required" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plate number</label>
                        <input type="text" name="plate_number" required="required" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Route</label>
                        <select name="route" required="required" class="form-control">
                            <option hidden></option>
                            <option>Tabaco to Legazpi</option>
                            <option>Sto.Domingo to Legazpi</option>
                            <option>Daraga to Legazpi</option>
                        </select>
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

            <?php if ($insertSuccess): ?>
                swal({
                    title: "Success",
                    text: "Congratulations, your account has been successfully created.",
                    icon: "success",
                    confirmButtonText: "OK",
                }).then(function(){
                    window.location.href = "dashboard.php?id=<?php echo $id ?>";
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
