<?php
// marker.php
include("../connection/conn.php");

$sql = "SELECT * FROM locate";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = array(
            'id' => $row['ID'],
            'plateNumber' => $row['platenumber'],
            'route' => $row['route'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
            'speed' => $row['speed'],
            'passenger' => $row['passenger'],
            'rotation' => $row['rotation'],
            'jeep' => $row['jeepicon']
        );
    }

    echo json_encode($data);
} else {
    echo json_encode(array('error' => 'No data found'));
}

$conn->close();
?>
