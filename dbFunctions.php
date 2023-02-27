<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once('login.php.inc');
require_once('dbConnection.php'); //establishes connection to database
require_once('testRabbitMQClient.php'); //sends requests to dmz

function createSession($email) {
	$mydb = dbConnection();
	$sessionID = SHA1($email.time());
	$sessionQuery = "INSERT INTO Sessions VALUES ('$email', '$sessionID', NOW())";
	$result = $mydb->query($sessionQuery);
	return $sessionID;
}

function validateSession($sessionID) {
	$mydb = dbConnection();
	$query = "SELECT UNIX_TIMESTAMP(creationTime) as epoch FROM Sessions WHERE sessionID = '$sessionID'";
	$result = $mydb->query($query);
	$row = $result->fetch_assoc();
	$epoch = intval($row['epoch']);
	$timeElapsed = time()-$epoch;
	if ($timeElapsed > 1200) {
		//$deleteSession = "DELETE * FROM Sessions";
		$deleteSession = "DELETE FROM Sessions WHERE sessionID = '$sessionID'";
		$result = $mydb->query($deleteSession);
		return json_encode(['valid' => 0]);
	}
	else {		
		$updateSession = "UPDATE Sessions SET creationTime = NOW() WHERE sessionID = '$sessionID'";
		$result = $mydb->query($updateSession);
		return json_encode(['valid' => 1]);
	
	}
}
function doLogin($email, $password) {
	$mydb = dbConnection();
	$hash = SHA1($password);
	$query = "SELECT * FROM Users WHERE email = '$email' AND password = '$hash'";
	$result = $mydb->query($query);
	$user = $result->fetch_assoc();
	$first = $user['first'];
	$last = $user['last'];
	if ($result->num_rows == 1) {
		return json_encode(['fname' => $first, 'lname' => $last, 'email' => $email, 'sessionID' => createSession($email)]);
	}
	else {
		return json_encode(['message' => 'wrong email/password']);
	}
}

function doRegister($first, $last, $email, $password) {
	$mydb = dbConnection();
	$hash = SHA1($password);
	$query = "SELECT * FROM Users WHERE email = '$email'";
	$result = $mydb->query($query);
	if ($result->num_rows == 1 ) {
		return json_encode(['message' => 'That email address is in use']);
	}
	else {
		$registerQuery = "INSERT INTO Users VALUES ('$first', '$last','$email', '$hash')";
		$result =$mydb->query($registerQuery);
		return json_encode(['fname' => $first, 'lname' => $last, 'email' => $email, 'sessionID' => createSession($email)]);
	}
}


function selectEmailFromSession ($sessionID) {
 	$mydb = dbConnection();
	$query = "SELECT email FROM Sessions WHERE sessionID = '$sessionID'";
	$result = $mydb->query($query);
	$session = $result->fetch_assoc();
	if ($result->num_rows == 1) {
		return $session['email'];
	}
}

