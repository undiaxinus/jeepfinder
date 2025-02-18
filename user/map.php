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
    <div id="map" style="height: 100vh;"></div>
</section>
<script>
    // Initialize the map
var map = L.map('map', { zoomControl: false, minZoom: 10 });
var routePolyline = L.polyline([], {
    // color: 'red'
}).addTo(map);
var userMarker;

// Add this variable at the top level of your script
var isPopupClosed = {};

// Add these variables at the top of your script
var isTracking = false;
var trackedJeep = null;

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
                showAlert('You have reached your destination!');
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

// Get the current location
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (position) {
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;
        map.setView([latitude, longitude], 17);
        updateUserLocation(); // Start tracking user location
    }, function (error) {
        console.error('Error getting user location:', error);
        map.setView([13.16472023105074, 123.75132122380849], 17); // Default location
        showAlert('Please enable location services to use all features');
    });
} else {
    console.error('Geolocation is not supported by this browser.');
    map.setView([13.16472023105074, 123.75132122380849], 17); // Default location
    showAlert('Your browser does not support location services');
}

// Define tile layers
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
var Stadia_AlidadeSmoothDark = L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.{ext}', {
    minZoom: 0,
    maxZoom: 20,
    attribution: '',
    ext: 'png'
});

// Set base layers
var baseLayers = {
    "Basic View": osmLayer,
    // "Satellite View": stadiaLayer,
    "Dark View": Stadia_AlidadeSmoothDark,
};

// Load the last selected layer from localStorage
var lastLayer = localStorage.getItem('lastLayer');
if (lastLayer && baseLayers[lastLayer]) {
    baseLayers[lastLayer].addTo(map);
} else {
    osmLayer.addTo(map); // Default layer
}


// Save the selected layer to localStorage
map.on('baselayerchange', function (eventLayer) {
    localStorage.setItem('lastLayer', eventLayer.name);
});

    L.control.layers(baseLayers).addTo(map);
    var doubleClickTimer;
    var isDoubleClick = false;
