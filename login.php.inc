<?php

class loginDB
{
private $logindb;

public function __construct()
{
	$this->logindb = new mysqli("localhost","kav32","spring23","IT490");

	if ($this->logindb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

public function validateLogin($email,$password)
{
	$email = $this->logindb->real_escape_string($email);
	$password = $this->logindb->real_escape_string($password);
	$statement = "select * from Users where email = '$email'";
	$response = $this->logindb->query($statement);

	while ($row = $response->fetch_assoc())
	{
		echo "checking password for $email".PHP_EOL;
		if ($row["password"] == $pw)
		{
			echo "passwords match for $email".PHP_EOL;
			return 1;// password match
		}
		echo "passwords did not match for $email".PHP_EOL;
	}
	return 0;//no users matched username
}
}
?>
