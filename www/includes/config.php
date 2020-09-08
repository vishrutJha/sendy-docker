<?php 
	//----------------------------------------------------------------------------------//	
	//                               COMPULSORY SETTINGS
	//----------------------------------------------------------------------------------//
	
	/*  Set the URL to your Sendy installation (without the trailing slash) */
	define('APP_PATH', 'http://mailman.vangal.com');
	
	/*  MySQL database connection credentials (please place values between the apostrophes) */
	$dbHost = 'database'; //MySQL Hostname from docker
	// $dbUser = $_ENV["MYSQL_USER"]; //MySQL Username
	// $dbPass = $_ENV["MYSQL_PASSWORD"]; //MySQL Password
	// $dbName = $_ENV["MYSQL_DATABASE"]; //MySQL Database Name
	$dbUser = 'docker'; //MySQL Username
	$dbPass = 'docker'; //MySQL Password
	$dbName = 'docker'; //MySQL Database Name
	
	
	//----------------------------------------------------------------------------------//	
	//								  OPTIONAL SETTINGS
	//----------------------------------------------------------------------------------//	
	
	/* 
		Change the database character set to something that supports the language you'll
		be using. Example, set this to utf16 if you use Chinese or Vietnamese characters
	*/
	$charset = 'utf8mb4';
	
	/*  Set this if you use a non standard MySQL port.  */
	$dbPort = 3306;	
	
	/*  Domain of cookie (99.99% chance you don't need to edit this at all)  */
	define('COOKIE_DOMAIN', '');
	
	//----------------------------------------------------------------------------------//
?>
