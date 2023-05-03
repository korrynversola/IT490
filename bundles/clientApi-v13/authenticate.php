<?php

require_once('../rabbit/path.inc');
require_once('../rabbit/get_host_info.inc');
require_once('../rabbit/rabbitMQLib.inc');

$client = new rabbitMQClient("apiRabbitMQ.ini","authServer");
$errLogClient = new rabbitMQClient("../rabbit/errorServerMQ.ini","errorServer");

$req_body = file_get_contents('php://input');
$data = json_decode($req_body, true);

// Session validation request
if (isset($data["type"]) && $data["type"] == 'validateSession'){
    try { // Send validation request
        $response = $client->send_request($data); 
        echo $response;
    } catch (Exception $e) {
        // On error, send error message to error log
        $errLogClient->send_request(['type' => 'Frontenderrors', 'error' => $e->getMessage()]);
        // Set request type to 500
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        //Send error message to client
        die(json_encode(array('message' => $e->getMessage())));
    } //Check for authentication request 
} else if(isset($data["type"]) && isset($data["email"]) && isset($data["password"]) &&
($data["type"] != '' && $data["email"] != '' && $data['password'] != '')){
    //If data missing on registration send error message to client
    if($data["type"] == "register" && (!isset($data["fname"]) || !isset($data["lname"]) || !isset($data["password2"]))){
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'Missing Fields')));
    } else {
        //Check if password matches confirmation password
        if($data["type"] == "register" && $data["password"] != $data["password2"]){
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array('message' => 'Passwords Must Match')));
        }

        try {
            //Send authentication request to DB
            $response = $client->send_request($data);
            $res_obj = json_decode($response, true);
            //$errLogClient->send_request(['type' => 'Frontenderrors', 'error' => 'Mock Error: frontend is good']);
            if(isset($res_obj['sessionID'])){
                //Send response back to client
                echo json_encode($res_obj);
            } else {
                // If DB didn't send data back, send 400 error to client
                header('HTTP/1.1 400 Bad Request');
                header('Content-Type: application/json; charset=UTF-8');
                die($response);
            }
        } catch (Exception $e) {
            // On error, send error message to error log
            $errLogClient->send_request(['type' => 'Frontenderrors', 'error' => $e->getMessage()]);
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array('message' => $e->getMessage())));
        }
        
        
    }
} else {
    // Send error message for missing credentials
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('message' => 'Missing Credentials')));
}

?>
