function updateRealTimeData() {
    $.ajax({
        url: 'marker.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Received data:', data);

            map.eachLayer(function (layer) {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });

            if (userMarker) {
                userMarker.addTo(map);
            }

            var nearestJeep = null;
            var nearestDistance = Infinity;
            var chosenJeepDistance = Infinity; // Initialize chosen jeep distance to a large number

            for (var i = 0; i < data.length; i++) {
                var id = data[i].id;
                var plateNumber = data[i].plateNumber;
                var route = data[i].route;
                var latitude = data[i].latitude;
                var longitude = data[i].longitude;
                var passengerCount = data[i].passenger;

                console.log('Processing data for ID ' + id + ':', latitude, longitude);

                var location = L.latLng(latitude, longitude);
                var distanceToUser = userMarker.getLatLng().distanceTo(location);

                // Check if the distance to the user is less than the chosen jeep's distance
                if (distanceToUser < chosenJeepDistance) {
                    chosenJeepDistance = distanceToUser;
                }

                // Check if the distance to the user is less than the nearest distance
                if (distanceToUser < nearestDistance) {
                    nearestDistance = distanceToUser;
                    nearestJeep = {
                        plateNumber: plateNumber,
                        route: route,
                        passengerCount: passengerCount,
                        distanceToUser: distanceToUser
                    };
                }

                var markerIcon = L.icon({
                    iconUrl: '../img/jeep.png',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });

                // Create a marker for each jeep with custom options
                var marker = L.marker(location, { icon: markerIcon, plateNumber: plateNumber, route: route, passengerCount: passengerCount }).addTo(map);
                marker.bindPopup("<b>Plate#: " + plateNumber + "</b><br>Route: " + route + "<br>Passenger: " + passengerCount + "/25");

                // Add click event handler to each marker
                marker.on('click', function (e) {
                    // Update header with clicked marker information
                    updateHeader(e.target.options);

                    // Update header information every second
                    clearInterval(headerUpdateInterval); // Clear any existing interval
                    headerUpdateInterval = setInterval(function() {
                        updateHeader(e.target.options);
                    }, 1000);
                });
            }

            // Update header with nearest jeep information if there's any
            if (nearestJeep) {
                updateHeader(nearestJeep);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

function updateHeader(nearestJeep) {
    // Calculate ETA
    var { hours, minutes } = calculateETA(nearestJeep.distanceToUser);
    var etaText = hours + ' hour' + (hours !== 1 ? 's' : '') + ' ' + minutes + ' minute' + (minutes !== 1 ? 's' : '');

    // Update header content with nearest jeep information
    document.getElementById('plateNumber').textContent = "Plate#: " + nearestJeep.plateNumber;
    document.getElementById('route').textContent = "Route: " + nearestJeep.route;
    document.getElementById('passengerCount').textContent = "Passenger: " + nearestJeep.passengerCount + "/25";
    document.getElementById('distanceToUser').textContent = "Distance to User: " + nearestJeep.distanceToUser.toFixed(2) + " meters";
    document.getElementById('speed').textContent = "Speed: 27"; // Assuming constant speed for now
    document.getElementById('eta').textContent = "ETA: " + etaText;
}

// Initialize headerUpdateInterval variable
var headerUpdateInterval;

// Call the updateRealTimeData function initially and then every second
updateRealTimeData(); // Call the function to initially load jeep markers and update header content
setInterval(updateRealTimeData, 1000); // Update data every 1 second
