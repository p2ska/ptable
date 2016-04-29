<?php

define("P_TABLES",	"c:/xampp/htdocs/ptable/tables");

require_once("_db.php");
require_once("_ptable.php");

// põhiklassi extension, võimaldamaks päringust saadud väärtusi edasi töödelda

class PTABLE_EXT extends PTABLE {
    // proovime

    function timeline(&$data) {
        //$value["timeline"] = "kana";
        $data->prio2 = 3;
        //return $value;
    }

    // hangi kasutajainfo

    function person($string) {
        return "nimi: ". $string;
    }

	// hangi sõnumi sisu

	function msg_content($uid) {
		return "hahaeh";
	}

    // hangi lugemata kirjade arv

    function unread_messages($parent_id) {
        $db = new P_DATABASE();
        $db->connect(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET, DB_COLLATION);

        $db->query("select sum(notes) as notes from task where parent_id = ?", array($parent_id));

        if ($db->rows) {
            $result = $db->get_obj();

            // kui on lugemata teateid

            if ($result->notes)
                $parent_id .= " ". $result->notes;
        }

        return $parent_id;
    }

	// koverteeri kuupäevad eesti regioonile vastavaks

	function convert_date($date) {
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

	function convert_time($time) {
		return substr($time, 0, 5);
	}

	// muuda emailiaadressid ja veebilingid linkideks

	function autolink($string) {
		$string = preg_replace("/(([\w\.-]+))(@)([\w\.]+)\b/i", "<a href=\"mailto:$0\">$0</a>", $string);
		$string = preg_replace('#(http|https|ftp)://([^\s]*)#', '<a href="\\1://\\2" target="_blank">\\1://\\2</a>', $string);

		return $string;
	}

    // lõhu kõige pikemad sõnad (kristo pärast :))

	function break_long($string) {
		return preg_replace("/([^\s]{80})(?=[^\s])/", "$1<br/>", $string);
	}
}

?>
