<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once('login.php.inc'); //establishes connection to database

function doLogin($email, $password) {
	$mydb = new mysqli('localhost', 'kav32', 'spring23', 'IT490');
	$hash = SHA1($password);
	$query = "SELECT * FROM Users WHERE email = '$email' AND password = '$hash'";
	$result = $mydb->query($query);
	echo "somethinG";
	if ($result->num_rows == 1) {
		return json_encode(['email' => $email, 'logged' => 1]);
	}
	else {
		return json_encode(['message' => 'wrong email/password', 'logged' => 0]);
	}
}

/*function doRegister($email, $password) {
	$mydb = new mysqli('localhost', 'kav32', 'spring23', 'IT490');
	$hash = SHA1($password);
	$query = 'SELECT * FROM Users WHERE email = '$email'';
	$result = $mydb->query($query);
	if ($result->num_rows == 1 ) {
		echo "That email address is in use';
		return false;
*/	
?>


