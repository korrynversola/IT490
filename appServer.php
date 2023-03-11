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
	switch ($request['type']) {
 		case "keywordrecipe":
			return searchKeywordRecipe($request['keyword']);
		case "groceryrecipe":
			return searchGroceryRecipe($request['sessionID']);
		case "expirerecipe":
			return searchExpireRecipe($request['sessionID']);
		case "grocerylist":
			return genGroceryList($request['sessionID'], $request['search']);
		case "saveRecipe":
			return saveRateRecipe($request['sessionID'], $request['recipe']);
		case "rateRecipe":
			return saveRateRecipe($request['sessionID'], $request['recipe']);
		case "viewRated":
			return viewRatedRecipes($request['sessionID']);
		case "userRecipe":
			return storeUserRecipe($request['sessionID'], $request['userRecipe']);
		case "addGroceries":
			return addGroceries($request['sessionID'], $request['groceries']);
		case "userGroceries":
			return getUserGroceries($request['sessionID']);
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
$appServer = new rabbitMQServer("serversMQ.ini","appServer");

$appServer->process_requests('requestProcessor');
exit();
?>
