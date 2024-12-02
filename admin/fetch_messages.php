<?php
    include_once("../connection/connect.php");
    $conn = connection();

    $receiver_name = $_GET['receiver_name'];
    $sender_id = $_GET['sender_id'];

    // Sanitize inputs
    $receiver_name = mysqli_real_escape_string($conn, $receiver_name);
    $sender_id = mysqli_real_escape_string($conn, $sender_id);

    $sql = "SELECT * FROM message 
            WHERE (sender_name = '$sender_id' AND receiver_name = '$receiver_name')
            OR (sender_name = '$receiver_name' AND receiver_name = '$sender_id')
            ORDER BY timestamp";

    $result = $conn->query($sql);
    $output = '';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['sender_name'] == $sender_id) {
                $output .= '<div class="d-flex justify-content-end mb-4">
                              <div class="msg_cotainer_send" data-message-id="'.htmlspecialchars($row['id']).'">
                                '.htmlspecialchars($row['message']).'
                                <span class="msg_time_send">'.date('h:i A', strtotime($row['timestamp'])).'</span>
                              </div>
                           </div>';
            } else {
                $output .= '<div class="d-flex justify-content-start mb-4">
                              <div class="msg_cotainer" data-message-id="'.htmlspecialchars($row['id']).'">
                                '.htmlspecialchars($row['message']).'
                                <span class="msg_time">'.date('h:i A', strtotime($row['timestamp'])).'</span>
                              </div>
                           </div>';
            }
        }
    } else {
        $output .= '<div class="text-center text-white">No messages yet</div>';
    }

    echo $output;
?>
