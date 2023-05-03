<?php

require_once('../rabbit/path.inc');
require_once('../rabbit/get_host_info.inc');
require_once('../rabbit/rabbitMQLib.inc');

$client = new rabbitMQClient("apiRabbitMQ.ini","appServer");
$errLogClient = new rabbitMQClient("../rabbit/errorServerMQ.ini","errorServer");

// Obtain request data from client and decode as json
$req_body = file_get_contents('php://input');
$data = json_decode($req_body, true);

//Check data type is set
if (isset($data["type"])){
    try { // send request data & echo response back
        $response = $client->send_request($data);
        echo $response;
    } catch (Exception $e) {
        // On error, log error message into message log
        $errLogClient->send_request(['type' => 'Frontenderrors', 'error' => $e->getMessage()]);
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => $e->getMessage())));
    }
} else {
    // If request type missing send back 400 error
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('message' => 'Missing Request Type')));
}

?>
