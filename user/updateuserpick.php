<?php
if(isset($_GET['platenumber'])) {
    include 'conn.php';
    $platenumber = $_GET['platenumber'];
    $platenumber = mysqli_real_escape_string($conn, $platenumber);
    $sql = "UPDATE `user` SET `platenumber` = '$platenumber' WHERE `id` = 1";
    if(mysqli_query($conn, $sql)) {
        echo "Platenumber updated successfully.";
    } else {
        echo "Error updating platenumber: " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
?>
