<?php

define("PTABLE_BASE_PATH",      "/ptable");                                 // baasurl
define("PTABLE_PATH",           getcwd());                                  // ptable asukoht
define("PTABLE_TMP",            PTABLE_PATH. "/_temp");                     // tmp kataloog: sys_get_temp_dir()
define("PTABLE_DB_CONNECTOR",   "c:/xampp/security/ptable/_connector.php"); // db connectori asukoht

// kas on tegemist tabeli eksportimisega, mitte kuvamisega

if (isset($_GET["export"])) {
    // puhasta input

    $uid = preg_replace("/\.+/", ".", preg_replace("/[^\p{L}\p{N}\s\.@_-]/u", "", trim($_GET["export"])));

    // hangi target'i id uid'ist (failinime jaoks)

    if (substr_count($uid, "-")) {
        $pos = strrpos($uid, "-");
        $title = substr($uid, 0, $pos);
        $uid = substr($uid, $pos + 1);
    }
    else
        $title = "export";

    $dl_filename = $title. " [". date("d-m-Y H-i"). "].csv";
    $csv_file = PTABLE_TMP. "/ptable-export-". $uid. ".csv";

    // kui fail eksisteerib, siis on hea

    if (file_exists($csv_file)) {
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename='". $dl_filename. "'");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo file_get_contents($csv_file);

        unlink($csv_file);

        return;
    }
    else { // kui ei leitud, tee rewrite lihtsalt vastava aplikatsiooni juurikasse..
        header("Location: ". PTABLE_BASE_PATH);

        return false;
    }
}
elseif (!isset($_POST["ptable"])) { // kui mingil põhjusel on peamuutuja tühi.. suuna kasutaja ümber
    header("Location: ". PTABLE_BASE_PATH);

    return false;
}

// sessiooni alustamine

session_name("ptable");
session_start();

// lae baasi parameetrid ja vajalikud klassid

require_once(PTABLE_DB_CONNECTOR);
require_once("classes/_translations.php");
require_once("classes/_ptable_ext.php");

// välise tabeli massiiv (demo)

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

// moodusta massiivist objektimassiiv (demo)

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

// välise tabeli muutuja

if (isset($_POST["subdata"]))
    $_POST["ptable"]["subdata"] = $_POST["subdata"];

// eksportimiseks vajalik muutuja

if (isset($_POST["export"]))
    $_POST["ptable"]["export"] = $_POST["export"];

// initsialiseeri ptable

$pt = new PTABLE_EXT($_POST["ptable"], $data);

// väljasta tabel

echo $pt->content;

/* nüüd ongi kõik */

// ---------------------------------------------------------------------------

// salvesta dump stringi

function get_dump($var) {
	ob_start();

	print_r($var);

	return ob_get_clean();
}

// logi dump faili

function p_log($file, $str, $append = false) {
    $path_found = false;

    // võimalikud logifaili asukohad - esimesse kataloog, mis eksisteerib, sinna ka salvestatakse

	$paths = [ "/var/tmp/", "c:/XAMPP/htdocs/ptable/_temp/", PTABLE_TMP ];

    foreach ($paths as $path)
        if (file_exists($path)) {
            $path_found = true;

            break;
        }

    // kui ei leitud kataloogi

    if (!$path_found)
        return false;

    // kui ei ole puhas string, siis hangi antud objekti või massiivi dump

    if (!is_string($str))
		$str = get_dump($str);

    // kas õnnestub faili avamine kirjutamiseks

	if (!$fp = fopen($path. $file, $append ? "a" : "w"))
        return false;

    // kirjuta dump faili

	fputs($fp, $str. "\n");
	fclose($fp);

    return true;
}

// võrdle stringe (kui str2 anda massiiv, siis tagastab true, kui kasvõi üks massiivis olevatest vastab otsitule)

function compare_strings($str1, $str2, $encoding = false) {
    // kui pole eraldi sunnitud mingit kodeeringut, siis võta default

    if (!$encoding)
        $encoding = mb_internal_encoding();

    // kui ei ole massiiv, siis tee selleks

    if (!is_array($str2))
        $str2 = [ $str2 ];

    // otsi elementide hulgast, kas leidub vastavusi

    foreach ($str2 as $str)
        if (strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str, $encoding)) == 0)
            return true;

    return false;
}

?>
