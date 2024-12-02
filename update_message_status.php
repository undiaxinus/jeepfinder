<?php
include_once("../connection/connect.php");
$conn = connection();

if (isset($_POST['contact_id'])) {
    $contact_id = $_POST['contact_id'];
    $sql = "UPDATE message SET message_status = 'read' WHERE receiver_name = '$contact_id' AND message_status = 'unread'";
    if ($conn->query($sql) === TRUE) {
        echo "Message status updated successfully.";
    } else {
        echo "Error updating message status: " . $conn->error;
    }
}
?>