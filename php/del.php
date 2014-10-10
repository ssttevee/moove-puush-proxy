<?php
include "config.php";
require "../lib/Moove.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

if(!empty($_POST)) {
    if (!isset($_POST["k"])) die("no key");
    if (!isset($_POST["i"])) die("no image");

    $name = base_convert($_POST["i"], 10, 36);

    try {
        /** Connect to SQLite database **/
        $moove = new Moove(PDO_DATA_SOURCE_NAME);

        $owner = $moove->getUserIdByApiKey($_POST["k"]);

        /** Prepare and execute SQL statement **/
        $sth = $moove->pdo->prepare("select key from files where owner = ? and id = ? limit 1");
        $sth->execute(array($owner, $_POST["i"]));

        /** Get all results **/
        $res = $sth->fetchAll();

        if(count($res) < 1) {
            die("not found");
        }

        $name = base_convert($_POST["i"], 10, 36);

        /** Delete the file and the thumbnail **/
        if(file_exists(DIR_STORAGE . $name . ".blob")) {
            exec("rm -f " . DIR_STORAGE . $name . ".blob");
        }
        if(file_exists(DIR_THUMB_CACHE . $name . ".100x100.blob")) {
            exec("rm -f " . DIR_THUMB_CACHE . $name . ".100x100.blob");
        }

        /** Delete row from db **/
        $sth = $moove->pdo->prepare("delete from files where owner = ? and id = ? and key = ?");
        $sth->execute(array($owner, $_POST["i"], $res[0]["key"]));

        echo $moove->getUserHistory($owner, 10);

        /** close the database connection **/
        $file_db = null;
    } catch(Exception $e) {
        echo $e->getMessage();
    }

} else {
    http_response_code(410) ;
}