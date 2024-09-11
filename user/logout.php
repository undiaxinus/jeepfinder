<?php
// logout.php
include '../connection/conn.php';

// Check if 'id' parameter is provided in the URL
if(isset($_GET['id']) && !empty($_GET['id'])) {
    // Sanitize the 'id' parameter to prevent SQL injection
    $id = $_GET['id'];
    
    // Define status
    $status = 'offline';
    $currentDateTime = date('Y-m-d H:i:s');

    // Prepare and execute update query
    $sqlUpdates = "UPDATE `user` SET `status` = ?, `login_time_out` = ? WHERE `user` = ?";
    $stmtUpdates = $conn->prepare($sqlUpdates);

    // Bind parameters and execute
    $stmtUpdates->bind_param("sss", $status, $currentDateTime, $id);
    if ($stmtUpdates->execute()) {
        // Status updated successfully
        $stmtUpdates->close();
        
        // Close database connection
        $conn->close();
        
        // Redirect to logout.php or any other page
        header('Location: logout1.php');
        exit(); // Ensure that script execution stops after redirection
    } else {
        // Error updating status
        echo "Error updating status: " . $conn->error;
    }
}
?>

