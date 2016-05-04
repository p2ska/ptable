<?php

// ptable ja tema klasside/template'de path

define("P_PATH",            "c:/xampp/htdocs/ptable");                  // ptable asukoht
define("P_TMP",             "c:/xampp/tmp");                            // tmp kataloog (või nt: sys_get_temp_dir())

define("P_BASE_PATH",       "/ptable");                                 // baasurl
define("P_EXTENSIONS",      P_PATH. "/extensions");                     // laienduste asukoht
define("P_TABLES",		    P_PATH. "/tables");                         // tabelikirjelduste asukoht
define("P_TRANSLATIONS",    P_PATH. "/lang/ptable.lang");               // tõlkefaili asukoht

// sessiooni alustamine

session_name("ptable");
session_start();

// lae baasi parameetrid ja vajalikud klassid

require_once("c:/xampp/security/ptable/_connector.php");                // andmebaasi parameetrid
require_once("classes/_db.php");                                        // andmebaasi klass
require_once("classes/_translations.php");                              // tõlgete klass
require_once("classes/_ptable.php");                                    // ptable põhiklass

// DEMO: välise tabeli massiiv

require_once("demo/external_data.php");

// välise tabeli muutuja

if (isset($_POST["subdata"]))
    $_POST["ptable"]["subdata"] = $_POST["subdata"];

// csv download

if (isset($_GET["download"]))
    $_POST["ptable"]["download"] = $_GET["download"];

// eksportimiseks vajalik muutuja (sisaldab teavet, millist osa tabelist on vaja eksportida)

if (isset($_POST["export"]))
    $_POST["ptable"]["export"] = $_POST["export"];

// initsialiseeri ptable

$pt = new PTABLE(@$_POST["ptable"], $data);

// väljasta tabel

echo $pt->content;

// --abifunktsioonid--------------------------------------------------------------------

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

	$paths = [ P_TMP, "/var/tmp" ];

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

	if (!$fp = fopen($path. "/". $file, $append ? "a" : "w"))
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
