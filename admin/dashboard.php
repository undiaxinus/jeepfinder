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
            gap: 10px;
            justify-items: center;
        }
        .border1 {
            margin-left: 70px;
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 10px;
            justify-items: center;
        }
        a {
            text-decoration: none;
            color: #ffffff;
        }
        .card {
            opacity: 80%;
            margin-top: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            width: 95%;
            transition: transform 0.3s ease;
        }
        .card1 {
            opacity: 80%;
            margin-top: 30px;
            background-color: orange;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 95%;
            transition: transform 0.3s ease;
        }
        .card1 h2{
             margin-top:70px
        }
        .border i {
            margin-left: -60px;
            opacity: 50%;
            position: absolute;
            font-size: 110px;
            margin-top: -30px;
            border-radius: 50%;
            padding: 10px;
            justify-content: center;
            align-items: center;
            filter: grayscale(50%);
            transition: transform 0.3s ease;
        }
        .card:hover, .border i:hover {
            transform: scale(1.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
            table-layout: fixed; /* Ensures that table cells don't expand beyond their fixed size */
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f2f2f2;
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
            margin-top: 2vh;
            right: 50px;
            top: 180px; 
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
            bottom: 20px;
            right: 20px; 
            z-index: 2000;
        }
        .addemergency details {
            position: relative;

        }
        .addemergency summary {
            list-style: none;
            cursor: pointer;
            font-size: 9px;
        }
        .addemergency ul {
            display: none;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .addemergency details[open] ul {
            width: 250px;
            display: block;
            position: absolute;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            top: -120px;
            right: 40px;
            z-index: 1000;
        }
        .addemergency ul li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .addemergency ul li:last-child {
            border-bottom: none;
        }
        .addemergency ul li:hover {
            background-color: #f0f0f0;
        }
        .addemergency i{
            font-size: 50px;
            color:  #007bff;
        }
        @media only screen and (max-width: 768px) {
            .border {
                margin-left: 10px;
                grid-template-columns: repeat(2, 1fr);
            }
            .border1 {
                margin-left: 0px;
                grid-template-columns: repeat(1, 1fr);
            }
            .card {
                opacity: 80%;
                margin-top: 30px;
                background-color: #ffffff;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 20px;
                text-align: center;
                width: 95%;
                transition: transform 0.3s ease;
            }
            .border i {
                margin-left: -75px;
                opacity: 50%;
                position: absolute;
                font-size: 120px;
            }
            .search-container input {
                width: 20%;
                padding: 5px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 10px;
                margin-left: 80%;
                margin-top: 2vh;
                right: 30px;
                top: 380px;
            }

            .card h2 {
                font-size: 20px;
            }
            .card p {
                font-size: 15px;
            }
            .card1 {
                padding: 5px;
                padding-top: 5px;
                padding-bottom: 0px;
                background-color: orange;
                width: 100%;
            }
            .card1 h2 {
                font-size: 15px;
                margin-top: 20px;
            }
            th, td {
                font-size: 10px;
            text-overflow: ellipsis; /* Ensures text is truncated with ellipsis */
            white-space: nowrap; /* Prevents text from wrapping */
        }
        .btn {
            padding: 1px 5px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 10px;
            cursor: pointer;
        }
        .addemergency {
                bottom: 70px;
                right: 20px; 
                position: fixed;
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
                <div class="card" style="background-color: blue;">
                    <i class='bx bxs-group'></i>
                    <h2>Users <?php echo $user ?></h2>
                    <p>This is the content of total account.</p>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="online.php?id=<?php echo $id ?>">
                
                <div class="card" style="background-color: green;">
                    <i class='bx bxs-user'></i>
                    <h2>Online <?php echo $online ?></h2>
                    <p>This is the content of total account online.</p>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="offline.php?id=<?php echo $id ?>">
                <div class="card" style="background-color: red;">
                    <i class='bx bxs-ghost'></i>
                    <h2>Offline <?php echo $offline ?></h2>
                    <p>This is the content of total account offline.</p>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="dashboard.php?id=<?php echo $id ?>">
                <div class="card" style="background-color: orange;">
                    <i class='bx bxs-car'></i>
                    <h2>Jeepney <?php echo $jeep ?></h2>
                    <p>This is the content of total jeepney.</p>
                </div>
            </a>
        </div>
        <div class="search-container">
                    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search..">
                </div>
        <div class="border1">
            <div class="card1">
                
                <h2 style="color: #fff;">Jeepney monitoring</h2>
                <div class="table-container">
                    <table id="jeepneyTable">
                        <tr>
                            <th>ID</th>
                            <th>Drivers Information</th>
                            <th>Jeepney data information</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        
                    </table>
                    <hr style="border: 2px black solid">
                </div>
                <div id="map" style="height: 70vh;"></div>
            </div>
        </div>
        <div class="addemergency" id="toggleAddEmergency">
                    <details id="addEmergencyDetails">
                        <summary><i class='bx bxs-plus-circle'></i></summary>
                        <ul>
                            <li><a href='Add_Account.php?id=<?php echo $id ?>' style="color:black;"> Add Account</a></li>
                            <li><a href='Add_Jeepney.php?id=<?php echo $id ?>' style="color:black;">Add Jeepney</a></li>
                            <li><a href='Add_Emergency_Contact.php?id=<?php echo $id ?>' style="color:black;">Add Emergency Contact</a></li>
                        </ul>
                    </details>
                </div>
        <br><br><br><br><br><br>
    </section>
</body>
<script>
        // Define a flag to check if add emergency is enabled
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


        // Function to search the table
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

        // Add event listener to the checkbox
        document.getElementById('toggleAddEmergency').addEventListener('change', function() {
            isAddEmergencyEnabled = this.checked;
        });

        // Fetch data initially
        fetchTableData();

        // Refresh table data every 1 second if search input is empty
        setInterval(fetchTableData, 1000);
    </script>

<script>
    var map = L.map('map', { zoomControl: false, minZoom: 10 });


    var routePolyline = L.polyline([], { // Add routePolyline declaration here
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

                /* if (!userMarker) {
                    var customIcon = L.icon({
                        iconUrl: '../img/persons.png',
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
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
                } */

                if (!userMarker) {
    // Define the SVG markup for the custom icon
    var customIconSvg = `
        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <!-- Large transparent blue circle -->
            <circle cx="20" cy="20" r="18" fill="rgba(0, 0, 255, 0.3)" />
            <!-- Small orange circle -->
            <circle cx="20" cy="20" r="7" fill="orange" />
        </svg>
    `;

    // Convert the SVG markup to a data URL
    var customIconUrl = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(customIconSvg);

    // Create the custom icon
    var customIcon = L.icon({
        iconUrl: customIconUrl,
        iconSize: [40, 40], // Set the size of the icon
        iconAnchor: [20, 20], // Set the anchor point of the icon
    });

    // Create the marker with the custom icon
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

    // Call updateDirectionLine initially
    updateDirectionLine();

    // Get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            map.setView([latitude, longitude], 17);
        }, function(error) {
            console.error('Error getting user location:', error);
            // If there's an error, set default view to a specific location
            map.setView([13.16472023105074, 123.75132122380849], 17);
        });
    } else {
        console.error('Geolocation is not supported by this browser.');
        // If geolocation is not supported, set default view to a specific location
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

    osmLayer.addTo(map); // Add OpenStreetMap layer by default

    var Stadia_AlidadeSmoothDark = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.{ext}', {
    minZoom: 0,
    maxZoom: 20,
    attribution: '',
    ext: 'png'
});

// Add Stadia_AlidadeSmoothDark layer to baseLayers object
var baseLayers = {
    "Basic View": osmLayer,
    "Satellite View": stadiaLayer,
    "Dark View": Stadia_AlidadeSmoothDark, // Added as the third option
    
};


/*
// Create a custom control for 3D view
var control3D = L.Control.extend({
    options: {
        position: 'topright'
    },
    onAdd: function(map) {
        var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');

        // Use the appropriate icon code for the 3D view button
        container.innerHTML = '<button onclick="redirectTo3DMap()" style="height:35px; border:none; width:45px"><i class="bx bx-building-house" style="font-size: 40px; background: white;color: gray;"></i></button>';

        return container;
    }
});

map.addControl(new control3D());
function redirectTo3DMap() {
    // Redirect to the 3D map page
    window.location.href = '3dmap.php?id=<?php echo $id ?>';
} */



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

// Disable zooming on double click
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
        const R = 6371e3; // Radius of the Earth in meters
        const φ1 = lat1 * Math.PI / 180; // Latitude of point 1 in radians
        const φ2 = lat2 * Math.PI / 180; // Latitude of point 2 in radians
        const Δφ = (lat2 - lat1) * Math.PI / 180; // Difference in latitude in radians
        const Δλ = (lon2 - lon1) * Math.PI / 180; // Difference in longitude in radians

        // Haversine formula
        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        // Distance in meters
        const distance = R * c;

        // Distance in kilometers
        const distanceKM = distance / 1000;

        return distanceKM; // Return distance in kilometers
    }

    function calculateETA(distance) {
        // Assuming average speed of the jeep in kilometers per hour
        const averageSpeed = 27; // Adjust this value according to your scenario

        // Calculate time in hours
        const timeHours = distance / averageSpeed;

        // Extract hours and minutes
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

 // Define a variable to store the currently displayed plate number
var currentPlateNumber = "";

// Function to calculate estimated time of arrival (ETA) based on speed
function calculateETAWithSpeed(distance, speed) {
    // Calculate time in hours
    var timeHours = distance / speed;

    // Extract hours and minutes
    var hours = Math.floor(timeHours);
    var minutes = Math.round((timeHours - hours) * 60);

    return { hours, minutes };
}

// Function to update real-time data
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


// Create a marker for each jeep with custom options
var marker = L.marker(location, {
    icon: markerIcon,
    plateNumber: plateNumber,
    route: route,
    passengerCount: passengerCount
}).addTo(map);


                // Calculate distance to user
                var distanceToUser = calculateDistance(latitude, longitude, userMarker.getLatLng().lat, userMarker.getLatLng().lng);

                // Calculate ETA using speed
                var { hours, minutes } = calculateETAWithSpeed(distanceToUser, speed);

                // Update the popup content with distance, speed, and ETA information
                marker.bindPopup("<b>Plate#: " + plateNumber + "</b><br>Route: " + route + "<br>Passenger: " + passengerCount + "/25" + "<br>Distance to User: " + distanceToUser.toFixed(2) + " km" + "<br>Speed: " + speed + " km/h" + "<br>ETA: " + hours + " hours " + minutes + " minutes", { autoClose: false });


                // Check if this marker's plate number matches the current displayed plate number
                if (currentPlateNumber === plateNumber) {
                    // Trigger the click event to update the header information
                    marker.fireEvent('click');
                }

                marker.on('click', function(e) {
                    // Remove existing route lines
                    map.eachLayer(function(layer) {
                        if (layer instanceof L.Polyline) {
                            map.removeLayer(layer);
                        }
                    });

                    // Update the currently displayed plate number
                    currentPlateNumber = e.target.options.plateNumber;

                    // Calculate distance to user
                    var distanceToUser = calculateDistance(e.target.getLatLng().lat, e.target.getLatLng().lng, userMarker.getLatLng().lat, userMarker.getLatLng().lng);

                   
                    // Calculate ETA using speed
                    var { hours, minutes } = calculateETAWithSpeed(distanceToUser, speed);

                    // Update the popup content with distance, speed, and ETA information
                    e.target.setPopupContent("<b>Plate#: " + currentPlateNumber + "</b><br>Route: " + e.target.options.route + "<br>Passenger: " + e.target.options.passengerCount + "/25" + "<br>Distance to User: " + distanceToUser.toFixed(2) + " km" + "<br>Speed: " + speed + " km/h" + "<br>ETA: " + hours + " hours " + minutes + " minutes");

                    // Calculate and display the route to the user
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

                    // Open the popup
                    e.target.openPopup();
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

// Call the update function initially and then every second
updateRealTimeData(); // Call the function to initially load jeep markers and update header content
setInterval(updateRealTimeData, 1000); // Update data every 1 second

</script>

</html>
