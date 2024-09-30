<?php
session_start();
if(isset($_SESSION['Role'])) {
    unset($_SESSION['Role']);
    session_destroy();
}
if(isset($_SESSION['last_visited_url'])) {
    header('Location: ' . $_SESSION['last_visited_url']);
} else {
    header('Location: ../index.html?success=Account Logged out');
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>SABAT MO</title>
</head>
<body>
	<?php 
	session_destroy();
	?>
</body>
</html>