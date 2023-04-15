<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once('login.php.inc');
require_once('dbConnection.php'); //establishes connection to database

//update bundle version
function currVersion($bundleName) {
	$mydb = dbconnection();
	$versionQuery = "SELECT MAX(currVersion) as latest FROM Deployment WHERE bundleName = '$bundleName'";
	$versionResult = $mydb->query($versionQuery);
	$bundleVersion = $versionResult->fetch_object();
	$latest = $bundleVersion->latest;
	echo $latest;
	$updateVersion = $bundleVersion++;
	return $updateVersion;
}

//inserts bundle info to the database
function addBundle($bundleName, $bundlePath) {
	$mydb = dbConnection();	
	$currVersion = currVersion($bundleName);	
	$query = "INSERT INTO Deployment VALUES ('$bundleName', '$currVersion', '0','$bundlePath')";
	$result = $mydb->query($query);
	return json_encode(["current version" => $currVersion]);
}


?>
