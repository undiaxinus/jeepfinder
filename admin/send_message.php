<?php
session_start();
$servername = "location";
$username = "root";
$password = "";
$dbname = "location";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sender_name = $_POST['sender_name'];
    $receiver_name = $_POST['receiver_name'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO message (sender_name, receiver_name, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $sender_name, $receiver_name, $message);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}

$conn->close();
?>
