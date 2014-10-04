<?php
include "config.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

if(!empty($_POST)) {
	if(!isset($_POST["k"])) die("no key");

	try {
		/** Connect to SQLite database **/
		$file_db = new PDO(PDO_DATABASE_CONNECT);

		/** Prepare and execute SQL statement **/
		$sth = $file_db->prepare("SELECT id FROM users where apikey == ?");
		$sth->execute(array($_POST["k"]));

		/** Get first result **/
		$res = $sth->fetch(PDO::FETCH_ASSOC);

		/** Store the user's id **/
		if(isset($res["id"])) $owner = $res["id"];
		else die("bad result");

		/** Prepare and execute SQL statement **/
		$sth = $file_db->prepare("SELECT id,name,ext,time FROM files where owner == ? order by time desc limit 10");
		$sth->execute(array($owner));

		/** Print blank entry */
		echo "0\n";

		/** Print the rest of the entries **/
		foreach($sth->fetchAll() as $row) {
			echo $row["id"] . ",";
			echo date("Y-m-d H:i:s", $row["time"]) . ",";
			echo "http://psh.ssttevee.com/" . base_convert($row["id"],10,36) . "." . $row["ext"] . ",";
			echo $row["name"] . ",0,0\n";
		}

		/** close the database connection **/
		$file_db = null;
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
} else {
	die("no data");
}