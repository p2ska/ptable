<?php

session_name("ptable");
session_start();

if (!isset($_GET["ptable"]))
	return false;

require_once("c:/xampp/security/ptable/_connector.php");
require_once("classes/_translations.php");
require_once("classes/_ptable_ext.php");

$example_data = array(
	"32ddwe;andres;midagi;2015-01-09",
	"c2dewd;peeter on huvitav tegelane;eeeh;2015-01-11",
	"jkh43c;kalev;ohoo;2015-01-10",
	"yr3fvv;zyrinx;kool;2015-01-04");

foreach ($example_data as $ex) {
	list($a, $b, $c, $d) = explode(";", $ex);

	$el = new stdClass();

	$el->id = $a;
	$el->nimi = $b;
	$el->lisatud = $c;
	$el->olek = $d;
    $el->deleted = 0;

    if ($d == 2)
        $el->deleted = 1;

	$data[] = $el;
}

if (isset($_GET["subdata"]))
    $_GET["ptable"]["subdata"] = $_GET["subdata"];

$pt = new PTABLE_EXT($_GET["ptable"], $data);

echo $pt->content;

// we are done here

function dump($this, $die = false) {
	echo "<pre>";
	print_r($this);
	echo "</pre>";
	
	if ($die)
		die();
}

?>
