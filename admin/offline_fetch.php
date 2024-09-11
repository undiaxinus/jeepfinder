<tr>
<th>Name</th>
<th>Email</th>
<th>User ID</th>
<th>Password</th>
<th>Account ID</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php
//fetch_data.php
include "../connection/conn.php";
date_default_timezone_set('Asia/Manila');
$date = date('Y-m-d h:i A');
$id = $_GET['id'];
$sql = "SELECT * FROM `user`";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    if ($row['status'] == 'offline') {
        // code...
    
    echo "<tr>
            <td title='{$row['fname']} {$row['mname']} {$row['lname']}'>{$row['fname']} {$row['mname']} {$row['lname']}</td>
            <td title='{$row['email']}'>{$row['email']}</td>
            <td title='{$row['user']}'>{$row['user']}</td>
            <td title='{$row['password']}'>{$row['password']}</td>
            <td title='{$row['account']}'>{$row['account']}</td>
            <td title='{$row['status']}'>{$row['status']}</td>
            ";
    
    echo "<td>
                <a href='edit.php?id={$id}&ids={$row['id']}' class='btn edit'>Edit</a>
                <a href='delete_user.php?id={$id}&ids={$row['id']}' class='btn delete'>Delete</a>
            </td>
        </tr>";
}
}

?>
