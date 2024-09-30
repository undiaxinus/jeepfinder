<?php
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
        </svg>
    `;
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
</body>
</html>
