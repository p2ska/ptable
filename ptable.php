<?php

if (isset($_GET["export"])) {
    // puhasta input

    $uid = preg_replace("/\.+/", ".", preg_replace("/[^\p{L}\p{N}\s\.@_-]/u", "", trim($_GET["export"])));

    if (substr_count($uid, "-"))
        list($title) = explode("-", $uid);
    else
        $title = "ptable";

    $csv_file = "c:/xampp/htdocs/ptable/_temp/ptable-export-". $uid. ".csv";
    $user_file = $title. ".csv";

    if (file_exists($csv_file)) {
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename='". $user_file. "'");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo file_get_contents($csv_file);

        unlink($csv_file);

        return;
    }
    else {
        header("Location: /ptable");
    }
}
elseif (!isset($_POST["ptable"]))
    return false;

session_name("ptable");
session_start();

require_once("c:/xampp/security/ptable/_connector.php");
require_once("classes/_translations.php");
require_once("classes/_ptable_ext.php");

$example_data = [
    "32ddwe;andres;midagi;2015-01-09",
    "c2dewd;peeter on huvitav tegelane;eeeh;2015-01-11",
    "jkh43c;kalev;!hoo;2015-01-10",
    "yr3fvv;zyrinx;kool;2015-01-04",
    "32ddwe;weber;midagi;2014-03-01",
    "c2dewd;erki on huvitav tegelane;eeeh;2013-04-21",
    "jkh43c;urmo;!hoo;2012-01-14",
    "yr3fvv;oliver;kool;2014-05-04",
    "32ddwe;kia;midagi;2013-09-19",
    "c2dewd;pomps on huvitav tegelane;eeeh;2013-11-11",
    "jkh43c;koll;!hoo;2015-11-12",
    "yr3fvv;jaanus;kool;2015-09-03",
    "32ddwe;triinu;midagi;2015-07-19",
    "c2dewd;jarmo on huvitav tegelane;eeeh;2015-05-16",
    "jkh43c;koer;!hoo;2015-06-15",
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

if (isset($_POST["export"]))
    $_POST["ptable"]["export"] = $_POST["export"];

$pt = new PTABLE_EXT($_POST["ptable"], $data);

echo $pt->content;

// we are done here

function get_dump($var) {
	ob_start();

	print_r($var);

	return ob_get_clean();
}

function p_log($file, $str, $append = false) {
	$paths = [ "/var/tmp/", "c:/XAMPP/htdocs/ptable/_temp/" ];

    foreach ($paths as $path)
        if (file_exists($path))
            break;

    if (!is_string($str))
		$str = get_dump($str);

	$fp = fopen($path. $file, $append ? "a" : "w");
	fputs($fp, $str. "\n");
	fclose($fp);
}

function compare_strings($str1, $str2, $encoding = false) {
    if (!$encoding)
        $encoding = mb_internal_encoding();

    if (!is_array($str2))
        $str2 = [ $str2 ];

    foreach ($str2 as $str)
        if (strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str, $encoding)) == 0)
            return true;

    return false;
}

?>
