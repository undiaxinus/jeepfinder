<?php
session_start();
if ($_SESSION['Role'] != 'admin') {
    header('Location: ../index.html?error=Access denied'); 
    exit();
}

include_once("../connection/connect.php");
$conn = connection();

$updateSuccess = false; 

if(isset($_POST['submit'])) {
    $ids = $_POST['ids']; 
    $name = $conn->real_escape_string($_POST['name']);
    $number = $conn->real_escape_string($_POST['number']);
    $address = $conn->real_escape_string($_POST['street_number']) . ', ' . $conn->real_escape_string($_POST['floor_unit']) . ', ' . $conn->real_escape_string($_POST['street_name']) . ', ' . $conn->real_escape_string($_POST['city']) . ', ' . $conn->real_escape_string($_POST['province']) . ', ' . $conn->real_escape_string($_POST['postal_code']);
    $pnumber = $conn->real_escape_string($_POST['plate_number']);
    $email = $conn->real_escape_string($_POST['email']);
    $route = $conn->real_escape_string($_POST['route']);
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $jeepicons = ['jeeps2.png','jeeep.png','jeepsv.png','jeepsy.png','jeepsy1.png','jeepsy2.png','jeepsy3.png','jeepsys.png','jeepsy4.png'];
    $jeepicon = $jeepicons[array_rand($jeepicons)];
    $passenger_capacity = $conn->real_escape_string($_POST['passenger_capacity']);

    $sql = "UPDATE `locate` SET `drivername`='$name', `cnumber`='$number', `email`='$email', `platenumber`='$pnumber', `route`='$route', `jeepicon`='$jeepicon', `address`='$address', `company_name`='$company_name', `capacity`='$passenger_capacity' WHERE `ID`='$ids'";
    
    if ($conn->query($sql) === TRUE) {
        $updateSuccess = true;
        $_SESSION['success_message'] = "Driver information updated successfully!";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

if (isset($_GET['ids'])) {
    $id = $_GET['id'];
    $ids = $_GET['ids'];
    $sql_fetch = "SELECT * FROM `locate` WHERE `ID`='$ids'";
    $result = $conn->query($sql_fetch);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['drivername'];
        $number = $row['cnumber'];
        $email = $row['email'];
        $address_parts = explode(', ', $row['address']);
        $street_number = $address_parts[0];
        $floor_unit = $address_parts[1] ?? '';
        $street_name = $address_parts[2] ?? '';
        $city = $address_parts[3] ?? '';
        $province = $address_parts[4] ?? '';
        $postal_code = $address_parts[5] ?? '';
        $pnumber = $row['platenumber'];
        $passenger_capacity = $row['capacity'];
        $route = $row['route'];
        $company_name = $row['company_name'];
    } else {
        echo "Record not found.";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Driver Information</title>
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
                <h1>Edit Driver Information</h1>
                <form action="" method="post">
                    <input type="hidden" name="ids" value="<?php echo $ids; ?>">
                    <div class="form-group">
                        <label class="form-label">Driver's Name</label>
                        <input type="text" name="name" required="required" value="<?php echo $name; ?>" placeholder="First name, Middle name, Last name" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="street_name" required="required" value="<?php echo $street_name; ?>" placeholder="Street name" class="form-control"/>
                            </div><br>

                            <div class="col-md-4">
                                <input type="text" name="street_number" required="required" value="<?php echo $street_number; ?>" placeholder="Number" class="form-control"/>
                            </div><br>

                            <div class="col-md-4">
                                <input type="text" name="floor_unit" value="<?php echo $floor_unit; ?>" placeholder="Floor, unit..." class="form-control"/>
                            </div>
                        </div><br>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <input type="text" name="postal_code" required="required" value="<?php echo $postal_code; ?>" placeholder="Postal code" class="form-control"/>
                            </div><br>

                            <div class="col-md-4">
                                <input type="text" name="city" required="required" value="<?php echo $city; ?>" placeholder="City" class="form-control"/>
                            </div><br>

                            <div class="col-md-4">
                                <input type="text" name="province" required="required" value="<?php echo $province; ?>" placeholder="Province" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" value="<?php echo $company_name; ?>" placeholder="(Optional)" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone number</label>
                        <input type="text" name="number" required="required" value="<?php echo $number; ?>" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" required="required" value="<?php echo $email; ?>" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plate number</label>
                        <input type="text" name="plate_number" required="required" value="<?php echo $pnumber; ?>" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Passenger Capacity</label>
                        <input type="text" name="passenger_capacity" required="required" value="<?php echo $passenger_capacity; ?>" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Route</label>
                        <select name="route" required="required" class="form-control">
                            <option hidden></option>
                            <option <?php if ($route == "Tabaco to Legazpi") echo "selected"; ?>>Tabaco to Legazpi</option>
                            <option <?php if ($route == "Sto.Domingo to Legazpi") echo "selected"; ?>>Sto.Domingo to Legazpi</option>
                            <option <?php if ($route == "Daraga to Legazpi") echo "selected"; ?>>Daraga to Legazpi</option>
                            <option <?php if ($route == "Tabaco to Legazpi") echo "selected"; ?>>Tabaco to Legazpi</option>
                            <option <?php if ($route == "Sto.Domingo to Legazpi") echo "selected"; ?>>Sto.Domingo to Legazpi</option>
                            <option <?php if ($route == "Daraga to Legazpi") echo "selected"; ?>>Daraga to Legazpi</option>
                            <option <?php if ($route == "Baao to Legazpi") echo "selected"; ?>>Baao to Legazpi</option>
                            <option <?php if ($route == "Manito to Legazpi") echo "selected"; ?>>Manito to Legazpi</option>
                            <option <?php if ($route == "Iriga to Legazpi") echo "selected"; ?>>Iriga to Legazpi</option>
                            <option <?php if ($route == "Camalig to Legazpi") echo "selected"; ?>>Camalig to Legazpi</option>
                        </select>
                    </div>
                
                    <button class="btn" type="submit" name="submit">Update</button>
                </form>
            </div>
        </section>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript">
            <?php if ($updateSuccess): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?php echo $_SESSION['success_message']; ?>',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'dashboard.php?id=<?php echo $id ?>';
                    }
                });
            <?php endif; ?>
        </script>
    </body>
</html>
