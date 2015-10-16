<?php

session_name("ptable");
session_start();

if (!isset($_GET["ptable"]))
	return false;

require_once("classes/_db.php");
require_once("classes/_translations.php");
require_once("classes/_andres.php");

$pt = new ANDRESE_PTABLE($_GET["ptable"]);

// we are done here

function dump($this, $die = false) {
	echo "<pre>";
	print_r($this);
	echo "</pre>";
	
	if ($die)
		die();
}

?>