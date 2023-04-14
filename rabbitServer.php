#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

require_once('dbConnection.php'); //connects to the database
require_once('rabbitFunctions.php'); //functions for server
function requestProcessor($request) {
        echo "received request".PHP_EOL;
        var_dump($request);
        //$errorClient = new rabbitMQClient("serversMQ.ini", "ErrorLogging");
       /* try {
        if(!isset($request['type'])) {
                return "ERROR: unsupported message type";
        }

        $validSession = json_decode(validateSession($request['sessionID']), true)['valid'];
        if($validSession == 0) {
                return json_encode(['valid' => 0]);
        }
	*/
       switch ($request['type']) {
       		case "addBundle":
			return addBundle($request['bundleName'], $request['bundlePath']);
	}
}
$appServer = new rabbitMQServer("rabbitServer.ini","rabbitServer");

$appServer->process_requests('requestProcessor');
exit();
?>

