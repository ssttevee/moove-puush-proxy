<?php
include_once __DIR__ . "/php/config.php";
require_once __DIR__ . "/lib/Moove.php";

function tablesExists($moove) {
	$sth = $moove->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table'");
	$sth->execute();

	$tables = $sth->fetchAll(PDO::FETCH_COLUMN);

	if (in_array("users", $tables) &&
		in_array("files", $tables) &&
		in_array("invites", $tables)) {
		return true;
	} else {
		return false;
	}
}

function usersExist($moove) {
	$sth = $moove->pdo->prepare("SELECT COUNT(id) FROM users");
	$sth->execute();
	return $sth->fetchColumn() > 0;
}

function formatBoolean($bool) {
	if($bool)
		return "<i style=\"color: green; font-weight: bold;\">true</i>";
	else
		return "<i style=\"color: red; font-weight: bold;\">false</i>";
}


$step = $_GET["step"];
if(!isset($step)) $step = 0;
?>
<pre>
<?php
switch($step) {
	default:
	case 0:
		// step 0: intro, check requirements, permissions, db existence
		echo "<h1>Check Requirements and Permissions</h1>";
		echo "Checking for pdo_sqlite... " . formatBoolean(extension_loaded("pdo_sqlite")) . "\n";
		echo "Checking for mcrypt... " . formatBoolean(extension_loaded("mcrypt")) . "\n";
		echo "Checking if DIR_STORAGE exists... " . formatBoolean(file_exists(DIR_STORAGE) && is_dir(DIR_STORAGE)) . "\n";
		echo "Checking if DIR_STORAGE is writable... " . formatBoolean(file_exists(DIR_STORAGE) && is_dir(DIR_STORAGE)) . "\n";
		echo "Checking if DIR_THUMB_CACHE exists... " . formatBoolean(file_exists(DIR_THUMB_CACHE) && is_dir(DIR_THUMB_CACHE)) . "\n";
		echo "Checking if DIR_THUMB_CACHE  is writable... " . formatBoolean(file_exists(DIR_THUMB_CACHE) && is_dir(DIR_THUMB_CACHE)) . "\n";
		echo "Checking if PDO_DATA_SOURCE_NAME exists... " . formatBoolean(file_exists(PDO_DATA_SOURCE_NAME)) . "\n";
		echo "Checking if PDO_DATA_SOURCE_NAME is writable... " . formatBoolean(file_exists(PDO_DATA_SOURCE_NAME)) . "\n";
		echo "\n<a href=\"setup.php?step=1\">Begin Setup</a>";
		break;
	case 1:
		// step 1: create db tables
		echo "<h1>Create Database Tables</h1>";

		try {
			$moove = new Moove(PDO_DATA_SOURCE_NAME);
		} catch(PDOException $e) {
			echo "Error: " . $e->getMessage();
			exit;
		}

		if(!tablesExists($moove)) {
			// Set default timezone
			date_default_timezone_set('America/Vancouver');

			try {


				/**************************************
				* Create tables                       *
				**************************************/

				// Create table users
				echo "Creating 'users' table... ";
				$moove->pdo->exec("CREATE TABLE IF NOT EXISTS users (
				              id INTEGER PRIMARY KEY,
				              email TEXT,
				              password TEXT,
				              apikey TEXT,
				              bytesused UNSIGNED BIG INT)");
				echo "done\n";

				// Create table files
				echo "Creating 'files' table... ";
				$moove->pdo->exec("CREATE TABLE IF NOT EXISTS files (
				            id INTEGER PRIMARY KEY,
				            name TEXT,
				            ext TEXT,
				            size INTEGER,
				            key INTEGER,
				            owner INTEGER REFERENCES users(id),
				            time INTEGER,
				            hits INTEGER)");
				echo "done\n";

				// Create table invites
				echo "Creating 'invites' table... ";
				$moove->pdo->exec("CREATE TABLE IF NOT EXISTS invites (
				              id INTEGER PRIMARY KEY,
				              code TEXT,
				              time INTEGER)");
				echo "done\n";
			} catch(PDOException $e) {
				// Print PDOException message
				echo "Error:" . $e->getMessage();
				exit;
			}
		} else {
			echo "The tables are already created. You can move on to the next step\n";
		}

		echo "\n<a href=\"setup.php?step=2\">Step 2: Create admin account</a>";
		break;
	case 2:
		// step 2: create administrator
		echo "<h1>Create Admin Account</h1>";

		try {
			$moove = new Moove(PDO_DATA_SOURCE_NAME);
		} catch(PDOException $e) {
			echo "Error: " . $e->getMessage();
			exit;
		}

		if(!empty($_POST)) {
			$missing = false;
			if(!isset($_POST["e"])) {
				$missing = true;
				echo "Error: missing email\n";
			}
			if(!isset($_POST["p"])) {
				$missing = true;
				echo "Error: missing password\n";
			}

			if(!$missing) {

				try {
					/** Generate new API Key **/
					$apiKey = strtoupper(hash("md5", time() . mt_rand() . $_SERVER['REMOTE_ADDR']));

					/** INSERT new user **/
			        $sth = $moove->pdo->prepare("INSERT INTO users(email, password, apikey) VALUES (?, ?, ?)");
			        $sth->execute(array($_POST["e"], hash("sha256", $_POST["p"]), $apiKey));
				} catch(PDOException $e) {
					echo "Error: " . $e->getMessage();
					exit;
				}

		    }
		}

		if(!usersExist($moove)) {
			echo "<form action=\"setup.php?step=2\" method=\"post\">";
			echo "Email: <input type=\"email\" name=\"e\">\n";
			echo "Password: <input type=\"password\" name=\"p\">\n";
			echo "<input type=\"submit\">";
			echo "</form>";
		} else {
			echo "Your admin account has been created!\n";
			echo "\n<a href=\"setup.php?step=3\">Finish</a>";
		}
		break;
	case 3:
		// step 3: done
		echo "<h1>You're done!</h1>";
		echo "The next step will delete this file for you\n";
		echo "\n<a href=\"setup.php?step=4\">Delete setup script</a>";
		break;
	case 4:
		// step 4: delete setup.php
		unlink(__DIR__ . "/setup.php");
		echo "<meta http-equiv=\"refresh\" content=\"0;URL='./'\" />";
		break;
}
?>

</pre>