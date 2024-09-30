<?php
include("../connection/conn.php");
$currentDateTime = date('Y-m-d H:i:s');
$id = isset($_GET['id']) ? $_GET['id'] : '';
$sql = "UPDATE user SET login_time = '$currentDateTime' WHERE user_id = $id";
if ($conn->query($sql) === TRUE) {
    echo "Login time updated successfully";
} else {
    echo "Error updating login time: " . $conn->error;
}
$conn->close();
?>
