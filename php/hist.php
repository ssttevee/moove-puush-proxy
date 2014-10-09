<?php
include "config.php";
require "../lib/Moove.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

if(!empty($_POST)) {
	if(!isset($_POST["k"])) die("no key");

	try {
		$moove = new Moove(PDO_DATA_SOURCE_NAME);

        /** Get user id from posted api key **/
        $owner = $moove->getUserIdByApiKey($_POST["k"]);

        /** Print user history **/
        echo $moove->getUserHistory($owner, 10);
	} catch(Exception $e) {
		echo $e->getMessage();
	}
} else {
	die("no data");
}