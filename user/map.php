<?php
//map.php
session_start();
if($_SESSION['Role'] != 'user'){
header('Location: ../index.html?error=Access denied'); 
 exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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




</head>
<style type="text/css">
    header {
        width: 100%;
    }

    p {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        padding: 10px;
        margin: 0;
        font-size: 11px;
    }

    p span {
        display: inline-block;
        text-align: center;
        overflow: hidden;
    }

    @media screen and (max-width: 600px) {
        .header-content p {
            grid-template-columns: repeat(2, 1fr);
        }

        p span {
            display: inline-block;
            width: 100px;
            text-align: center;
            overflow: hidden;
            font-size: 7px;
        }
    }
</style>
<body style="overflow: hidden;">
<?php include "navigation.php" ?>
<section class="home-section">
    <header hidden>
        <p>
            <span id="plateNumber">Plate#:</span>
            <span id="route">Route:</span>
            <span id="passengerCount">Passenger:</span>
            <span id="distanceToUser">Distance:</span>
            <span id="speed">Speed:</span>
            <span id="eta">ETA:</span>
        </p>
    </header>

    <div id="map" style="height: 100vh;"></div>

</section>

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

</body>
</html>
