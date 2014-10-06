<?php
include "config.php";
require "../lib/cipher.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

if(!empty($_POST)) {
	if(!isset($_POST["k"])) die("no key");
	if(!isset($_POST["z"])) die("no poop");

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

		/** Generate encryption key */
		$index = str_split("0123456789abcdefghijklmnopqrstuvwxyz");
		$textKey = "";
		for ($i = 0; $i < 6; $i++)
			$textKey .= $index[mt_rand(0, count($index) - 1)];

		/** Get file extension **/
		$ext = explode(".", $_FILES["f"]["name"]);
		$ext = $ext[count($ext) - 1];

		/** Insert file data into the db **/
		$sth = $file_db->prepare("insert into files(name,ext,size,key,owner,time) values(?,?,?,?,?,?)");
		$sth->execute(array($_FILES["f"]["name"], $ext, filesize($_FILES["f"]["tmp_name"]), $textKey, $owner, time()));

		/** Get inserted id **/
		$id = $file_db->lastInsertId();

		/** Transform id into file name */
		$fname = base_convert($id, 10, 36);

		/** Move upload to storage **/
		move_uploaded_file($_FILES["f"]["tmp_name"], DIR_STORAGE . $fname . ".blob");

		/** Encrypt Uploaded File **/
		$cipher = new Cipher($textKey);
		$data = file_get_contents(DIR_STORAGE . $fname . ".blob");
		$data = $cipher->encrypt($data);
		file_put_contents(DIR_STORAGE . $fname . ".blob", $data);

		/** Return string for puush client **/
		echo "0," . ROOT_URL . $textKey . "/" . $fname . "." . $ext . "," . $id . ",0";

		/** close the database connection **/
		$file_db = null;
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
} else {
	?>
	<form method="post" enctype="multipart/form-data">
		API Key: <input type="text" name="k" /><br/>
		File: <input type="file" name="f" /><br/>
		<input type="hidden" name="z" value="poop" />
		<input type="submit" />
	</form>
<?php }