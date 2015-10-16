<?php

session_name("ptable");
session_start();

if (!isset($_GET["ptable"]))
	return false;

require_once("classes/_andres.php");

$example_data = array(
	"32ddwe;andres;midagi;1",
	"c2dewd;peeter;eeeh;2",
	"jkh43c;kalev;ohoo;3",
	"yr3fvv;zyrinx;kool;4");

foreach ($example_data as $ex) {
	list($a, $b, $c, $d) = explode(";", $ex);

	$el = new stdClass();

	$el->id = $a;
	$el->nimi = $b;
	$el->lisatud = $c;
	$el->olek = $d;

	$data[] = $el;
}

$pt = new ANDRESE_PTABLE($_GET["ptable"], $data);

// we are done here

function dump($this, $die = false) {
	echo "<pre>";
	print_r($this);
	echo "</pre>";
	
	if ($die)
		die();
}

?>
