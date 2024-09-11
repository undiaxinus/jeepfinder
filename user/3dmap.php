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
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Display buildings in 3D</title>
<script src="https://cdn.maptiler.com/maptiler-sdk-js/v1.2.0/maptiler-sdk.umd.js"></script>
<link href="https://cdn.maptiler.com/maptiler-sdk-js/v1.2.0/maptiler-sdk.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  body {margin: 0; padding: 0;}
  #map {position: absolute; top: 0; bottom: 0; width: 100%;}
  .custom-marker {
    background-color: #337ab7;
    border-radius: 50%;
    width: 20px; /* Adjust the width and height according to your preference */
    height: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
  }
</style>
</head>
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
maptilersdk.config.apiKey = '08fwEOzJzCDUTKAiPyqa';
const map = new maptilersdk.Map({
  container: 'map', // container's id or the HTML element to render the map
  style: maptilersdk.MapStyle.STREETS,
  center: [123.734243, 13.139349], // starting position [lng, lat]
  zoom: 14, // starting zoom
  minZoom: 14 // set the minimum zoom level
});

map.on('dblclick', function(e) {
  const coordinates = e.lngLat.toArray().map(coord => coord.toFixed(6)).reverse().join(', ');
  copyToClipboard(coordinates);
  Swal.fire({
    icon: 'success',
    title: 'Location Copied!',
    text: 'The coordinates ' + coordinates + ' have been copied to the clipboard.',
    showConfirmButton: false,
    timer: 1500
  });
});
;

function copyToClipboard(text) {
  const textarea = document.createElement('textarea');
  textarea.value = text;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand('copy');
  document.body.removeChild(textarea);
}
// Disable zooming on double click
map.doubleClickZoom.disable();

// Function to calculate distance between two points using Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371; 
  const dLat = deg2rad(lat2 - lat1);
  const dLon = deg2rad(lon2 - lon1);
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  const d = R * c; // Distance in km
  return d; 
}

function deg2rad(deg) {
  return deg * (Math.PI / 180);
}

// Call the update function initially and then every second
updateRealTimeData(); // Call the function to initially load jeep markers and update header content
setInterval(updateRealTimeData, 1000); // Update data every 1 second

function updateRealTimeData() {
    // Fetch marker data from marker.php
    fetch('marker.php')
        .then(response => response.json())
        .then(data => {
            
            // Get user's current location using Geolocation API
            navigator.geolocation.getCurrentPosition(position => {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;

                data.forEach(markerData => {
                    // Calculate distance between marker and user
                    const distanceToUser = calculateDistance(markerData.latitude, markerData.longitude, userLat, userLng);

                    const marker = new maptilersdk.Marker({
                        element: createMarkerElement(markerData), // Create a marker element
                    })
                        .setLngLat([markerData.longitude, markerData.latitude]) // Marker coordinates (lng, lat)
                        .setPopup(new maptilersdk.Popup().setHTML("<h1>" + markerData.plateNumber + "</h1><p>Route: " + markerData.route + "<br>Speed: " + markerData.speed + " km/h" + "<br>Passengers: " + markerData.passenger + "<br>Distance to User: " + distanceToUser.toFixed(2) + " km</p>")) // Popup content
                        .addTo(map); // Add the marker to the map
                });
            }, error => {
                console.error('Error getting user location:', error);
            });
        })
        .catch(error => console.error('Error fetching marker data:', error));
}


function createMarkerElement(markerData) {
    // Create a new SVG element
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
    svg.setAttribute("width", "32");
    svg.setAttribute("height", "32");
    svg.setAttribute("viewBox", "0 0 32 32");

    // Create an image element inside the SVG
    const image = document.createElementNS("http://www.w3.org/2000/svg", "image");
    image.setAttribute("href", `../img/${markerData.jeep}`);
    image.setAttribute("width", "32");
    image.setAttribute("height", "32");
    image.setAttribute("transform", `rotate(${markerData.rotation} 16 16)`);

    svg.appendChild(image);

    return svg;
}
</script>

</body>
</html>
