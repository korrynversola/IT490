#!/usr/bin/php
#<?php

require_once('../rabbitmqphp_example/database/path.inc');
require_once('../rabbitmqphp_example/database/get_host_info.inc');
require_once('../rabbitmqphp_exampl/database/rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini", "dbServer");

$request = array();
$request['type'] = "login";
$request['email'] = $argv[1];
$request['password'] = $argv[2];
$reponse = $client->send_request($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
