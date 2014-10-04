<?php
include "config.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

if(!empty($_POST)) {
	if(!isset($_POST["e"])) die("no email");
	if(!isset($_POST["z"])) die("no poop");

	try {
		/** Connect to SQLite database **/
		$file_db = new PDO(PDO_DATABASE_CONNECT);

		/** Generate SQL statement **/
		$sql = "select apikey from users where email == ? and ";

		if(isset($_POST["p"])) $sql .= "password == ?";
		else if(isset($_POST["k"])) $sql .= "apikey == ?";
		else die("no auth");

		/** Prepare and execute SQL statement **/
		$sth = $file_db->prepare($sql);
		$sth->execute(array($_POST["e"], (isset($_POST["p"]) ? hash("sha256", $_POST["p"]) : $_POST["k"])));

		/** Get first result **/
		$res = $sth->fetch();

		/** Return the API key **/
		if(isset($res["apikey"])) echo "0," . $res["apikey"] . ",,0";
		else echo "bad result";

		/** close the database connection **/
		$file_db = null;
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
} else {
	die("no data");
}