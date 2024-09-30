<?php
    include '../connection/conn.php';
    if(isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];    
        $status = 'offline';
        $sqlUpdates = "UPDATE `user` SET `status` = ? WHERE `user` = ?";
        $stmtUpdates = $conn->prepare($sqlUpdates);
        $stmtUpdates->bind_param("ss", $status, $id);
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
