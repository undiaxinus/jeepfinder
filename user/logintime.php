<?php
// logintime.php
include("../connection/conn.php");

// Get the current date and time
$currentDateTime = date('Y-m-d H:i:s');

// Get user ID from the URL parameter
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Update the login_time field for the user with the specified ID
$sql = "UPDATE user SET login_time = '$currentDateTime' WHERE user_id = $id";
if ($conn->query($sql) === TRUE) {
    echo "Login time updated successfully";
} else {
    echo "Error updating login time: " . $conn->error;
}

$conn->close();
?>
