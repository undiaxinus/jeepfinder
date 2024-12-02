<?php 
session_start();
if ($_SESSION['Role'] != 'admin') {
    header('Location: ../index.html?error=Access denied'); 
    exit();
}

include "../connection/conn.php";

$sql = "SELECT * FROM `locate`";
$result = $conn->query($sql);

$sql = "SELECT COUNT(*) AS jeep FROM `locate` ";
$results = $conn->query($sql);
// Check if query was successful
if ($results->num_rows > 0) {
    $rows = $results->fetch_assoc();
    $jeep = $rows['jeep'];
} else {
    $jeep = 0;
}

$sql = "SELECT COUNT(*) AS status FROM `user` ";
$results = $conn->query($sql);
// Check if query was successful
if ($results->num_rows > 0) {
    $rows = $results->fetch_assoc();
    $user = $rows['status'];
} else {
    $user = 0;
}
$sql = "SELECT COUNT(*) AS online FROM `user` WHERE status = 'online' ";
$results = $conn->query($sql);
// Check if query was successful
if ($results->num_rows > 0) {
    $rows = $results->fetch_assoc();
    $online = $rows['online'];
} else {
    $online = 0;
}
$sql = "SELECT COUNT(*) AS offline FROM `user` WHERE status = 'offline' ";
$results = $conn->query($sql);
// Check if query was successful
if ($results->num_rows > 0) {
    $rows = $results->fetch_assoc();
    $offline = $rows['offline'];
} else {
    $offline = 0;
}
$conn->close();
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
        <style>
            .home-section {
                max-width: 100%;
            }
            .border {
                margin-left: 90px;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                padding: 20px;
            }
            .border1 {
                margin: 20px 90px;
            }
            a {
                text-decoration: none;
                color: #ffffff;
            }
            .card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 25px;
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            }
            .card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                    45deg,
                    rgba(255, 255, 255, 0.1),
                    rgba(255, 255, 255, 0.05)
                );
                z-index: 1;
            }
            .card i {
                font-size: 48px;
                color: rgba(255, 255, 255, 0.9);
                margin-bottom: 15px;
                position: relative;
                z-index: 2;
                transition: all 0.3s ease;
            }
            .card h2 {
                color: white;
                font-size: 24px;
                font-weight: 600;
                margin: 10px 0;
                position: relative;
                z-index: 2;
            }
            .card p {
                color: rgba(255, 255, 255, 0.7);
                font-size: 14px;
                position: relative;
                z-index: 2;
            }
            /* Card specific colors */
            .card.blue {
                background: linear-gradient(135deg, #4e54c8, #8f94fb);
            }
            .card.green {
                background: linear-gradient(135deg, #11998e, #38ef7d);
            }
            .card.red {
                background: linear-gradient(135deg, #eb3349, #f45c43);
            }
            .card.orange {
                background: linear-gradient(135deg, #ff8008, #ffc837);
            }
            /* Hover effects */
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.25);
            }
            .card:hover i {
                transform: scale(1.1);
            }
            table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                color: white;
                min-width: 800px;
            }
            th, td {
                text-align: center;  /* Center align all table content */
                padding: 15px 20px;
                vertical-align: middle;  /* Vertically center content */
            }
            th {
                background: rgba(76, 76, 255, 0.2);
                font-weight: 600;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid rgba(255, 255, 255, 0.1);
                white-space: nowrap;
            }
            tr:hover td {
                background: rgba(76, 76, 255, 0.1);
                transition: all 0.3s ease;
            }
            .search-container {
                margin-bottom: 10px;
            }
            .search-container input {
                width: 15%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 14px;
                right: 50px;
                position: absolute; 
                z-index:100;
            }
            .btn {
                padding: 5px 10px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 14px;
                cursor: pointer;
            }
            .btn.edit{
                width: 100%;
                margin-bottom: 5px;
            }
            .btn.delete {
                background-color: #f44336;
                width: 100%;
            }
            .addemergency {
                position: fixed;
                bottom: 80px;
                right: 30px;
                z-index: 1000;
            }

            .addemergency summary {
                list-style: none;
                cursor: pointer;
            }

            .addemergency summary i {
                width: 45px;
                height: 45px;
                font-size: 20px;
                color: white;
                background: #4CAF50;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
            }

            .addemergency summary::-webkit-details-marker {
                display: none;
            }

            /* Hover effect */
            .addemergency summary i:hover {
                transform: scale(1.05);
                background: #45a049;
            }

            @media (max-width: 768px) {
                .addemergency summary i {
                    width: 40px;
                    height: 40px;
                    font-size: 18px;
                }
            }

            .addemergency ul {
                position: absolute;
                bottom: 60px;
                right: 0;
                list-style: none;
                background: white;
                border-radius: 12px;
                padding: 8px 0;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                min-width: 150px;
            }

            .addemergency ul li {
                padding: 8px 20px;
                transition: background 0.3s ease;
            }

            .addemergency ul li a {
                color: #333 !important;
                text-decoration: none;
                display: block;
                font-size: 14px;
                font-weight: 500;
            }

            /* Animation for menu */
            .addemergency ul {
                animation: slideUp 0.3s ease-out;
            }

            @keyframes slideUp {
                0% {
                    opacity: 0;
                    transform: translateY(10px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .addemergency {
                    bottom: 70px;
                    right: 20px;
                }
                
                .addemergency summary {
                    width: 45px;
                    height: 45px;
                }
                
                .addemergency summary i {
                    font-size: 22px;
                }
            }
            .header-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 20px 90px;
                padding: 10px 0;
                position: relative;
                z-index: 100;
            }

            .header-container h2 {
                color: #fff;
                font-size: 24px;
                font-weight: 600;
                margin: 0;
            }

            .search-container {
                position: relative;
                margin: 0;
                padding: 0;
            }

            .search-container input {
                width: 250px;
                padding: 10px 15px;
                padding-left: 40px;
                border: none;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                color: white;
                font-size: 14px;
                transition: all 0.3s ease;
                margin: 0;
            }

            .search-container::before {
                content: '\f002';
                font-family: 'BoxIcons';
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: rgba(255, 255, 255, 0.6);
                font-size: 16px;
            }

            .search-container input::placeholder {
                color: rgba(255, 255, 255, 0.6);
            }

            .search-container input:focus {
                outline: none;
                background: rgba(255, 255, 255, 0.15);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            @media (max-width: 768px) {
                .header-container {
                    flex-direction: row;
                    margin: 15px;
                    padding: 5px;
                }

                .header-container h2 {
                    font-size: 18px;
                }

                .search-container input {
                    width: 150px;
                    padding: 8px 15px 8px 35px;
                    font-size: 13px;
                }

                .search-container::before {
                    font-size: 14px;
                    left: 12px;
                }
            }

            /* For very small screens */
            @media (max-width: 480px) {
                .header-container {
                    gap: 10px;
                }

                .header-container h2 {
                    font-size: 16px;
                }

                .search-container input {
                    width: 120px;
                }
            }

            #map {
                height: 70vh;
                border-radius: 0 0 15px 15px;
                overflow: hidden;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }

            .table-container {
                padding: 20px;
                background: rgba(255, 255, 255, 0.05);
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                color: white;
                min-width: 800px; /* Ensures table doesn't get too squeezed */
            }

            th {
                background: rgba(76, 76, 255, 0.2);
                padding: 15px 20px;
                text-align: center;
                font-weight: 600;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid rgba(255, 255, 255, 0.1);
                white-space: nowrap;
            }

            td {
                padding: 15px 20px;
                background: rgba(255, 255, 255, 0.05);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            /* Column widths */
            th:nth-child(1), td:nth-child(1) { width: 30%; }  /* Drivers Information */
            th:nth-child(2), td:nth-child(2) { width: 30%; }  /* Jeepney Data */
            th:nth-child(3), td:nth-child(3) { width: 20%; text-align: center; }  /* Status */
            th:nth-child(4), td:nth-child(4) { width: 20%; text-align: center; }  /* Action */

            /* Mobile Responsive Styles */
            @media (max-width: 768px) {
                .border1 {
                    margin: 10px;
                }
                
                .table-container {
                    padding: 10px;
                }

                table {
                    min-width: 600px;
                }
                
                th, td {
                    padding: 10px;
                    font-size: 12px;
                }
                
                /* Adjust text wrapping for mobile */
                td {
                    white-space: normal;
                    word-break: break-word;
                }
                
                /* Make buttons stack nicely */
                .btn {
                    padding: 6px 10px;
                    margin-bottom: 4px;
                    font-size: 12px;
                }
                
                /* Status badge adjustments */
                .status {
                    padding: 4px 8px;
                    font-size: 11px;
                }
            }

            /* Custom scrollbar for the table container */
            .table-container::-webkit-scrollbar {
                height: 6px;
            }

            .table-container::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 3px;
            }

            .table-container::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.3);
                border-radius: 3px;
            }

            .table-container::-webkit-scrollbar-thumb:hover {
                background: rgba(255, 255, 255, 0.4);
            }

            /* Data cell formatting */
            td strong {
                display: none; /* Hide labels on desktop */
            }

            /* Mobile optimized data display */
            @media (max-width: 480px) {
                table {
                    min-width: 400px;
                }
                
                td strong {
                    display: inline-block;
                    margin-right: 5px;
                    font-weight: 600;
                    color: rgba(255, 255, 255, 0.8);
                }
                
                /* Stack information in cells */
                td div {
                    margin-bottom: 4px;
                }
                
                /* Adjust column widths for mobile */
                th:nth-child(1), td:nth-child(1) { width: 10%; }
                th:nth-child(2), td:nth-child(2) { width: 30%; }
                th:nth-child(3), td:nth-child(3) { width: 25%; }
                th:nth-child(4), td:nth-child(4) { width: 15%; }
                th:nth-child(5), td:nth-child(5) { width: 20%; }
            }

            /* Ensure buttons remain clickable and visible */
            .btn {
                min-width: 60px;
                white-space: nowrap;
            }

            /* Add these styles */
            .info-row {
                margin-bottom: 8px;
                line-height: 1.4;
                text-align: left;
            }

            .info-row:last-child {
                margin-bottom: 0;
            }

            .info-row strong {
                display: inline-block;
                color: rgba(255, 255, 255, 0.7);
                margin-right: 5px;
                font-weight: 500;
            }

            .driver-info, .jeepney-info {
                padding: 10px 0;
            }

            td {
                vertical-align: top;
                background: rgba(255, 255, 255, 0.05);
            }

            @media (max-width: 768px) {
                .info-row {
                    margin-bottom: 6px;
                    font-size: 12px;
                }
                
                .info-row strong {
                    font-size: 12px;
                }
            }

            .addemergency li a {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 15px;
                color: black;
                text-decoration: none;
                transition: all 0.3s ease;
            }

            .addemergency li a i {
                font-size: 20px;
                color: #4776E6;
            }

            .addemergency li a span {
                font-size: 14px;
            }

            .addemergency li a:hover {
                background: #f5f5f5;
                border-radius: 8px;
            }

            /* Status styling */
            .status {
                display: inline-block;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 500;
            }

            /* Action buttons container */
            .action-buttons {
                display: flex;
                gap: 8px;
                justify-content: center;  /* Center the action buttons */
            }

            /* Mobile responsive adjustments */
            @media (max-width: 768px) {
                table {
                    min-width: 600px;
                }
                
                th, td {
                    padding: 10px;
                    font-size: 12px;
                }
            }

            /* Mobile responsive styles */
            @media (max-width: 768px) {
                .border {
                    margin-left: 0; /* Remove left margin on mobile */
                    grid-template-columns: repeat(2, 1fr); /* 2 columns for mobile */
                    gap: 15px;
                    padding: 15px;
                }

                .card {
                    padding: 15px;
                }

                .card i {
                    font-size: 36px;
                }

                .card h2 {
                    font-size: 18px;
                    margin: 8px 0;
                }

                .card p {
                    font-size: 12px;
                }
            }

            /* Very small screens */
            @media (max-width: 480px) {
                .border {
                    gap: 10px;
                    padding: 10px;
                }
            }
        </style>
    </head>
    <body>
        <?php include "navigation.php" ?>
        <section class="home-section">
        <div class="border">
                <!-- Card 1 -->
                <a href="users.php?id=<?php echo $id ?>">
                    <div class="card blue">
                        <i class='bx bxs-group'></i>
                        <h2>Users <?php echo $user ?></h2>
                        <p>Registered accounts</p>
                    </div>
                </a>
                <!-- Card 2 -->
                <a href="online.php?id=<?php echo $id ?>">
                    <div class="card green">
                        <i class='bx bxs-user'></i>
                        <h2>Online <?php echo $online ?></h2>
                        <p>Currently active users</p>
                    </div>
                </a>
                <!-- Card 3 -->
                <a href="offline.php?id=<?php echo $id ?>">
                    <div class="card red">
                        <i class='bx bxs-ghost'></i>
                        <h2>Offline <?php echo $offline ?></h2>
                        <p>Inactive user accounts</p>
                    </div>
                </a>
                <!-- Card 4 -->
                <a href="dashboard.php?id=<?php echo $id ?>">
                    <div class="card orange">
                        <i class='bx bxs-car'></i>
                        <h2>Jeepney <?php echo $jeep ?></h2>
                        <p>Registered jeepneys</p>
                    </div>
                </a>
            </div>
            <div class="header-container">
                <h2>Jeepney Monitoring</h2>
                <div class="search-container">
                    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search jeepney...">
                </div>
            </div>
            <div class="border1">
                <div class="card1">
                    <div class="table-container">
                        <table id="jeepneyTable">
                            <thead>
                                <tr>
                                    <th>Drivers Information</th>
                                    <th>Jeepney data information</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Table content will be dynamically loaded -->
                            </tbody>
                        </table>
                    </div>
                    <div id="map"></div>
                </div>
            </div>
            <div class="addemergency" id="toggleAddEmergency">
                <details id="addEmergencyDetails">
                    <summary><i class='bx bxs-plus-circle'></i></summary>
                    <ul>
                        <li>
                            <a href='Add_Account.php?id=<?php echo $id ?>'>
                                <i class='bx bxs-user-plus'></i>
                                <span>Add Account</span>
                            </a>
                        </li>
                        <li>
                            <a href='Add_Jeepney.php?id=<?php echo $id ?>'>
                                <i class='bx bxs-car'></i>
                                <span>Add Jeepney</span>
                            </a>
                        </li>
                    </ul>
                </details>
            </div>
            <br><br><br><br><br><br>
        </section>
    </body>
    <script>
        let isAddEmergencyEnabled = false;
        function fetchTableData() {
            let searchInput = document.getElementById('searchInput').value.trim();
            if (searchInput === "") {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', 'fetch_data.php?id=<?php echo $id; ?>', true);
                xhr.onload = function() {
                    if (this.status === 200) {
                        document.getElementById('jeepneyTable').getElementsByTagName('tbody')[0].innerHTML = this.responseText;
                    }
                };
                xhr.send();
            }
        }

        function searchTable() {
            let input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("jeepneyTable");
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }

        document.getElementById('toggleAddEmergency').addEventListener('change', function() {
            isAddEmergencyEnabled = this.checked;
        });
        fetchTableData();
        setInterval(fetchTableData, 1000);
    </script>
    <script>
        var map = L.map('map', { zoomControl: false, minZoom: 10 });
        var routePolyline = L.polyline([], { 
            color: 'red',
            dashArray: '10 5'
        }).addTo(map);
        var userMarker;
        
        function updateDirectionLine() {
            if (userMarker && routePolyline) {
                var userLocation = userMarker.getLatLng();
                var destination = routePolyline.getLatLngs()[0];
                var lineCoordinates = [userLocation, destination];
                routePolyline.setLatLngs(lineCoordinates);
            }
        }    
        
        function updateUserLocation(destination) {
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(function (position) {
                    var userLocation = [position.coords.latitude, position.coords.longitude];

                if (!userMarker) {
                    var customIconSvg = `
                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="18" fill="rgba(0, 0, 255, 0.3)" />
                            <circle cx="20" cy="20" r="7" fill="orange" />
                        </svg>`;
                        
                        var customIconUrl = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(customIconSvg);
                        var customIcon = L.icon({
                            iconUrl: customIconUrl,
                            iconSize: [40, 40], 
                            iconAnchor: [20, 20],
                        });
                        userMarker = L.marker(userLocation, {
                            icon: customIcon,
                            draggable: true
                        }).addTo(map);
                        userMarker.bindPopup("<b>Your Location</b>").openPopup();
                        userMarker.on('dragend', function (event) {
                            var updatedDestination = event.target.getLatLng();
                            updateRoute(updatedDestination);
                            updateUserLocation(updatedDestination);
                        });
                    } else {
                        userMarker.setLatLng(userLocation);
                    }
                    routePolyline.addLatLng(userLocation);
                    var distanceToDestination = userMarker.getLatLng().distanceTo(destination);
                    if (distanceToDestination < 10) {
                        Swal.fire({
                            title: 'Destination Reached!',
                            text: 'You have reached your destination!',
                            icon: 'success',
                            confirmButtonText: 'Okay'
                        });
                    }
                    updateRoute(destination);
                    updateDirectionLine();
                }, function (error) {
                    console.error('Error getting user location:', error.message);
                }, { enableHighAccuracy: true });
            } else {
                console.error('Geolocation is not supported by this browser.');
            }
        }
        updateUserLocation();
        updateDirectionLine();

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var latitude = position.coords.latitude;
                var longitude = position.coords.longitude;
                map.setView([latitude, longitude], 17);
            }, function(error) {
                console.error('Error getting user location:', error);
                map.setView([13.16472023105074, 123.75132122380849], 17);
            });
        } else {
            console.error('Geolocation is not supported by this browser.');
            map.setView([13.16472023105074, 123.75132122380849], 17);
        }

        var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            minZoom: 0,
            maxZoom: 20,
            attribution: ''
        });

        var stadiaLayer = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_satellite/{z}/{x}/{y}{r}.{ext}', {
            minZoom: 0,
            maxZoom: 20,
            attribution: '',
            ext: 'jpg'
        });
        var MtbMap = L.tileLayer('http://tile.mtbmap.cz/mtbmap_tiles/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &amp; USGS'
        });

        osmLayer.addTo(map); 

        var Stadia_AlidadeSmoothDark = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.{ext}', {
            minZoom: 0,
            maxZoom: 20,
            attribution: '',
            ext: 'png'
        });

        var baseLayers = {
            "Basic View": osmLayer,
            "Satellite View": stadiaLayer,
            "Dark View": Stadia_AlidadeSmoothDark, 
        };
        L.control.layers(baseLayers).addTo(map);

        var doubleClickTimer;
        var isDoubleClick = false;
        map.on('dblclick', function (e) {
            doubleClickTimer = setTimeout(function () {
                isDoubleClick = true;
                var destination = e.latlng;
                copyToClipboard(destination.lat + ',' + destination.lng);
                Swal.fire({
                    title: 'Coordinates Copied!',
                    text: 'Latitude and Longitude copied to clipboard: ' + destination.lat + ',' + destination.lng,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500
                });
            }, 1000);
        });
        map.doubleClickZoom.disable();
        map.on('click', function (e) {
            clearTimeout(doubleClickTimer);
            if (!isDoubleClick) {
                var clickedDestination = e.latlng;
            }
            isDoubleClick = false;
        });

        function copyToClipboard(text) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
        var routingKey = '5b3ce3597851110001cf6248cea0e0c6d544416c8e414f71f901b721';

        function updateRoute(destination) {
            var apiUrl = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${routingKey}&coordinates=${userMarker.getLatLng().lng},${userMarker.getLatLng().lat}|${destination.lng},${destination.lat}`;

            fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                var routeCoordinates = data.features[0].geometry.coordinates;
                routePolyline.setLatLngs(routeCoordinates);
            })
            .catch(error => console.error('Error fetching route:', error));
        }
        
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; 
            const φ1 = lat1 * Math.PI / 180; 
            const φ2 = lat2 * Math.PI / 180; 
            const Δφ = (lat2 - lat1) * Math.PI / 180; 
            const Δλ = (lon2 - lon1) * Math.PI / 180; 
            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const distance = R * c;
            const distanceKM = distance / 1000;
            return distanceKM; 
        }

        function calculateETA(distance) {
            const averageSpeed = 27; 
            const timeHours = distance / averageSpeed;
            const hours = Math.floor(timeHours);
            const minutes = Math.round((timeHours - hours) * 60);
            return { hours, minutes };
        }

        function calculateRouteToUser(jeepLocation) {
            var startPoint = jeepLocation;
            var endPoint = userMarker.getLatLng();

            var url = 'https://router.project-osrm.org/route/v1/driving/' + startPoint.lng + ',' + startPoint.lat + ';' + endPoint.lng + ',' + endPoint.lat + '?geometries=geojson';

            return fetch(url)
            .then(response => response.json())
            .then(data => {
                return data.routes[0].geometry;
            })
            .catch(error => {
                console.error('Error fetching route:', error);
                return null;
            });
        }
        var currentPlateNumber = "";
        function calculateETAWithSpeed(distance, speed) {
            var timeHours = distance / speed;
            var hours = Math.floor(timeHours);
            var minutes = Math.round((timeHours - hours) * 60);
            return { hours, minutes };
        }
        function updateRealTimeData() {
            $.ajax({
            url: 'marker.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('Received data:', data);
                map.eachLayer(function(layer) {
                    if (layer instanceof L.Marker) {
                        map.removeLayer(layer);
                    }
                });

                if (userMarker) {
                    userMarker.addTo(map);
                }

                for (var i = 0; i < data.length; i++) {
                    var id = data[i].id;
                    var plateNumber = data[i].plateNumber;
                    var route = data[i].route;
                    var latitude = data[i].latitude;
                    var longitude = data[i].longitude;
                    var speed = data[i].speed;
                    var passengerCount = data[i].passenger;
                    var rotate = data[i].rotation;
                    var jeep = data[i].jeep;
                    console.log('Processing data for ID ' + id + ':', latitude, longitude);
                    var location = [latitude, longitude];
                    var markerIcon = L.divIcon({
                        className: 'custom-icon',
                        html: `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><image href="../img/${jeep}" width="32" height="32" transform="rotate(${rotate} 16 16)"/></svg>`,
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
                    });

                    var marker = L.marker(location, {
                        icon: markerIcon,
                        plateNumber: plateNumber,
                        route: route,
                        passengerCount: passengerCount
                    }).addTo(map);

                    var distanceToUser = calculateDistance(latitude, longitude, userMarker.getLatLng().lat, userMarker.getLatLng().lng);
                    var { hours, minutes } = calculateETAWithSpeed(distanceToUser, speed);
                    marker.bindPopup("<b>Plate#: " + plateNumber + "</b><br>Route: " + route + "<br>Passenger: " + passengerCount + "/25" + "<br>Distance to User: " + distanceToUser.toFixed(2) + " km" + "<br>Speed: " + speed + " km/h" + "<br>ETA: " + hours + " hours " + minutes + " minutes", { autoClose: false });
                    
                    if (currentPlateNumber === plateNumber) {
                        marker.fireEvent('click');
                    }

                    marker.on('click', function(e) {
                    map.eachLayer(function(layer) {
                        if (layer instanceof L.Polyline) {
                            map.removeLayer(layer);
                        }
                    });
                    currentPlateNumber = e.target.options.plateNumber;
                    var distanceToUser = calculateDistance(e.target.getLatLng().lat, e.target.getLatLng().lng, userMarker.getLatLng().lat, userMarker.getLatLng().lng);
                    var { hours, minutes } = calculateETAWithSpeed(distanceToUser, speed);
                    e.target.setPopupContent("<b>Plate#: " + currentPlateNumber + "</b><br>Route: " + e.target.options.route + "<br>Passenger: " + e.target.options.passengerCount + "/25" + "<br>Distance to User: " + distanceToUser.toFixed(2) + " km" + "<br>Speed: " + speed + " km/h" + "<br>ETA: " + hours + " hours " + minutes + " minutes");
                    calculateRouteToUser(e.target.getLatLng())
                    .then(route => {
                        if (route) {
                            L.geoJSON(route, {
                                style: { color: 'green' }
                            }).addTo(map);
                            } else {
                                console.error('Error: No route found.');
                            }
                        });
                        e.target.openPopup();
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', error);
            }
        });
    }
    updateRealTimeData(); 
    setInterval(updateRealTimeData, 1000); 
    </script>
</html>
