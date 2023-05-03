<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

require_once('login.php.inc');
require_once('dbConnection.php'); //establishes connection to database

/*$IPaddresses = ["QA" => [
	"frontend" => "gc348@192.168.195.180",
	"dmz" => "sjm@192.168.195.96",
	"backend" =>"kversola@192.168.195.167"
	], "Production" => [
	"frontend" => "gc348@172.26.235.34",
	"dmz" => "sjm@172.26.64.43",
	"backend" => "kversola@172.26.247.140"
	]
];
 */

//update bundle version
function currVersion($bundleName) {
	$mydb = dbconnection();
	$versionQuery = "SELECT ifnull((SELECT MAX(currVersion) AS latest FROM Bundles WHERE bundleName = '$bundleName'), 0) AS latestVersion";
	$versionResult = $mydb->query($versionQuery);
	$bundleVersion = $versionResult->fetch_assoc();
	$latestVersion = $bundleVersion["latestVersion"];
	$updateVersion = $latestVersion + 1;
	return $updateVersion;
}

//inserts bundle info to the database
function addBundle($bundleName, $bundlePath, $bundleMachine) {
	$mydb = dbConnection();	
	$currVersion = currVersion($bundleName);	
	$query = "INSERT INTO Bundles VALUES ('$bundleName', '$currVersion', '0','$bundlePath', '$bundleMachine')";
	$result = $mydb->query($query);
	return json_encode(["current_version" => $currVersion]);
}

//function for rollout (bundle name and cluster)
function rollout($bundleName, $cluster, $rollback) {
	$mydb = dbConnection();
	$IPaddresses = ["QA" => [
        "frontend" => "gc348@192.168.195.126",
        "dmz" => "sjm@192.168.195.96",
        "backend" =>"it490@192.168.195.167"
        ], "Production" => [
        "frontend" => "gc348@172.26.235.34",
        "dmz" => "sjm@172.26.64.43",
        "backend" => "it490@172.26.247.140"
        ]
	];
	$query = "";
	if (!$rollback) {
		$query = "SELECT * FROM Bundles WHERE currVersion in (SELECT MAX(currVersion) FROM Bundles WHERE bundleName = '$bundleName') AND bundleName = '$bundleName'";
	}
	else {
		$query = "SELECT * FROM Bundles WHERE currVersion in (SELECT MAX(currVersion) FROM Bundles WHERE bundleName = '$bundleName' AND statusFlag = 1) AND bundleName = '$bundleName'";
	}
	$result = $mydb->query($query);
	$bundleData = $result->fetch_assoc();
	$latestVersion = $bundleData["currVersion"];
	$bundleMachine = $bundleData["bundleMachine"];
	$bundlePath = $bundleData["bundlePath"];
	//echo $latestVersion;
	//echo $bundlePath;
	//echo $cluster;
	//echo $bundleMachine;
	$machineAddress = $IPaddresses[$cluster][$bundleMachine];
//	echo $machineAddress;
/*	if ($rollout = false && $machineAddress != $IPaddresses["frontend"]["Production"]) {
		$query = "SELECT * FROM Bundles WHERE currVersion in (SELECT MAX(currVersion) FROM Bundles WHERE bundleName = '$bundleName' AND statusFlag = 0)";
		$result = $mydb->query($query);
		return json_encode(["message"=>"status is false"]);
	}*/
	$exec = shell_exec("ssh $machineAddress 'rm -rf $bundlePath/*'");
	echo $exec;
	$execute = shell_exec("rsync -av /home/kversola/git/rabbitmqphp_example/bundles/$bundleName-v$latestVersion/ $machineAddress:$bundlePath");
}

//function for status confirmation
function confirmStatus($bundleName, $version, $status) {
	$mydb = dbConnection();
	$statusQuery = "UPDATE Bundles SET statusFlag = '$status' WHERE bundleName = '$bundleName' AND currVersion = '$version'";
	$updateStatus = $mydb->query($statusQuery);
	return json_encode(["message" => "updated status"]);
}
?>
