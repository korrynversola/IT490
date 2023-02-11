<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once('dbConnection.php');

function requestProcessor($request) {
	echo "received request".PHP_EOL;
	var_dump($request);
	if(!isset($request['type']) {
		return "ERROR: unsupported message type";
	}
	switch ($request['type']) {
	case "login":
		return doLogin($request['email'], $request['password']);
	case "validate_session":
		return doValidate($request['sessionID']);
	}
	return array "returnCode" -> '0', 'message'=>"Server received request and processed");
		
$server = new RabbitMQServer('../rabbitmqphp_example/rabbitMQ_db/ini', 'dbServer');

echo "Database Server BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "Database Server END".PHP_EOL;
exit(0);
?>
