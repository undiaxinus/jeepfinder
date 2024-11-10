<tr>
    <!--<th>ID</th>-->
    <th>Drivers Information</th>
    <th>Jeepney data information</th>
    <th>Status</th>
    <th>Action</th>
</tr>
<?php
    include "../connection/conn.php";
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d h:i A');
    $id = $_GET['id'];
    $sql = "SELECT * FROM `locate`";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $hashedPassword = hash('sha256', $row['ID']);
        echo "<tr>
            <!--<td>{$hashedPassword}</td>-->
            <td><strong>Drivers name: </strong>{$row['drivername']}<br>
            <strong>Address: </strong>{$row['address']}<br>
            <strong>Company name: </strong>{$row['company_name']}<br>
            <strong>Phone number: </strong>{$row['cnumber']}<br>
            <strong>Plate number: </strong>{$row['platenumber']}<br>
            <strong>Route: </strong>{$row['route']}</td>
            ";
            $resdateTime = new DateTime($row['resdate']);
            $currentDateTime = new DateTime($date);
            $interval = $currentDateTime->diff($resdateTime);
            $diffInMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
            if ($diffInMinutes > 5) {
                echo "
                <td><strong>Passengers count: </strong>0<br>
                <strong>Location: </strong>{$row['latitude']},{$row['longitude']}<br>
                <strong>Response Date: </strong>{$row['resdate']}<br>
                <strong>Current Date: </strong>{$date}</td>
                <td>Connection lost</td>";
            } else {
                echo "
                <td><strong>Passengers count: </strong>{$row['passenger']}<br>
                <strong>Location: </strong>{$row['latitude']},{$row['longitude']}<br>
                <strong>Response Date: </strong>{$row['resdate']}<br>
                <strong>Current Date: </strong>{$date}</td>
                <td>Active</td>";
            } 
            echo "<td>
                <a href='edit_jeepney.php?id={$id}&ids={$row['ID']}' class='btn edit'>Edit</a>
                <a href='delete_jeepney.php?id={$id}&ids={$row['ID']}' class='btn delete'>Delete</a>
            </td>
        </tr>";
    }
?>