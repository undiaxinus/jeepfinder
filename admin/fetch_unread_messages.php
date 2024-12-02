<?php
session_start();
include '../connection/conn.php';

$userId = $_GET['id']; // Assuming the user ID is passed as a query parameter
$sql = "SELECT COUNT(*) as unread_count FROM message WHERE receiver_name = ? AND message_status = 'unread'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
echo json_encode(['unread_count' => $data['unread_count']]);
$stmt->close();
$conn->close();
?> 