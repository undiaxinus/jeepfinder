<?php
// Include necessary files and initialize session if needed
include_once("../connection/connect.php");
$conn = connection();

// Fetch messages from the database
$sql = "SELECT * FROM message ORDER BY timestamp ASC";
$result = $conn->query($sql);
$messages = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

function formatTimestamp($timestamp) {
    $dateTime = new DateTime($timestamp);
    $now = new DateTime();
    
    $diff = $now->diff($dateTime);
    $diffInDays = $diff->days;

    if ($diffInDays == 0) {
        return $dateTime->format('g:i A') . ', Today';
    } elseif ($diffInDays == 1) {
        return '1 day ago';
    } else {
        return $diffInDays . ' days ago';
    }
}

// Output HTML for messages
foreach ($messages as $msg) {
    if ($msg['sender_name'] == $_SESSION['id']) {
        echo '<div class="d-flex justify-content-end mb-4">
                <div class="msg_cotainer_send">
                    ' . $msg['message'] . '
                    <span class="msg_time_send">' . formatTimestamp($msg['timestamp']) . '</span>
                </div>
                <div class="img_cont_msg">
                    <img src="../img/gif.gif" class="rounded-circle user_img_msg">
                </div>
              </div>';
    } else {
        echo '<div class="d-flex justify-content-start mb-4">
                <div class="img_cont_msg">
                    <img src="../img/sbmo.png" class="rounded-circle user_img_msg">
                </div>
                <div class="msg_cotainer">
                    ' . $msg['message'] . '
                    <span class="msg_time">' . formatTimestamp($msg['timestamp']) . '</span>
                </div>
              </div>';
    }
}
?>
