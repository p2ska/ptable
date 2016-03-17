<?php

session_name("ptable");
session_start();

if (!isset($_POST["ptable"]))
    return false;

require_once("c:/xampp/security/ptable/_connector.php");
require_once("classes/_translations.php");
require_once("classes/_ptable_ext.php");

$example_data = [
    "32ddwe;andres;midagi;2015-01-09",
    "c2dewd;peeter on huvitav tegelane;eeeh;2015-01-11",
    "jkh43c;kalev;ohoo;2015-01-10",
    "yr3fvv;zyrinx;kool;2015-01-04",
    "32ddwe;weber;midagi;2014-03-01",
    "c2dewd;erki on huvitav tegelane;eeeh;2013-04-21",
    "jkh43c;urmo;ohoo;2012-01-14",
    "yr3fvv;oliver;kool;2014-05-04",
    "32ddwe;kia;midagi;2013-09-19",
    "c2dewd;pomps on huvitav tegelane;eeeh;2013-11-11",
    "jkh43c;koll;ohoo;2015-11-12",
    "yr3fvv;jaanus;kool;2015-09-03",
    "32ddwe;triinu;midagi;2015-07-19",
    "c2dewd;jarmo on huvitav tegelane;eeeh;2015-05-16",
    "jkh43c;koer;ohoo;2015-06-15",
    "yr3fvv;loom;kool;2015-05-24"
];

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

if (isset($_POST["subdata"]))
    $_POST["ptable"]["subdata"] = $_POST["subdata"];

$pt = new PTABLE_EXT($_POST["ptable"], $data);

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
