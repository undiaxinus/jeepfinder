<?php
include "../connection/conn.php";

if (isset($_GET['ids'])) {
    $ids = $_GET['ids'];
	$id = $_GET['id'];
    // Delete record
    $sql = "DELETE FROM user WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ids);

    if ($stmt->execute()) {
        header("Location: users.php?id=$id");
        exit;
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
} else {
    echo "Invalid request!";
    exit;
}
$conn->close();
?>