<?php
include "config.php";
require "../lib/Moove.php";

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
		$moove = new Moove(PDO_DATA_SOURCE_NAME);

		/** Check for existing user **/
		$sth = $moove->pdo->prepare("select password,id from users where email == ?");
		$sth->execute(array($_POST["e"]));
		$res = $sth->fetchAll();

		if($_POST["i"] == "generate") {
			if(hash("sha256", $_POST["p"]) == $res[0]["password"] && $res[0]["id"] == 1) {
				/** Generate encryption key */
				$index = str_split("0123456789abcdefghijklmnopqrstuvwxyz");
				$inviteKey = "";
				for ($i = 0; $i < 16; $i++)
					$inviteKey .= $index[mt_rand(0, count($index) - 1)];

                $sth = $moove->pdo->prepare("insert into invites(code, time) values (?, ?)");
                $sth->execute(array($inviteKey, 0));

				throw new Exception($inviteKey);
			} else {
				throw new Exception("Unauthorized");
			}
		} else if (count($res) > 0) {
            throw new Exception("User Already Exists");
		}

		/** Check for valid invite **/
		$sth = $moove->pdo->prepare("select id from invites where code = ? and time = ?");
        $sth->execute(array($_POST["i"], 0));
        $res = $sth->fetchAll();

		if (count($res) < 1) {
            throw new Exception("Invalid Invitation Code");
		}

		/** Use invitation code **/
        $sth = $moove->pdo->prepare("update invites set time = ? where code = ? and time = ?");
        $sth->execute(array(time(), $_POST["i"], 0));

		/** Generate new API Key **/
		$apiKey = strtoupper(hash("md5", time() . mt_rand() . $_SERVER['REMOTE_ADDR']));

		/** INSERT new user **/
        $sth = $moove->pdo->prepare("insert into users(email, password, apikey) values (?, ?, ?)");
        $sth->execute(array($_POST["e"], hash("sha256", $_POST["p"]), $apiKey));

        echo "Registration Complete";
	} catch (Exception $e) {
		echo $e->getMessage();
	}
} else {
?>
<form method="post">
	Email: <input type="text" name="e" /><br/>
	Password: <input type="password" name="p" /><br/>
	Invite Code: <input type="text" name="i" onload="this.value = window.location.hash.substr(1);" /><br/>
	<input type="submit" />
</form>
<?php }