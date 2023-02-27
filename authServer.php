#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

require_once('dbConnection.php'); //connects to the database
require_once('dbFunctions.php'); //functions for the database

function requestProcessor($request) {
	echo "received request".PHP_EOL;
	var_dump($request);
	if(!isset($request['type'])) {
		return "ERROR: unsupported message type";
       	}
	switch ($request['type'])
  	{
        	case "login":
			return doLogin($request['email'],$request['password']);
        	case "validateSession":
                	return validateSession($request['sessionID']);
        	case "register":
                	return doRegister($request['fname'],$request['lname'],$request['email'],$request['password']);
	}
	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$authServer = new rabbitMQServer("testRabbitMQ.ini","testServer");
$authServer->process_requests('requestProcessor');
exit();
?>
