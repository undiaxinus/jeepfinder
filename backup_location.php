<?php
session_start();

include_once 'connection/conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure you have PHPMailer installed via composer

// Add this function for sending OTP
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'undiaxinus@gmail.com'; // Replace with your email
        $mail->Password = 'ptjihcapoaqbrily'; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('undiaxinus@gmail.com', 'Location Tracker');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Device ID Verification OTP';
        $mail->Body = "Your OTP for device verification is: <b>$otp</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Add this endpoint to handle OTP verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_otp') {
        $device_id = $_POST['device_id'];
        $sql = "SELECT email FROM locate WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $device_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['device_id'] = $device_id;
            $_SESSION['otp_time'] = time(); // Add timestamp for OTP expiration
            
            if (sendOTP($row['email'], $otp)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Device ID not found']);
        }
        exit;
    }

    if ($_POST['action'] === 'verify_otp') {
        $entered_otp = $_POST['otp'];
        
        // Check if OTP session exists and hasn't expired (10 minutes expiration)
        if (isset($_SESSION['otp']) && isset($_SESSION['otp_time']) && 
            (time() - $_SESSION['otp_time']) <= 600) {
            
            if ($entered_otp == $_SESSION['otp']) {
                // OTP is valid - set device as verified in session
                $_SESSION['device_verified'] = $_SESSION['device_id'];
                
                // Clear OTP session variables
                unset($_SESSION['otp']);
                unset($_SESSION['otp_time']);
                unset($_SESSION['device_id']);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'OTP has expired or is invalid']);
        }
        exit;
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['latitude'])) {
    // Parse incoming data
    $device_id = isset($_POST['ID']) ? $_POST['ID'] : '';
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : '';
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : '';
    $speed = isset($_POST['speed']) ? $_POST['speed'] : '';
    $bearing = isset($_POST['rotation']) ? $_POST['rotation'] : '';

    // Check if device is verified in session
    if (isset($_SESSION['device_verified']) && $_SESSION['device_verified'] === $device_id) {
        // Update MySQL database using prepared statement
        $sql = "UPDATE locate SET latitude = ?, longitude = ?, speed = ?, rotation = ?, resdate = NOW() WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddddi", $latitude, $longitude, $speed, $bearing, $device_id);

        if ($stmt->execute()) {
            echo "";
        } else {
            echo "Error updating record: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    }
}

// Add this after the existing POST handlers, before the HTML
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_passenger'])) {
    $id = $_POST['device_id'];
    $change = $_POST['change'];

    // First get current passenger count
    $sql = "SELECT passenger FROM locate WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Calculate new passenger count
    $newCount = max(0, ($row['passenger'] ?? 0) + $change); // Ensure count doesn't go below 0
    
    // Update passenger count
    $sql = "UPDATE locate SET passenger = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newCount, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'count' => $newCount]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update passenger count']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        
        .tracking-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .form-control[readonly] {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
        }

        label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .status-indicator {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            border-radius: 8px;
            background: #e8f5e9;
            color: #2e7d32;
        }

        @media (max-width: 576px) {
            .tracking-container {
                padding: 15px;
            }
        }

        .clock-display {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-top: 10px;
            font-size: 1.2em;
            color: #2c3e50;
            border: 2px solid #e9ecef;
        }

        .time-text {
            margin-left: 8px;
            font-weight: 600;
        }

        /* Dark mode styles */
        body.dark-mode {
            background: #1a1a1a;
        }

        .dark-mode .tracking-container {
            background: #2d2d2d;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }

        .dark-mode .tracking-header {
            color: #ffffff;
        }

        .dark-mode .text-muted {
            color: #bbbbbb !important;
        }

        .dark-mode .form-control {
            background-color: #3d3d3d;
            border-color: #4d4d4d;
            color: #ffffff;
        }

        .dark-mode .form-control[readonly] {
            background-color: #333333;
        }

        .dark-mode label {
            color: #ffffff;
        }

        .dark-mode .clock-display {
            background: #3d3d3d;
            border-color: #4d4d4d;
            color: #ffffff;
        }

        .dark-mode .status-indicator {
            background: #2e4c32;
            color: #a8e6ad;
        }

        /* Dark mode toggle button */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            background: #ffffff;
            border: 2px solid #e9ecef;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .dark-mode .theme-toggle {
            background: #2d2d2d;
            border-color: #4d4d4d;
            color: #ffffff;
        }

        .compass-container {
            width: 40px;
            height: 40px;
            border: 2px solid #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .compass-arrow {
            color: #2c3e50;
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .dark-mode .compass-container {
            background: #3d3d3d;
            border-color: #4d4d4d;
        }

        .dark-mode .compass-arrow {
            color: #ffffff;
        }

        .dark-mode .modal-content {
            background-color: #2d2d2d;
            color: #ffffff;
        }
        
        .dark-mode .modal-header {
            border-bottom-color: #4d4d4d;
        }
        
        .dark-mode .modal-footer {
            border-top-color: #4d4d4d;
        }
        
        .dark-mode .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .modal-content {
            background-color: #fff;
            border-radius: 15px;
        }

        #otpInput {
            letter-spacing: 2px;
            font-size: 20px;
            padding: 10px;
            text-align: center;
        }

        .dark-mode #otpInput {
            background-color: #333;
            color: #fff;
            border-color: #444;
        }

        .dark-mode #otpInput:focus {
            background-color: #444;
            border-color: #555;
            color: #fff;
        }

        .passenger-btn {
            padding: 12px 24px;
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            transition: all 0.3s ease;
        }

        .passenger-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .passenger-btn:active {
            transform: translateY(0);
        }

        .passenger-btn i {
            margin-right: 8px;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .d-flex.align-items-center.gap-3 {
                flex-direction: column;
                align-items: stretch !important;
            }

            .passenger-btn {
                padding: 15px 24px;
                font-size: 1.2rem;
                width: 100%;
                margin-top: 8px;
            }

            #passenger {
                text-align: center;
                font-size: 1.2rem;
                padding: 12px;
            }
        }
    </style>
    <link rel="manifest" href="manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="modal fade" id="customAlert" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="alertMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="container">
        <div class="tracking-container">
            <div class="tracking-header">
                <h2>📍 Location Tracker</h2>
                <p class="text-muted">Real-time location monitoring</p>
                <div class="clock-display">
                    <i class="far fa-clock"></i>
                    <span id="currentTime" class="time-text"></span>
                </div>
            </div>

            <form method="POST" action="" id="locationForm">
                <div class="form-group">
                    <label for="latitude">
                        <i class="fas fa-map-marker-alt"></i> Latitude
                    </label>
                    <input type="number" class="form-control" id="latitude" name="latitude" 
                           step="any" required placeholder="Waiting for location..." readonly>
                </div>

                <div class="form-group">
                    <label for="longitude">
                        <i class="fas fa-map-marker-alt"></i> Longitude
                    </label>
                    <input type="number" class="form-control" id="longitude" name="longitude" 
                           step="any" required placeholder="Waiting for location..." readonly>
                </div>

                <div class="form-group">
                    <label for="speed">
                        <i class="fas fa-tachometer-alt"></i> Speed (m/s)
                    </label>
                    <input type="number" class="form-control" id="speed" name="speed" 
                           step="0.01" min="0" required placeholder="Calculating speed..." readonly>
                </div>

                <div class="form-group">
                    <label for="rotation">
                        <i class="fas fa-compass"></i> Bearing (degrees)
                    </label>
                    <div class="d-flex align-items-center">
                        <input type="number" class="form-control" id="rotation" name="rotation" 
                               step="0.01" min="0" required placeholder="Calculating bearing..." readonly>
                        <div class="compass-container ms-3">
                            <i class="fas fa-arrow-up compass-arrow" id="compassArrow"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="passenger">
                        <i class="fas fa-users"></i> Passenger Count
                    </label>
                    <div class="d-flex align-items-center gap-3">
                        <input type="number" class="form-control" id="passenger" name="passenger" 
                               required placeholder="Current passengers" readonly>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success passenger-btn" onclick="updatePassengerCount(1)">
                                <i class="fas fa-plus fa-lg"></i>
                                <span class="ms-2">Add</span>
                            </button>
                            <button type="button" class="btn btn-danger passenger-btn" onclick="updatePassengerCount(-1)">
                                <i class="fas fa-minus fa-lg"></i>
                                <span class="ms-2">Remove</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deviceId">
                        <i class="fas fa-fingerprint"></i> Device ID
                    </label>
                    <input type="number" class="form-control" id="deviceId" name="ID" 
                           required placeholder="Enter Device ID">
                    <div class="mt-2">
                        <button type="button" class="btn btn-primary" onclick="saveDeviceId()">Save Device ID</button>
                        <button type="button" class="btn btn-secondary" onclick="editDeviceId()">Edit Device ID</button>
                    </div>
                </div>
            </form>

            <div class="status-indicator" id="statusIndicator">
                Tracking Active
            </div>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Existing JavaScript code remains the same -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let lastPosition = null;  // Add this at the start of your script

        async function registerServiceWorker() {
            if ('serviceWorker' in navigator && 'periodicSync' in registration) {
                try {
                    const registration = await navigator.serviceWorker.register('service-worker.js');
                    
                    // Request permission for background sync
                    const status = await navigator.permissions.query({
                        name: 'periodic-background-sync',
                    });

                    if (status.state === 'granted') {
                        // Register periodic sync with a minimum interval of 1 minute
                        await registration.periodicSync.register('location-sync', {
                            minInterval: 60 * 1000, // 1 minute in milliseconds
                        });
                    }
                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            registerServiceWorker();
            loadInitialPassengerCount();
        });

        function startTracking() {
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(showPosition, showError, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                });
                
                // Update both form and passenger count regularly
                setInterval(() => {
                    submitForm();
                    fetchPassengerCount();
                }, 1000);
            } else {
                showCustomAlert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            document.getElementById("latitude").value = position.coords.latitude;
            document.getElementById("longitude").value = position.coords.longitude;
            document.getElementById("speed").value = (position.coords.speed || 0).toFixed(2);
            
            // Calculate bearing if we have a previous position
            if (lastPosition) {
                const bearing = calculateBearing(
                    lastPosition.coords.latitude,
                    lastPosition.coords.longitude,
                    position.coords.latitude,
                    position.coords.longitude
                );
                document.getElementById("rotation").value = bearing.toFixed(2);
                
                // Update compass arrow rotation
                const compassArrow = document.getElementById("compassArrow");
                compassArrow.style.transform = `rotate(${bearing}deg)`;
            }
            
            // Store current position for next calculation
            lastPosition = position;
            
            // Update status indicator
            document.getElementById("statusIndicator").innerHTML = "Location Updated";
            setTimeout(() => {
                document.getElementById("statusIndicator").innerHTML = "Tracking Active";
            }, 1000);
        }

        // Add this new function to calculate bearing between two points
        function calculateBearing(lat1, lon1, lat2, lon2) {
            const toRad = angle => angle * Math.PI / 180;
            const toDeg = rad => rad * 180 / Math.PI;

            const φ1 = toRad(lat1);
            const φ2 = toRad(lat2);
            const Δλ = toRad(lon2 - lon1);

            const y = Math.sin(Δλ) * Math.cos(φ2);
            const x = Math.cos(φ1) * Math.sin(φ2) -
                     Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);

            let bearing = toDeg(Math.atan2(y, x));
            
            // Normalize to 0-360
            bearing = (bearing + 360) % 360;
            
            return bearing;
        }

        function submitForm() {
            const formData = new FormData(document.getElementById('locationForm'));
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .catch(error => console.error('Error:', error));
        }

        function showError(error) {
            let errorMessage = "";
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "Please enable location permissions for this site.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "Location request timed out.";
                    break;
                case error.UNKNOWN_ERROR:
                    errorMessage = "An unknown error occurred.";
                    break;
            }
            showCustomAlert(errorMessage);
            document.getElementById("statusIndicator").innerHTML = "Tracking Error";
            document.getElementById("statusIndicator").style.backgroundColor = "#ffebee";
            document.getElementById("statusIndicator").style.color = "#c62828";
        }

        // Updated clock function for 12-hour format
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call to display time immediately

        // Dark mode toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const icon = themeToggle.querySelector('i');

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            // Update icon
            if (body.classList.contains('dark-mode')) {
                icon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            }
        });

        // Device ID handling
        function saveDeviceId() {
            const deviceId = document.getElementById('deviceId').value;
            if (deviceId) {
                // Show loading state
                Swal.fire({
                    title: 'Sending OTP',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send request to generate and send OTP
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'send_otp',
                        'device_id': deviceId
                    }).toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ask for OTP using SweetAlert2
                        Swal.fire({
                            title: 'Enter OTP',
                            text: 'Please enter the OTP sent to your email',
                            input: 'text',
                            inputAttributes: {
                                autocapitalize: 'off',
                                maxlength: 6,
                                autocomplete: 'off',
                                pattern: '[0-9]*'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Verify',
                            showLoaderOnConfirm: true,
                            backdrop: true,
                            preConfirm: (otp) => {
                                if (!otp) {
                                    Swal.showValidationMessage('Please enter OTP');
                                    return false;
                                }
                                return otp;
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                            if (result.isConfirmed) {
                                verifyOTP(result.value);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to send OTP'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while sending OTP'
                    });
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Device ID',
                    text: 'Please enter a Device ID'
                });
            }
        }

        function verifyOTP(otp) {
            Swal.fire({
                title: 'Verifying OTP',
                text: 'Please wait...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            const deviceId = document.getElementById('deviceId').value;

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'verify_otp',
                    'otp': otp
                }).toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Save device ID and make input readonly
                    localStorage.setItem('deviceId', deviceId);
                    document.getElementById('deviceId').readOnly = true;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Device ID verified and saved successfully!',
                        showConfirmButton: true
                    }).then(() => {
                        // Start tracking only after successful verification
                        startTracking();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: data.message || 'Invalid OTP',
                        showConfirmButton: true,
                        confirmButtonText: 'Try Again',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            saveDeviceId(); // Allow retry
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while verifying OTP'
                });
            });
        }

        function editDeviceId() {
            document.getElementById('deviceId').readOnly = false;
            document.getElementById('deviceId').focus();
        }

        // Load saved Device ID on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Only start tracking if device is already verified (from previous session)
            const deviceId = localStorage.getItem('deviceId');
            if (deviceId) {
                document.getElementById('deviceId').value = deviceId;
                document.getElementById('deviceId').readOnly = true;
                
                // Check if device is verified in session before starting tracking
                fetch('check_verification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'device_id': deviceId
                    }).toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.verified) {
                        startTracking();
                    } else {
                        // If not verified, prompt for verification
                        saveDeviceId();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            
            loadInitialPassengerCount();
        });

        function showCustomAlert(message) {
            const alertModal = new bootstrap.Modal(document.getElementById('customAlert'));
            document.getElementById('alertMessage').textContent = message;
            alertModal.show();
        }

        function updatePassengerCount(change) {
            const deviceId = document.getElementById('deviceId').value;
            if (!deviceId) {
                showCustomAlert("Please set a Device ID first");
                return;
            }

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'update_passenger': true,
                    'device_id': deviceId,
                    'change': change
                }).toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove manual update since it will be updated by the interval
                    const message = change > 0 ? "Passenger added" : "Passenger removed";
                    Swal.fire({
                        icon: 'success',
                        title: message,
                        text: `Current passenger count: ${data.count}`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update passenger count'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating passenger count'
                });
            });
        }

        // Add this to your existing document.addEventListener('DOMContentLoaded', ...) function
        // This will load the initial passenger count when the page loads
        function loadInitialPassengerCount() {
            const deviceId = localStorage.getItem('deviceId');
            if (deviceId) {
                fetch(`get_passenger_count.php?device_id=${deviceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('passenger').value = data.count;
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Add this function to the JavaScript section
        function fetchPassengerCount() {
            const deviceId = document.getElementById('deviceId').value;
            if (deviceId) {
                fetch(`get_passenger_count.php?device_id=${deviceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('passenger').value = data.count;
                    }
                })
                .catch(error => console.error('Error fetching passenger count:', error));
            }
        }
    </script>

    <!-- Add these before closing </body> tag -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
