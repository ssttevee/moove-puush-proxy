<?php
include "config.php";

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
		$file_db = new PDO(PDO_DATABASE_CONNECT);

		/** Check for existing user **/
		$sth = $file_db->prepare("select password,id from users where email == ?");
		$sth->execute(array($_POST["e"]));
		$res = $sth->fetchAll();

		if($_POST["i"] == "generate") {
			if(hash("sha256", $_POST["p"]) == $res[0]["password"] && $res[0]["id"] == 1) {
				/** Generate encryption key */
				$index = str_split("0123456789abcdefghijklmnopqrstuvwxyz");
				$inviteKey = "";
				for ($i = 0; $i < 16; $i++)
					$inviteKey .= $index[mt_rand(0, count($index) - 1)];
				$res = $file_db->exec("insert into invites(code, time) VALUES (" . $file_db->quote($inviteKey) . ", " . $file_db->quote(0) . ")");
				die($inviteKey);
			} else {
				die("unauthorized");
			}
		} else if (count($res) > 0) {
			$file_db = null;
			die("user exists");
		}

		/** Check for valid invite **/
		$res = $file_db->query("select id from invites where code = " . $file_db->quote($_POST["i"]) . " and time = " . $file_db->quote(0))->fetchAll();

		if (count($res) < 1) {
			$file_db = null;
			die("invalid invitation");
		}

		/** Use invitation code **/
		$res = $file_db->exec("update invites set time = " . $file_db->quote(time()) . " where code = " . $file_db->quote($_POST["i"]));

		/** Generate new API Key **/
		$apiKey = strtoupper(hash("md5", time() . mt_rand() . $_SERVER['REMOTE_ADDR']));

		/** INSERT new user **/
		$res = $file_db->exec("insert into users(email, password, apikey) VALUES (" . $file_db->quote($_POST["e"]) . ", " . $file_db->quote(hash("sha256", $_POST["p"])) . ", " . $file_db->quote($apiKey) . ")");

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