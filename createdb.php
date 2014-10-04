<?php
include "config.php";

// Set default timezone
date_default_timezone_set('America/Vancouver');

try {
	/**************************************
	* Create databases and                *
	* open connections                    *
	**************************************/

	// Create (connect to) SQLite database in file
	$file_db = new PDO(PDO_DATABASE_CONNECT);


	/**************************************
	* Create tables                       *
	**************************************/

	$file_db->exec("DROP IF EXISTS files");

	// Create table files
	$file_db->exec("CREATE TABLE IF NOT EXISTS files (
	            id INTEGER PRIMARY KEY,
	            name TEXT,
	            ext TEXT,
	            size INTEGER,
	            owner INTEGER,
	            time INTEGER)");

	// Create table users
	$file_db->exec("CREATE TABLE IF NOT EXISTS users (
	              id INTEGER PRIMARY KEY,
	              email TEXT,
	              password TEXT,
	              apikey TEXT)");

	// Create table invites
	$file_db->exec("CREATE TABLE IF NOT EXISTS invites (
	              id INTEGER PRIMARY KEY,
	              code TEXT,
	              time INTEGER)");


	/**************************************
	* Close db connections                *
	**************************************/

	// Close file db connection
	$file_db = null;
	// Close memory db connection
	$memory_db = null;
}
catch(PDOException $e) {
	// Print PDOException message
	echo $e->getMessage();
}