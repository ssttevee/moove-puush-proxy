<?php
include "config.php";
require "../lib/Moove.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

if(!empty($_POST)) {
	if(!isset($_POST["e"])) die("no email");
	if(!isset($_POST["z"])) die("no poop");

	try {
		/** Connect to SQLite database **/
		$moove = new Moove(PDO_DATA_SOURCE_NAME);

		/** Authenticate **/
		if(isset($_POST["p"])) echo $moove->authenticateByPassword($_POST["e"], $_POST["p"]);
		else if(isset($_POST["k"])) echo $moove->authenticateByApiKey($_POST["e"], $_POST["k"]);
		else die("no auth");
	} catch(Exception $e) {
		echo $e->getMessage();
	}
} else {
	die("no data");
}