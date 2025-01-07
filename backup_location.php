<?php

include_once 'connection/conn.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['latitude'])) {
    // Parse incoming data
    $id = isset($_POST['ID']) ? $_POST['ID'] : '';
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : '';
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : '';
    $speed = isset($_POST['speed']) ? $_POST['speed'] : '';
    $bearing = isset($_POST['rotation']) ? $_POST['rotation'] : '';

    // Update MySQL database using prepared statement
    $sql = "UPDATE locate SET latitude = ?, longitude = ?, speed = ?, rotation = ?, resdate = NOW() WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddddi", $latitude, $longitude, $speed, $bearing, $id);

    if ($stmt->execute()) {
        echo "";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
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
    </style>
    <link rel="manifest" href="manifest.json">
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
            startTracking();
        });

        function startTracking() {
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(showPosition, showError, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                });
                
                // Remove device orientation listener since we'll calculate bearing from movement
                setInterval(submitForm, 1000);
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
            .then(response => response.text())
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
                localStorage.setItem('deviceId', deviceId);
                document.getElementById('deviceId').readOnly = true;
                showCustomAlert('Device ID saved successfully!');
            } else {
                showCustomAlert('Please enter a Device ID');
            }
        }

        function editDeviceId() {
            document.getElementById('deviceId').readOnly = false;
            document.getElementById('deviceId').focus();
        }

        // Load saved Device ID on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedDeviceId = localStorage.getItem('deviceId');
            if (savedDeviceId) {
                document.getElementById('deviceId').value = savedDeviceId;
                document.getElementById('deviceId').readOnly = true;
            }
        });

        function showCustomAlert(message) {
            const alertModal = new bootstrap.Modal(document.getElementById('customAlert'));
            document.getElementById('alertMessage').textContent = message;
            alertModal.show();
        }
    </script>
</body>
</html>
