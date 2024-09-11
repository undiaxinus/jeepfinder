<?php
// Check if platenumber is set in the URL
if(isset($_GET['platenumber'])) {
    // Include your database connection file
    include 'conn.php';

    // Sanitize the input
    $platenumber = $_GET['platenumber'];
    $platenumber = mysqli_real_escape_string($conn, $platenumber);

    // Update the platenumber for ID 1
    $sql = "UPDATE `user` SET `platenumber` = '$platenumber' WHERE `id` = 1";

    if(mysqli_query($conn, $sql)) {
        echo "Platenumber updated successfully.";
    } else {
        echo "Error updating platenumber: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
