<?php
include '../connection/conn.php';
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $status = 'offline';
    $currentDateTime = date('Y-m-d H:i:s');
    $sqlUpdates = "UPDATE `user` SET `status` = ?, `login_time_out` = ? WHERE `user` = ?";
    $stmtUpdates = $conn->prepare($sqlUpdates);
    $stmtUpdates->bind_param("sss", $status, $currentDateTime, $id);
    if ($stmtUpdates->execute()) {
        $stmtUpdates->close();
        $conn->close();
        header('Location: logout1.php');
        exit();
    } else {
        echo "Error updating status: " . $conn->error;
    }
}
?>

