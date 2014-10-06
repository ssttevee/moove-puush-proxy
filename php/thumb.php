<?php
include "config.php";
require "../lib/cipher.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

ini_set('memory_limit', '-1');

if(!empty($_POST)) {
    if (!isset($_POST["k"])) die("no key");
    if (!isset($_POST["i"])) die("no image");

    $name = base_convert($_POST["i"], 10, 36);

    try {
        /** Connect to SQLite database **/
        $file_db = new PDO(PDO_DATABASE_CONNECT);

        /** Prepare and execute SQL statement **/
        $sth = $file_db->prepare("SELECT id FROM users where apikey = ?");
        $sth->execute(array($_POST["k"]));

        /** Get first result **/
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        /** Store the user's id **/
        if(isset($res["id"])) $owner = $res["id"];
        else die("bad result");

        /** Prepare and execute SQL statement **/
        $sth = $file_db->prepare("SELECT key FROM files where owner = ? and id = ? limit 1");
        $sth->execute(array($owner, $_POST["i"]));

        /** Get all results **/
        $res = $sth->fetchAll();

        if(count($res) < 1) {
            die("bad image");
        }

        header("Content-type: image/png");

        $cipher = new Cipher($res[0]["key"]);
        if(file_exists(DIR_THUMB_CACHE . $name)) {
            echo $cipher->decrypt(file_get_contents(DIR_THUMB_CACHE . $name . ".100x100.blob"));
        } else {
            $image = createThumb($cipher->decrypt(file_get_contents(DIR_STORAGE . $name . ".blob")), 100, 100);

            $data = $cipher->encrypt($image);
            file_put_contents(DIR_THUMB_CACHE . $name . ".100x100.blob", $data);

            echo $image;
        }

        /** close the database connection **/
        $file_db = null;
    } catch(PDOException $e) {
        echo $e->getMessage();
    }

} elseif(!empty($_GET)) {
    if (!isset($_GET["k"])) die("no key");
    if (!isset($_GET["f"])) die("no file");

    header("Content-type: image/png");

    $name = $_GET["f"];
    $cipher = new Cipher($_GET["k"]);

    if(file_exists(DIR_THUMB_CACHE . $name)) {
        echo $cipher->decrypt(file_get_contents(DIR_THUMB_CACHE . $name . ".100x100.blob"));
    } else {
        $image = createThumb($cipher->decrypt(file_get_contents(DIR_STORAGE . $name . ".blob")), 100, 100);

        $data = $cipher->encrypt($image);
        file_put_contents(DIR_THUMB_CACHE . $name . ".100x100.blob", $data);

        echo $image;
    }
} else {
    http_response_code(410);
}

function createThumb($image_data,$new_w,$new_h){
    $src_img = imagecreatefromstring($image_data);

    $src_w = imageSX($src_img);
    $src_h = imageSY($src_img);

    $source_aspect_ratio = $src_w / $src_h;
    $desired_aspect_ratio = $new_w / $new_h;

    if ($source_aspect_ratio > $desired_aspect_ratio) {
        $temp_height = $new_h;
        $temp_width = ( int ) ($new_h * $source_aspect_ratio);
    } else {
        $temp_width = $new_w;
        $temp_height = ( int ) ($new_w / $source_aspect_ratio);
    }

    $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
    imagecopyresampled($temp_gdim, $src_img, 0, 0, 0, 0, $temp_width, $temp_height, $src_w, $src_h);

    $x0 = ($temp_width - $new_w) / 2;
    $y0 = ($temp_height - $new_h) / 2;
    $dst_img = imagecreatetruecolor($new_w, $new_h);
    imagecopy($dst_img, $temp_gdim, 0, 0, $x0, $y0, $new_w, $new_h);

    ob_start();
    imagepng($dst_img);
    $ret_img = ob_get_contents();
    ob_end_clean();

    imagedestroy($dst_img);
    imagedestroy($src_img);

    return $ret_img;
}
