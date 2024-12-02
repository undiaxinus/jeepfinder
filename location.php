<?php

include_once 'connection/conn.php';

// Parse incoming data
$id = $_POST['ID']; // Assuming the ID is sent from Arduino
$message = $_POST['message']; // Assuming the message is sent from Arduino
$latitude = $_POST['lat'];
$longitude = $_POST['lon'];
$speed = $_POST['speed'];
$bearing = $_POST['rotation']; // Assuming the bearing is sent from Arduino

// Update MySQL database
$sql = "UPDATE locate SET passenger = '$message', latitude = '$latitude', longitude = '$longitude', speed = '$speed', rotation = '$bearing', resdate = NOW()  WHERE ID = '$id'";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();

?>
