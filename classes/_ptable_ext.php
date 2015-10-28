<?php

define("P_TABLES",	"c:/xampp/htdocs/ptable/ptables");

require_once("_db.php");
require_once("_ptable.php");

// põhiklassi extension, võimaldamaks päringust saadud väärtusi edasi töödelda

class PTABLE_EXT extends PTABLE {
    // hangi kasutajainfo

    function ext_person($string) {
        return "nimi: ". $string;
    }

	// koverteeri kuupäevad eesti regioonile vastavaks

	function ext_convert_date($date) {
        $timestamp = strtotime($date);

        // kui ei ole korrektne sisend, siis ära töötle

        if ($timestamp < 1)
            return $date;

		if (strlen($date) <= 10) // kui on lühike formaat, ilma kellaajata
			return date("d.m.Y", $timestamp);
		else
			return date("d.m.Y H:i:s", $timestamp);
	}

    // võta sekundid maha

	function ext_convert_time($time) {
		return substr($time, 0, 5);
	}

	// muuda emailiaadressid ja veebilingid linkideks

	function ext_autolink($string) {
		$string = preg_replace("/(([\w\.]+))(@)([\w\.]+)\b/i", "<a href=\"mailto:$0\">$0</a>", $string);
		$string = preg_replace('#(http|https|ftp)://([^\s]*)#', '<a href="\\1://\\2" target="_blank">\\1://\\2</a>', $string);

		return $string;
	}

    // lõhu kõige pikemad sõnad (kristo pärast :))

	function ext_break_long($string) {
		return preg_replace("/([^\s]{80})(?=[^\s])/", "$1<br/>", $string);
	}
}

?>
