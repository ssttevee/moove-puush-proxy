<?php
include "config.php";
require "../lib/cipher.php";
require "../lib/Moove.php";

if(isset($_GET["f"]) && isset($_GET["k"]) && isset($_GET["x"])) {
    if(file_exists(DIR_STORAGE . $_GET["f"] . ".blob")) {
        try {
            $file_id = base_convert($_GET["f"], 36, 10);

            $moove = new Moove(PDO_DATA_SOURCE_NAME);
            $moove->countHit($file_id);
            $file = $moove->getFileById($file_id, $_GET["k"]);

            if(!$file) {
                header('HTTP/1.0 401 Unauthorized');
                die();
            }

            header('Content-type: ' . get_mime_type($_GET["x"]));
            header('Content-Length: ' . $file["size"]);
            header('Content-Disposition: filename="' . $file["name"] . '"');

            $cipher = new Cipher($_GET["k"]);
            echo $cipher->decrypt(file_get_contents(DIR_STORAGE . $_GET["f"] . ".blob"));
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 File not found', true, 404);
    }
}

function get_mime_type($extension) {
	$types = array(
		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'php' => 'text/html',
		'css' => 'text/css',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'swf' => 'application/x-shockwave-flash',
		'flv' => 'video/x-flv',

		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',

		// archives
		'zip' => 'application/zip',
		'rar' => 'application/x-rar-compressed',
		'exe' => 'application/x-msdownload',
		'msi' => 'application/x-msdownload',
		'cab' => 'application/vnd.ms-cab-compressed',

		// audio/video
		'mp3' => 'audio/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',

		// adobe
		'pdf' => 'application/pdf',
		'psd' => 'image/vnd.adobe.photoshop',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',

		// ms office
		'doc' => 'application/msword',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',

		// open office
		'odt' => 'application/vnd.oasis.opendocument.text',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	);
	return $types[$extension];
}