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
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
                max-width: 800px;
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

            /* Address Grid Layout */
            .row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-bottom: 15px;
            }

            .col-md-4 {
                padding: 0;
            }

            /* Select Styling */
            select.form-control {
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 15px center;
                padding-right: 40px;
            }

            select.form-control option {
                background: rgba(17, 16, 29, 0.95);
                color: #fff;
                padding: 12px;
            }

            /* Submit Button */
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
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(71, 118, 230, 0.3);
            }

            .btn:active {
                transform: translateY(0);
            }

            /* Animations */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .form-container {
                animation: fadeIn 0.5s ease-out;
            }

            /* Mobile Responsiveness */
            @media (max-width: 768px) {
                .home-section {
                    padding: 15px;
                }

                .form-container {
                    padding: 20px;
                }

                .row {
                    grid-template-columns: 1fr;
                    gap: 10px;
                }

                .form-container h1 {
                    font-size: 24px;
                }

                .btn {
                    padding: 10px 15px;
                    font-size: 15px;
                }
            }

            /* Custom Scrollbar */
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
                                <input type="text" name="street_number" required="required" placeholder="Purok, Block, Lot, etc." class="form-control"/>
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
                                <input type="text" name="city" required="required" placeholder="Municipality/City" class="form-control"/>
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
                            <option value="" hidden>Select Route</option>
                            <option value="Tabaco to Legazpi">Tabaco to Legazpi</option>
                            <option value="Sto.Domingo to Legazpi">Sto.Domingo to Legazpi</option>
                            <option value="Daraga to Legazpi">Daraga to Legazpi</option>
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
