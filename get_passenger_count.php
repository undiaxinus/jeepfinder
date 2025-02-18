<?php
include_once 'connection/conn.php';

if (isset($_GET['device_id'])) {
    $device_id = $_GET['device_id'];
    
    $sql = "SELECT passenger FROM locate WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $device_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'count' => $row['passenger']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Device not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No device ID provided']);
} 