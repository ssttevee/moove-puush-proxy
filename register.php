<?php

// Set default timezone
date_default_timezone_set('America/Vancouver');

if(!empty($_POST)) {
	if (!isset($_POST["e"]))
		die("no email");
	if (!isset($_POST["p"]))
		die("no password");
	if (!isset($_POST["i"]))
		die("no invitation");
	try {
		/** Connect to SQLite database **/
		$file_db = new PDO('sqlite:puush.sqlite3');

		/** Check for existing user **/
		$sth = $file_db->prepare("select count(*) from users where email == ?");
		$sth->execute(array($_POST["e"]));
		$count = $sth->fetch(PDO::FETCH_NUM);

		if ($count[0] > 0) {
			$file_db = null;
			die("user exists");
		}

//		/** Check for valid invite **/
//		$count = $file_db->query("select count(id) from invite where code = " . $file_db->quote($_POST["i"]) . "")->fetchColumn();
//
//		if ($count < 1) {
//			$file_db = null;
//			die("invalid invitation");
//		}

		/** Generate new API Key **/
		$apiKey = strtoupper(hash("md5", time() . mt_rand() . $_SERVER['REMOTE_ADDR']));

		/** INSERT new user **/
		$count = $file_db->exec("insert into users(email, password, apikey) VALUES (" . $file_db->quote($_POST["e"]) . ", " . $file_db->quote(hash("sha256", $_POST["p"])) . ", " . $file_db->quote($apiKey) . ")");

		/** close the database connection **/
		$file_db = null;

		echo "0," . $apiKey . ",0,0";
	} catch (PDOException $e) {
		echo $e->getMessage();
	}
} else {
?>
<form method="post">
	Email: <input type="text" name="e" /><br/>
	Password: <input type="password" name="p" /><br/>
	Invite Code: <input type="text" name="i" /><br/>
	<input type="submit" />
</form>
<?php }