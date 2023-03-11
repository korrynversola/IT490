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
		default:
			return logerror($request['type'], $request['error']);
	}
	return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

function logerror($type, $error) {
        $file_data = $error;
        $file_data .= file_get_contents($type.'.txt');
        file_put_contents($type.'.txt', $file_data);
        return json_encode(["message" => "Error received"]);
}

$authServer = new rabbitMQServer("serversMQ.ini","authServer");
$authServer->process_requests('requestProcessor');
exit();
?>
