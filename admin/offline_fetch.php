<tr>
    <th>Name</th>
    <th>Email</th>
    <!--<th>User ID</th>
    <th>Password</th>-->
    <th>Account ID</th>
    <th>Status</th>
    <th>Action</th>
</tr>
<?php
    include "../connection/conn.php";
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d h:i A');
    $id = $_GET['id'];
    $sql = "SELECT * FROM `user`";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'offline') {
            echo "<tr>
            <td title='{$row['fname']} {$row['mname']} {$row['lname']}'>{$row['fname']} {$row['mname']} {$row['lname']}</td>
            <td title='{$row['email']}'>{$row['email']}</td>
            <!--<td title='{$row['user']}'>{$row['user']}</td>
            <td title='{$row['password']}'>{$row['password']}</td>-->
            <td title='{$row['account']}'>{$row['account']}</td>
            <td title='{$row['status']}'>
                <span class='status {$row['status']}'>{$row['status']}</span>
            </td>
            ";
            echo "
             <td>
                    <div class='action-buttons'>
                        <a href='edit_jeepney.php?id={$row['id']}' class='btn-icon edit' title='Edit'>
                            <i class='bx bxs-edit'></i>
                        </a>
                        <a href='delete_jeepney.php?id={$row['id']}' class='btn-icon delete' title='Delete'>
                            <i class='bx bxs-trash'></i>
                        </a>
                    </div>
                </td>
        </tr>";
        }
    }
?>
<style>

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-icon.edit {
    background: #4CAF50;
}

.btn-icon.delete {
    background: #f44336;
}

.btn-icon i {
    color: #fff;
    font-size: 18px;
}

.btn-icon:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

@media (max-width: 768px) {
    .info-row {
        font-size: 12px;
    }
    
    .btn-icon {
        width: 28px;
        height: 28px;
    }
    
    .btn-icon i {
        font-size: 16px;
    }
}

.status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
}

.status.online {
    background: rgba(76, 175, 80, 0.2);  /* Green with transparency */
    color: #4CAF50;
}

.status.offline {
    background: rgba(244, 67, 54, 0.2);  /* Red with transparency */
    color: #f44336;
}

/* Optional hover effect */
.status:hover {
    opacity: 0.9;
}
</style>