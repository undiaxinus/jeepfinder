<?php
//logout1.php
session_start();

// Check if there is an existing session
if(isset($_SESSION['Role'])) {
    // Unset the session variable
    unset($_SESSION['Role']);
    // Destroy the session
    session_destroy();
}

// Check if there is a last visited URL saved in the session
if(isset($_SESSION['last_visited_url'])) {
    // Redirect the user to the last visited URL
    header('Location: ' . $_SESSION['last_visited_url']);
} else {
    // If there is no last visited URL, redirect the user to index.html with a success message
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