map.on('dblclick', function (e) {
    doubleClickTimer = setTimeout(function () {
        isDoubleClick = true;
        var destination = e.latlng;
        copyToClipboard(destination.lat + ',' + destination.lng);
        showAlert('Coordinates copied: ' + destination.lat + ',' + destination.lng);
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
    // Ensure we have valid positive numbers
    distance = Math.abs(parseFloat(distance)) || 0;
    
    // Set minimum speed to 20 km/h if speed is 0 or very low
    speed = Math.abs(parseFloat(speed));
    if (speed < 20) {
        speed = 20; // Minimum assumed speed of 20 km/h
    }
    
    // Add traffic factor (assume 30% slower during peak hours)
    const now = new Date();
    const hour = now.getHours();
    const isRushHour = (hour >= 7 && hour <= 9) || (hour >= 16 && hour <= 19);
    const trafficFactor = isRushHour ? 1.3 : 1;
    
    // Calculate time in hours, accounting for traffic
    const timeHours = (distance / speed) * trafficFactor;
    
    // Convert to hours and minutes
    const hours = Math.floor(timeHours);
    const minutes = Math.round((timeHours - hours) * 60);
    
    // Format the result
    if (hours === 0 && minutes === 0) {
        return { hours: 0, minutes: 1 }; // Minimum 1 minute ETA
    }
    
    // Cap maximum ETA at 24 hours
    if (hours > 24) {
        return { hours: 24, minutes: 0 };
    }
    
    return { hours, minutes };
}

// Helper function to format ETA for display
function formatETA(hours, minutes) {
    if (hours === 0) {
        return `${minutes} min`;
    } else if (minutes === 0) {
        return `${hours} hr`;
    }
    return `${hours}h ${minutes}m`;
}

function updateRealTimeData() {
    $.ajax({
        url: 'marker.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Received data:', data);
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker && !layer._icon.classList.contains('user-marker')) {
                    map.removeLayer(layer);
                }
            });

           // Attempt to retrieve the last known location from local storage
const cachedLocation = JSON.parse(localStorage.getItem('lastLocation'));
if (cachedLocation) {
    userMarker = L.marker(cachedLocation, {
        icon: L.divIcon({
            className: 'user-marker',
            html: `
                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="5" fill="rgba(255, 0, 0, 0.3)" class="ping-circle" /> <!-- Red ping circle -->
                    <circle cx="20" cy="20" r="5" fill="red" /> <!-- Solid red dot in the center -->
                </svg>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        })
    }).addTo(map);

    // Add animation to the ping circle
    const pingCircle = document.querySelector('.ping-circle');
    if (pingCircle) {
        pingCircle.style.animation = 'ping-animation 1.5s infinite';
    }
}

// CSS for the ping animation
const style = document.createElement('style');
style.innerHTML = `
    @keyframes ping-animation {
        0% {
            r: 5;   /* Start from the size of the solid red dot */
            opacity: 1;
        }
        50% {
            r: 18;  /* Grow to the outer circle size */
            opacity: 0.5;
        }
        100% {
            r: 5;   /* Return to the size of the solid red dot */
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);


            for (var i = 0; i < data.length; i++) {
                var id = data[i].id;
                var plateNumber = data[i].plateNumber;
                var route = data[i].route;
                var latitude = data[i].latitude;
                var longitude = data[i].longitude;
                var speed = data[i].speed;
                var passengerCount = data[i].passenger;
                var capacity = data[i].capacity;
                var rotate = data[i].rotation;
                var jeep = data[i].jeep;

                console.log('Processing data for ID ' + id + ':', latitude, longitude);
                var location = [latitude, longitude];

                // Original SVG marker for real-time data
                var markerIcon = L.divIcon({
                    className: 'custom-icon',
                    html: `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                            <!-- Apply a drop shadow filter -->
                            <defs>
                                <filter id="drop-shadow">
                                    <feDropShadow dx="2" dy="2" stdDeviation="3" flood-color="red"/>
                                </filter>
                            </defs>
                            <!-- The image with a drop shadow border effect -->
                            <image href="../img/${jeep}" width="32" height="32" transform="rotate(${rotate} 16 16)" filter="url(#drop-shadow)"/>
                        </svg>
                        `,
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });

                var marker = L.marker(location, {
                    icon: markerIcon,
                    plateNumber: plateNumber,
                    route: route,
                    passengerCount: passengerCount,
                    capacity: capacity,
                    riseOnHover: false,
                    riseOffset: 0
                }).addTo(map);

                // Use the user's last known location if location services are off
                var userLat, userLng;
                if (cachedLocation) {
                    userLat = cachedLocation[0];
                    userLng = cachedLocation[1];
                } else if (userMarker) {
                    userLat = userMarker.getLatLng().lat;
                    userLng = userMarker.getLatLng().lng;
                } else {
                    // Skip calculations if no user location is available
                    continue;
                }
                
                var distanceToUser = calculateDistance(latitude, longitude, userLat, userLng);
                var { hours, minutes } = calculateETAWithSpeed(distanceToUser, speed);
                
                updateMarkerContent(
                    marker,
                    plateNumber,
                    route,
                    passengerCount,
                    capacity,
                    distanceToUser,
                    speed,
                    hours,
                    minutes
                );

                // Only open popup if tracking this jeep
                if (currentPlateNumber === plateNumber && !isPopupClosed[plateNumber]) {
                    marker.openPopup();
                    // Force the popup to stay in position
                    marker.getPopup()._source = marker;
                }
                
                marker.on('click', function(e) {
                    const clickedPlateNumber = e.target.options.plateNumber;
                    
                    if (isTracking && currentPlateNumber === clickedPlateNumber) {
                        isTracking = false;
                        trackedJeep = null;
                        currentPlateNumber = null;
                        map.dragging.enable();
                        map.scrollWheelZoom.enable(); // Re-enable zoom when tracking stops
                        e.target.closePopup();
                        return;
                    }
                    
                    isTracking = true;
                    trackedJeep = e.target;
                    currentPlateNumber = clickedPlateNumber;
                    map.dragging.disable();
                    map.scrollWheelZoom.disable(); // Disable zoom when popup is open
                    isPopupClosed[clickedPlateNumber] = false;
                    
                    map.eachLayer(function(layer) {
                        if (layer instanceof L.Polyline) {
                            map.removeLayer(layer);
                        }
                    });
                    
                    forceJeepCenter(e.target);
                    e.target.openPopup();
                });

                // Only center on jeep if actively tracking
                if (isTracking && currentPlateNumber === plateNumber) {
                    forceJeepCenter(marker);
                    
                    // Re-open popup if it was closed
                    if (!isPopupClosed[plateNumber]) {
                        marker.openPopup();
                    }
                }
            }

            // Cache the user's current location when successfully retrieved
            navigator.geolocation.getCurrentPosition(function(position) {
                const currentLocation = [position.coords.latitude, position.coords.longitude];
                localStorage.setItem('lastLocation', JSON.stringify(currentLocation)); // Cache current location
                if (userMarker) {
                    userMarker.setLatLng(currentLocation); // Update userMarker position if it exists
                } else {
                    userMarker = L.marker(currentLocation, {
                        icon: L.divIcon({
                            className: 'user-marker',
                            html: `
                                <div style="position: relative; display: flex; justify-content: center; align-items: center;">
                                    <div style="background: radial-gradient(circle, rgba(0, 0, 255, 0.2) 0%, rgba(0, 0, 255, 0.1) 70%); 
                                                border: 2px solid blue; 
                                                border-radius: 50%; 
                                                width: 40px; 
                                                height: 40px; 
                                                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);">
                                    </div>
                                    <div style="background: blue; border-radius: 50%; width: 15px; height: 15px; position: absolute;"></div>
                                </div>
                            `,
                            iconSize: [40, 40],
                            iconAnchor: [20, 40]
                        })
                    }).addTo(map); // Add marker if it doesn't exist
                }
                // Do not set the map view to the user's location
            }, function() {
                console.warn('Unable to retrieve location; using cached location if available.');
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

// Add CSS to prevent popup transitions
const popupStyle = document.createElement('style');
popupStyle.textContent = `
    .leaflet-popup {
        transition: none !important;
    }
    .leaflet-popup-content-wrapper {
        transition: none !important;
    }
    .leaflet-popup-tip-container {
        transition: none !important;
    }
`;
document.head.appendChild(popupStyle);

updateRealTimeData(); 
setInterval(updateRealTimeData, 1000);

function getPassengerStatusColor(passengerCount, capacity) {
    const occupancyRate = (passengerCount / capacity) * 100;
    if (occupancyRate > 100) {
        return '#FF0000';    // Bright red for over capacity
    } else if (occupancyRate === 100) {
        return 'red';        // Regular red for full
    } else if (occupancyRate >= 80) {
        return 'orange';     // Nearly full
    }
    return 'green';         // Available
}

function getPassengerStatusText(passengerCount, capacity) {
    const occupancyRate = (passengerCount / capacity) * 100;
    if (occupancyRate > 100) {
        return '<span style="color: #FFFFFF; font-weight: bold;">⚠️ OVERLOADED! EXCEEDS CAPACITY!</span>';
    } else if (occupancyRate === 100) {
        return '<span style="color:  #FFFFFF; font-weight: bold;">⚠️ FULL</span>';
    } else if (occupancyRate >= 80) {
        return '<span style="color:  #FFFFFF; font-weight: bold;">⚠️ Nearly Full</span>';
    }
    return '<span style="color:  #FFFFFF;">Available</span>';
}

// Add these functions after your existing code
function requestNotificationPermission() {
    if ('Notification' in window) {
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                console.log('Notification permission granted');
            }
        });
    }
}

function sendNotification(title, options) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, options);
    }
}

function findNearestAvailableJeep(userLocation, route = null) {
    let nearestJeep = null;
    let shortestDistance = Infinity;

    map.eachLayer(function(layer) {
        if (layer instanceof L.Marker && layer.options.plateNumber) {
            // Skip if route is specified and doesn't match
            if (route && layer.options.route !== route) return;
            
            // Calculate occupancy rate
            const occupancyRate = (layer.options.passengerCount / layer.options.capacity) * 100;
            
            // Only consider jeeps that aren't full
            if (occupancyRate < 100) {
                const distance = calculateDistance(
                    userLocation.lat,
                    userLocation.lng,
                    layer.getLatLng().lat,
                    layer.getLatLng().lng
                );

                if (distance < shortestDistance) {
                    shortestDistance = distance;
                    nearestJeep = layer;
                }
            }
        }
    });

    return nearestJeep;
}

function monitorJeepProximity(jeep, userLocation, notificationThreshold = 1) { // threshold in kilometers
    const distance = calculateDistance(
        userLocation.lat,
        userLocation.lng,
        jeep.getLatLng().lat,
        jeep.getLatLng().lng
    );

    if (distance <= notificationThreshold) {
        sendNotification('Jeep Approaching!', {
            body: `${jeep.options.plateNumber} is ${distance.toFixed(2)}km away`,
            icon: '../img/sbmo.png',
            vibrate: [200, 100, 200]
        });
    }
}

// Initialize notification permission request when page loads
document.addEventListener('DOMContentLoaded', function() {
    requestNotificationPermission();
});

// Add route selection functionality
function addRouteSelector() {
    const routeSelector = document.createElement('select');
    routeSelector.id = 'routeSelector';
    routeSelector.className = 'route-selector';
    
    // Add default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Select Route';
    routeSelector.appendChild(defaultOption);
    
    // Define the available routes
    const availableRoutes = [
        "Tabaco to Legazpi",
        "Sto.Domingo to Legazpi",
        "Daraga to Legazpi",
        "Baao to Legazpi",
        "Manito to Legazpi",
        "Iriga to Legazpi",
        "Camalig to Legazpi"
    ];
    
    // Add route options
    availableRoutes.forEach(route => {
        const option = document.createElement('option');
        option.value = route;
        option.textContent = route;
        routeSelector.appendChild(option);
    });
    
    // Add change event listener
    routeSelector.addEventListener('change', function(e) {
        const selectedRoute = e.target.value;
        const userLoc = userMarker ? userMarker.getLatLng() : null;
        
        if (userLoc) {
            const nearestJeep = findNearestAvailableJeep(userLoc, selectedRoute);
            if (nearestJeep) {
                nearestJeep.openPopup();
                map.setView(nearestJeep.getLatLng());
                
                // Calculate distance and ETA
                const distance = calculateDistance(
                    userLoc.lat,
                    userLoc.lng,
                    nearestJeep.getLatLng().lat,
                    nearestJeep.getLatLng().lng
                );
                
                const speed = nearestJeep.options.speed || 27; // Default speed if not available
                const { hours, minutes } = calculateETAWithSpeed(distance, speed);
                
                // Show suggestion using SweetAlert2
                showJeepAlert(nearestJeep, distance, hours, minutes, speed, userLoc);
            } else {
                showAlert('No available jeeps found for this route');
            }
        } else {
            showAlert('Please enable location services to find nearby jeeps.');
        }
    });
    
    // Create container for map controls
    const mapControlsContainer = document.createElement('div');
    mapControlsContainer.className = 'map-controls-container';
    
    // Add route selector to container
    mapControlsContainer.appendChild(routeSelector);
    
    // Add the container to the map
    map.getContainer().appendChild(mapControlsContainer);
    
    // Move layer control into the container if it exists
    const layerControl = document.querySelector('.leaflet-control-layers');
    if (layerControl) {
        mapControlsContainer.appendChild(layerControl);
    }
}

// Update the map controls container styles
const headerStyle = document.createElement('style');
headerStyle.textContent = `
    header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        padding: 12px 20px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
    }

    header p {
        margin: 0;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        justify-content: center;
        font-family: 'Arial', sans-serif;
    }

    header span {
        padding: 8px 12px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        font-size: 13px;
        font-weight: 500;
        color: white;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    header span:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        header {
            padding: 8px 10px;
        }

        header p {
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 8px;
        }

        header span {
            font-size: 12px;
            padding: 6px 8px;
        }

        /* First row */
        header span#plateNumber,
        header span#route,
        header span#passengerCount {
            grid-row: 1;
        }

        /* Second row */
        header span#distanceToUser,
        header span#speed,
        header span#eta {
            grid-row: 2;
        }

        /* Individual column positions */
        header span#plateNumber { grid-column: 1; }
        header span#route { grid-column: 2; }
        header span#passengerCount { grid-column: 3; }
        header span#distanceToUser { grid-column: 1; }
        header span#speed { grid-column: 2; }
        header span#eta { grid-column: 3; }
    }

    /* Custom colors for each info type */
    header span#plateNumber {
        background: rgba(33, 150, 243, 0.3);
        border-color: rgba(33, 150, 243, 0.5);
    }

    header span#route {
        background: rgba(76, 175, 80, 0.3);
        border-color: rgba(76, 175, 80, 0.5);
    }

    header span#passengerCount {
        background: rgba(255, 193, 7, 0.3);
        border-color: rgba(255, 193, 7, 0.5);
    }

    header span#distanceToUser {
        background: rgba(0, 188, 212, 0.3);
        border-color: rgba(0, 188, 212, 0.5);
    }

    header span#speed {
        background: rgba(156, 39, 176, 0.3);
        border-color: rgba(156, 39, 176, 0.5);
    }

    header span#eta {
        background: rgba(244, 67, 54, 0.3);
        border-color: rgba(244, 67, 54, 0.5);
    }

    /* Add subtle animation for updates */
    @keyframes update-flash {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .update-flash {
        animation: update-flash 0.3s ease;
    }

    /* Updated Map controls container styles */
    .map-controls-container {
        position: absolute;
        top: 10px;  /* Changed from bottom to top */
        right: 10px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .route-selector {
        padding: 8px 12px;
        border-radius: 4px;
        background: white;
        border: 2px solid rgba(0, 0, 0, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 14px;
        width: 200px;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23007CB2%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
        background-repeat: no-repeat;
        background-position: right 12px top 50%;
        background-size: 12px auto;
    }

    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .map-controls-container {
            top: 10px;  /* Keep at top for mobile */
            right: 10px;
            width: auto;
        }

        .route-selector {
            width: 160px;
            padding: 10px;
            font-size: 13px;
        }
    }

    /* Extra small screens */
    @media (max-width: 320px) {
        .map-controls-container {
            top: 10px;  /* Keep at top for very small screens */
            right: 5px;
        }

        .route-selector {
            width: 140px;
            font-size: 12px;
        }
    }

    /* Landscape mode adjustments */
    @media (max-height: 500px) {
        .map-controls-container {
            top: 10px;  /* Keep at top for landscape mode */
        }
    }
`;
document.head.appendChild(headerStyle);

// Initialize the map controls
addRouteSelector();

// Remove the header element and its styles
const headerToRemove = document.querySelector('header');
if (headerToRemove) {
    headerToRemove.remove();
}

// Update the marker popup content with a better format
function updateMarkerContent(marker, plateNumber, route, passengerCount, capacity, distanceToUser, speed, hours, minutes) {
    // Set passengerCount to 0 if empty
    passengerCount = passengerCount || 0;
    
    const occupancyRate = (passengerCount / capacity) * 100;
    const statusColor = getPassengerStatusColor(passengerCount, capacity);
    const statusText = getPassengerStatusText(passengerCount, capacity);

    const etaText = formatETA(hours, minutes);
    
    const content = `
        <div class="marker-popup">
            <div class="popup-header">
                <div class="popup-row" style="background: #1e3c72; color: white; padding: 8px; border-radius: 4px 4px 0 0;">
                    <strong>${plateNumber}</strong>
                </div>
            </div>
            <div class="popup-row" style="padding: 8px;">
                <strong>Route:</strong> ${route}
            </div>
            <div class="popup-row" style="padding: 8px; background: ${statusColor}; color: white;">
                <strong>Passengers:</strong> ${passengerCount}/${capacity} ${statusText}
            </div>
            <div class="popup-row" style="padding: 8px; background: #f8f9fa;">
                <strong>Available Seats:</strong> ${Math.max(0, capacity - passengerCount)}
            </div>
            <div class="popup-row" style="padding: 8px;">
                <strong>Distance:</strong> ${distanceToUser.toFixed(2)} km
            </div>
            <div class="popup-row" style="padding: 8px;">
                <strong>Speed:</strong> ${speed} km/h
            </div>
            <div class="popup-row" style="padding: 8px; border-radius: 0 0 4px 4px;">
                <strong>ETA:</strong> ${etaText}
            </div>
        </div>
    `;

    const popup = marker.bindPopup(content, { 
        autoClose: false,
        closeOnClick: false,
        className: 'custom-popup',
        animate: false
    });
}

// Update the style block to include popup close button styles
const style = document.createElement('style');
style.textContent = `
    .custom-popup .leaflet-popup-content-wrapper {
        padding: 0;
        overflow: hidden;
        border-radius: 8px;
    }

    .custom-popup .leaflet-popup-content {
        margin: 0;
        min-width: 200px;
    }

    .marker-popup {
        font-family: Arial, sans-serif;
    }

    .popup-header {
        position: relative;
    }

    .popup-row {
        border-bottom: 1px solid #eee;
    }

    .popup-row:last-child {
        border-bottom: none;
    }

    .popup-row strong {
        font-weight: 600;
    }
`;
document.head.appendChild(style);

// Update your existing marker creation code to use the new function
// Example usage:
marker.on('add', function() {
    updateMarkerContent(
        marker,
        plateNumber,
        route,
        passengerCount,
        capacity,
        distanceToUser,
        speed,
        hours,
        minutes
    );
});

// Simple custom alert function
function showAlert(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'custom-alert';
    alertDiv.innerHTML = `
        <div class="alert-content">
            <div class="alert-message">${message}</div>
        </div>
    `;
    document.body.appendChild(alertDiv);

    // Auto remove after 3 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Add styles for the custom alert
const alertStyle = document.createElement('style');
alertStyle.textContent = `
    .custom-alert {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #1e3c72;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        z-index: 10000;
        font-family: Arial, sans-serif;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
        border-left: 4px solid #3498db;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .alert-content {
        display: flex;
        align-items: center;
    }

    .alert-message {
        margin: 0;
        font-weight: 500;
        letter-spacing: 0.3px;
    }
`;
document.head.appendChild(alertStyle);

// Example usage for nearest jeep alert
function showNearestJeepAlert(jeep) {
    const message = `
        <div style="text-align: left; line-height: 1.8;">
            <div><strong>Plate#:</strong> ${jeep.plateNumber}</div>
            <div><strong>Route:</strong> ${jeep.route}</div>
            <div><strong>Distance:</strong> ${jeep.distance.toFixed(2)} km</div>
            <div><strong>Passengers:</strong> ${jeep.passengerCount}/${jeep.capacity}</div>
            <div><strong>ETA:</strong> ${jeep.eta}</div>
        </div>
    `;
    showAlert(message);
}

// Replace the route selection alert
if (nearestJeep) {
    showNearestJeepAlert({
        plateNumber: nearestJeep.options.plateNumber,
        route: nearestJeep.options.route,
        distance: distanceToUser,
        passengerCount: nearestJeep.options.passengerCount,
        capacity: nearestJeep.options.capacity,
        eta: `${hours}h ${minutes}m`
    });
} else {
    showAlert('No available jeeps found for this route');
}

// Create custom modal alert
function showJeepAlert(nearestJeep, distance, hours, minutes, speed, userLoc) {
    const modalDiv = document.createElement('div');
    modalDiv.className = 'custom-modal';
    modalDiv.innerHTML = `
        <div class="modal-content">
            <h2>Nearest Available Jeep</h2>
            
            <div class="info-row">
                <div class="info-label">Plate#:</div>
                <div class="info-value">${nearestJeep.options.plateNumber}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Route:</div>
                <div class="info-value">${nearestJeep.options.route}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Distance:</div>
                <div class="info-value">${distance.toFixed(2)} km</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Passengers:</div>
                <div class="info-value">${nearestJeep.options.passengerCount}/${nearestJeep.options.capacity}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">ETA:</div>
                <div class="info-value">${hours}h ${minutes}m</div>
            </div>

            <button class="track-button">Track This Jeep</button>
        </div>
    `;
    document.body.appendChild(modalDiv);

    const modalStyle = document.createElement('style');
    modalStyle.textContent = `
        .custom-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .modal-content h2 {
            color: #24478f;
            text-align: center;
            font-size: 24px;
            margin: 0 0 25px 0;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.5;
        }

        .info-label {
            width: 120px;
            color: #666;
            font-weight: 500;
        }

        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }

        .track-button {
            display: block;
            width: 100%;
            padding: 12px;
            background: #24478f;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s ease;
        }

        .track-button:hover {
            background: #1a3366;
        }

        @media (max-width: 480px) {
            .modal-content {
                padding: 20px;
            }

            .info-row {
                font-size: 14px;
            }

            .info-label {
                width: 100px;
            }
        }
    `;
    document.head.appendChild(modalStyle);

    // Fix the track button functionality
    const trackButton = modalDiv.querySelector('.track-button');
    if (trackButton) {
        trackButton.addEventListener('click', function() {
            if (nearestJeep && userLoc) {
                isTracking = true;
                trackedJeep = nearestJeep;
                currentPlateNumber = nearestJeep.options.plateNumber;
                isPopupClosed[currentPlateNumber] = false;
                
                // Disable map dragging
                map.dragging.disable();
                
                // Force center on the jeep
                forceJeepCenter(nearestJeep);
                
                monitorJeepProximity(nearestJeep, userLoc);
                
                calculateRouteToUser(nearestJeep.getLatLng())
                    .then(route => {
                        if (route) {
                            L.geoJSON(route, {
                                style: { color: 'green' }
                            }).addTo(map);
                        }
                    });

                nearestJeep.openPopup();
            }
            modalDiv.remove();
        });
    }

    // Close modal when clicking outside
    modalDiv.addEventListener('click', (e) => {
        if (e.target === modalDiv) {
            modalDiv.remove();
        }
    });
}

// Make sure to call showJeepAlert with all required parameters
if (userLoc) {
    const nearestJeep = findNearestAvailableJeep(userLoc, selectedRoute);
    if (nearestJeep) {
        const distance = calculateDistance(
            userLoc.lat,
            userLoc.lng,
            nearestJeep.getLatLng().lat,
            nearestJeep.getLatLng().lng
        );
        const speed = nearestJeep.options.speed || 27; // Default speed if not available
        const { hours, minutes } = calculateETAWithSpeed(distance, speed);
        
        showJeepAlert(nearestJeep, distance, hours, minutes, speed, userLoc);
    } else {
        showAlert('No available jeeps found for this route');
    }
} else {
    showAlert('Please enable location services to find nearby jeeps.');
}

// Replace keepJeepCentered with forceJeepCenter
function forceJeepCenter(marker) {
    const markerPosition = marker.getLatLng();
    const offset = map.getSize().y * 0.1; // 10% offset from the center
    
    // Calculate the new center point with offset
    const point = map.project(markerPosition);
    point.y -= offset;
    const newCenter = map.unproject(point);
    
    // Immediately set view without animation
    map.setView(newCenter, map.getZoom(), {
        animate: false
    });
}

// Add handler to re-enable map dragging when tracking stops
function stopTracking() {
    isTracking = false;
    trackedJeep = null;
    currentPlateNumber = null;
    map.dragging.enable();
    map.scrollWheelZoom.enable(); // Re-enable zoom when tracking stops
}
</script>
</body>
</html>
