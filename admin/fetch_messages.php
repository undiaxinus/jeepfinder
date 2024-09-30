<?php
    include_once("../connection/connect.php");
    $conn = connection();

    $sender_id = isset($_GET['sender_id']) ? $_GET['sender_id'] : null;
    $receiver_name = isset($_GET['receiver_name']) ? $_GET['receiver_name'] : null;
    $sql = "SELECT * FROM message WHERE (sender_name = ? AND receiver_name = ?) OR (sender_name = ? AND receiver_name = ?) ORDER BY timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $sender_id, $receiver_name, $receiver_name, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
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
    foreach ($messages as $msg) {
        if ($msg['sender_name'] == $sender_id) {
            echo '<div class="d-flex justify-content-end mb-4">
                <div class="msg_cotainer_send">
                    ' . htmlspecialchars($msg['message']) . '
                    <span class="msg_time_send">' . formatTimestamp($msg['timestamp']) . '</span>
                </div>
                <div class="img_cont_msg">
                    <img src="../img/pic1.jpg" class="rounded-circle user_img_msg">
                </div>
            </div>';
        } else {
            echo '<div class="d-flex justify-content-start mb-4">
                <div class="img_cont_msg">
                    <img src="../img/sbmo.png" class="rounded-circle user_img_msg">
                </div>
                <div class="msg_cotainer">
                    ' . htmlspecialchars($msg['message']) . '
                    <span class="msg_time">' . formatTimestamp($msg['timestamp']) . '</span>
                </div>
            </div>';
        }
    }
?>
