
<?php
$ids = $_GET['id'] ?? null;
include "../connection/conn.php";
date_default_timezone_set('Asia/Manila');
$date = date('Y-m-d h:i A');

$sql = "SELECT * FROM `locate`";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Initialize variables for status
        $statusClass = "not-activated";
        $statusText = "Device not activated";

        if (!empty($row['resdate'])) {
            // Calculate time difference only if `resdate` is not empty
            $currentTime = new DateTime();
            $responseTime = new DateTime($row['resdate']);
            $diffInMinutes = $currentTime->diff($responseTime)->i;

            // Determine status based on time difference
            if ($diffInMinutes > 5) {
                $statusClass = "connection-lost";
                $statusText = "Connection lost";
            } else {
                $statusClass = "active";
                $statusText = "Active";
            }
        }

        echo "<tr>
            <td>{$row['ID']}</td>
            <td class='driver-info'>
                <div class='info-row'>Driver's name: {$row['drivername']}</div>
                <div class='info-row'>Address: {$row['address']}</div>
                <div class='info-row'>Company: {$row['company_name']}</div>
                <div class='info-row'>Phone: {$row['cnumber']}</div>
                <div class='info-row'>Plate: {$row['platenumber']}</div>
                <div class='info-row'>Route: {$row['route']}</div>
            </td>
            <td class='jeepney-info'>
                <div class='info-row'>Passengers: {$row['passenger']}/{$row['capacity']}</div>
                <div class='info-row'>Location: {$row['latitude']},{$row['longitude']}</div>
                <div class='info-row'>Response: {$row['resdate']}</div>
                <div class='info-row'>Current: {$date}</div>
            </td>
            <td class='status-cell'>
                <span class='status {$statusClass}'>
                    {$statusText}
                </span>
            </td>
            <td>
                <div class='action-buttons'>
                    <a href='edit_jeepney.php?ids={$row['ID']}&id={$ids}' class='btn-icon edit' title='Edit'>
                        <i class='bx bxs-edit'></i>
                    </a>
                    <a href='delete_jeepney.php?ids={$row['ID']}&id={$ids}' class='btn-icon delete' title='Delete' onclick='return confirm(\"Are you sure you want to delete this account?\");'>
                        <i class='bx bxs-trash'></i>
                    </a>
                </div>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No data found</td></tr>";
}

$conn->close();
?>


<style>
.info-row {
    margin-bottom: 8px;
    color: #fff;
    font-size: 14px;
}

.driver-info, .jeepney-info {
    padding: 10px;
}

.status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status.active {
    background: rgba(76, 175, 80, 0.2);
    color: #4CAF50;
}

.status.connection-lost {
    background: rgba(244, 67, 54, 0.2);
    color: #f44336;
}
.status.not-activated {
    background: rgba(245, 167, 66, 0.2);
    color: #f5a742;
}

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
</style>