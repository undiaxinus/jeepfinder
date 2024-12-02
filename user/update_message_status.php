<?php
session_start();
include '../connection/conn.php';

$messageId = $_POST['message_id'];

$sql = "UPDATE message SET message_status = 'read' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $messageId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update message status']);
}

$stmt->close();
$conn->close();
?> 