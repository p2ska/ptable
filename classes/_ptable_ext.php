<?php

define("P_TABLES",	"c:/xampp/htdocs/ptable/ptables");

require_once("_db.php");
require_once("_ptable.php");

// põhiklassi extension, võimaldamaks päringust saadud väärtusi edasi töödelda

class PTABLE_EXT extends PTABLE {
	// muuda emailiaadressid ja veebilingid linkideks

	function ext_autolink($string) {
		$string = preg_replace("/(([\w\.]+))(@)([\w\.]+)\b/i", "<a href=\"mailto:$0\">$0</a>", $string);
		$string = preg_replace('#(http|https|ftp)://([^\s]*)#', '<a href="\\1://\\2" target="_blank">\\1://\\2</a>', $string);

		return $string;
	}

	// koverteeri kuupäevad eesti regioonile vastavaks

	function ext_convert_date($value) {
		if (strlen($value) <= 10) // kui on lühike formaat, ilma kellaajata
			return date("d.m.Y", strtotime($value));
		else
			return date("d.m.Y H:i:s", strtotime($value));
	}

    // võta sekundid maha

	function ext_convert_time($value) {
		return substr($value, 0, 5);
	}

    // lõhu kõige pikemad sõnad (kristo pärast :))

	function ext_break_long($string, $len = 80) {
		return preg_replace("/([^\s]{". $len. "})(?=[^\s])/", "$1<br/>", $string);
	}
}

?>
