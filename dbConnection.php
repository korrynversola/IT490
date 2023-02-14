#!/usr/bin/php
<?php

function dbConnection() {
	$mydb = new mysqli('localhost', 'kav32', 'spring23', 'IT490');

	if ($mydb->errno != 0) {
	echo "Failed to connect to database: ". #mydb->error . PHP_EOL;
	exit(0);
	}

echo "Successfully connected to database.".PHP_EOL;
return $mydb;

?>