/*
function addGroceries($sessionID, $item, $expirationDate, $buyDate) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "INSERT INTO Groceries (email, item, expirationDate, buyDate) VALUES ('$email', '$groceryItem', '$expirationDate', '$buyDate')";
	$result = $mydb->query($query);
	return json_encode(['message' => '']);
}

function addToGroceryList($sessionID, $item) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "INSERT INTO Groceries (email, item) VALUES ('$email, '$item')";
	$result = $mydb->($query);
	return json_encode(['message' => 'Added groceries to list']);
}


function getUserGroceries($sessionID, $bought) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$bought = "SELECT * FROM Groceries WHERE email = '$email' AND buyDate IS NOT NULL";
	$result = $mydb->query($bought);
	if ($result->num_rows == 0) {
		echo "no groceries";
		return json_encode(['message' => 'no groceries']);
	}
	else {
		$groceries = $result->fetch_all();
		return json_encode([$groceries]);
	}
}

function getExpiringGroceries($sessionID, $item, $expirationDate) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT * FROM Groceries WHERE email = '$email' AND expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
	$result = $mydb->query($query);
	if ($result->num_rows == 0) {
		echo "no groceries are expiring this week";
		return json_encode(['message' => 'no groceries are expiring this week');
	}
	else {
		$expiring = $result->fetch_all();
		return json_encode([$expiring]);
	}
}
function rateRecipe($sessionID, $recipeID, $rating) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "INSERT INTO Rated_Recipes (email, recipeID, rating) VALUES ('$email','$recipeID', '$rating')";
	$result = $mydb->query($query);
}

function viewRatedRecipes($sessionID, $recipeID) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT name, rating FROM Rated_Recipes rr JOIN Recipes r ON r.recipeID = ri.recipeID JOIN Users u ON u.email == rr.email; 
	$result = $mydb->query($query);
	if ($result->num_rows == 0) {
		echo "you have not rated any recipes";
		return json_encode ["message" => "you have not rated any recipes"]);
	}
	else {
		$userRatedRecipes = $result->fetch_all();
		return $userRatedRecipes;
	}
}

function storeRecipe($recipe) {
	$mydb = dbConnection();
	$query = "INSERT INTO Recipes (name, description, instructions, maxReadyTime) VALUES ('$name', '$description', '$instructions', '$maxReadyTime)";
	$result = $mydb->query($query);
	return json_encode(['recipe' => $recipe]);
}
 */
function searchKeywordRecipe($keyword) {
	$mydb = dbConnection();
	$query = "SELECT * FROM Recipes WHERE name LIKE '%$keyword%'";
	$result = $mydb->query($query);
	if ($result->num_rows == 0) {
		$response = dbClient(["type" => "keywordrecipe", "keywordrecipe" => $keyword]);
		echo $response;
		return $response;
	}
}

/*
function dmzKeywordRecipe($name, $description, $instructions, $maxReadyTime) {
	$request = array();
	$request['type'] = 'keywordrecipe';
	$request['titleMatch'] = $name;
	$request['addRecipeInformation'] = $description;
	$request['instructionsRequired'] = $instructions;
	$request['maxReadyTime'] = $maxReadyTime;
	$response = dbClient($request);
	echo var_dump($response);
	return $response;
}
*/
function searchGroceryRecipe($sessionID, $item) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT * FROM Groceries WHERE email = '$email' AND buyDate IS NOT NULL";
	$result = $mydb->query($query);
	if ($result->num_rows == 0) {
		echo "no items in fridge";
	}
	else {
		$groceryRecipeQuery = "SELECT * FROM Recipes r JOIN Recipe_Ingredients ri ON r.recipeID = ri.recipeID JOIN Ingredients i ON i.ingredientID = ri.ingredientID JOIN Groceries g ON g.item = i.ingredient AND i.buyDate IS NOT NULL";
		$groceryRecipeResult = $mydb->query($groceryRecipeQuery);
		if ($groceryRecipeResult->num_rows == 0) {
			$response = dbClient(["type" => "groceryrecipe", "groceryrecipe" => $item]);
			echo $response;
			return $response;
		}
	}
}

function searchExpireRecipe ($sessionID, $item) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$query = "SELECT * FROM Groceries WHERE email = '$email' AND expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
	$result = $mydb->query($query);
	if ($result->num_rows == 0) {
		echo "no grocery items will expire this week";
		return json_encode(["message" => "no grocery items will expire this week"]);
	}
	else {
		$expireRecipeQuery = "SELECT* FROM Recipes r JOIN Recipe_Ingredients ri ON r.recipeID = ri.recipeID JOIN Ingredients i ON i.ingredientID = ri.ingredientID JOIN Groceries g ON g.item = i.ingredient AND g.expirationDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
		if ($expireRecipeQuery->num_rows == 0) {
			$response = dbClient(["type" => "expirerecipe", "expirerecipe" => $item]);
			echo $response;
			return $response;
		}
	}
}

function genGroceryList ($sessionID, $search) {
	$mydb = dbConnection();
	$email = selectEmailFromSession($sessionID);
	$response = dbClient(["type" => "grocerylist", "grocerylist" => $search]);
	echo $response;
	return $response;
}


?>

