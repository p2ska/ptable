<?php

// imap postkasti sisu hankimine

if (isset($v->post->ptable["data"]["imap"])) {
	require PLUGIN_PATH. "/srm/inc/lib/class_email.php";

    $d = new DATABASE();

    if (!$d->connect(DB_HOST, DB_USER, DB_PASS, PLUGIN_SRM)) {
	    $w->alert[ERROR][] = "C001 " . $l->txt_err_open_database;
	    return false;
    }

    $email = new EMAIL($d, $v->post->ptable["data"]["imap"]);
	$email->fetch();

    $detect = [ "Tellimus / Order: #", "Tellimus: #", "Order: #", "Ylesanne: #" ];
	$data_array = [];

	foreach ($email->c as $msg) {
	    $found_reply = false;

		foreach ($detect as $detected) {
			if (strpos($msg->subject, $detected) !== false) {
			    $found_reply = true;

			    break;
		    }
	    }

		if ($found_reply || $msg->answered == 'A' || !isset($msg->body) || !isset($msg->size))
			continue;

		$data_array[] = $msg;
	}
}

// põhiklassi extension, võimaldamaks päringust saadud väärtusi edasi töödelda

class PTABLE_EXT extends PTABLE {
	// protsessi timeline välju

	function timeline(&$data) {
		if (isset($this->data["template"]) && $this->data["template"] == "timeline_task")
			$fields	= [ "owner_id", "deadline", "file", "status", "result" ];
		else
			$fields	= [ "solver", "prio", "duedate", "file", "status", "result" ];

		$changes = json_decode($data->changes);

		foreach ($fields as $field) {
			if (isset($changes->{ $field }))
				$data->{ $field } = $changes->{ $field };
		}

		/*if (isset($changes->status) && isset($changes->result)) {
			if ($changes->status <= 2 && $changes->result == 0)
				$data->result = "-";
		}*/
	}

	function filename($str) {
		if (!substr_count($str, "::"))
			return $str;

		list($method, $filename) = explode("::", $str);

		if ($method == "D")
			return $this->l->txt_file_deleted. ": ". $this->break_long($filename, 50);
		else
			return $this->l->txt_file_added. ": ". $this->break_long($filename, 50);
	}

	function msg_content($uid) {
		global $email;

		$email->fetch();
		$msg = $email->msg($uid);

		$content = $msg->body;
		$content.= "<a href=\"/". $this->data["focus"]. "/". $this->data["sess"]. "/request:mail:". $uid. "\" ";
		$content.= "class=\"ajax-load btn btn-primary\">". $this->l->txt_take. "</a>";

		return $content;
	}

    // hangi lugemata kirjade arv

	function unread_messages($parent_id) {
		global $u;

		//$pd = new P_DATABASE();

		//$pd->connect(DB_HOST, PLUGIN_SRM, DB_USER, DB_PASS, "utf8", "utf8_general_ci");

		// tellimuse uued sõnumid

		//$pd->query("select id from request where id = ? && ", [ $parent_id ]);

		$q = "select request.id from request left join chat on request.id = chat.parent_id ";
		$q.= "where request.id = ? && request.solver = ? && chat.person != ? && chat.unread = ?";

        $this->db->query($q, [ $parent_id, $u->login_name, $u->login_name, "y" ]);

		$count = $this->db->rows;

		// ülesannete uued sõnumid

		$q = "select chat.id from chat left join task on chat.parent_id = task.id ";
		$q.= "where task.parent_id = ? && task.owner_id = ? && chat.person != ? && chat.unread = ?";

		$this->db->query($q, [ $parent_id, $u->login_name, $u->login_name, "y" ]);

		$count += $this->db->rows;

		if ($count) {
			$parent_id .= " <span class=\"badge\" title=\"". $this->l->txt_unread_notes. "\">". $count. "</span>";
		}

		return $parent_id;
    }

	function notes($notes) {
		if ($notes) {
			return "&nbsp;<span class=\"badge\" title=\"". $this->l->txt_unread_notes. "\">". $notes. "</span>";
		}
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
			return date("d.m.Y H:i", $timestamp);
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

	function break_long($string, $length = 80) {
		return preg_replace("/([^\s]{". $length. "})(?=[^\s])/", "$1<br/>", $string);
	}

	// kuva nimi

	function display_name($uid) {
		if (substr_count($uid, ".")) {
			$url = sprintf("%s/name:". $uid, WS_LDAP_CACHE);
			json_get($url, $c);

			if (isset($c[0]->displayname))
				return $c[0]->displayname;
		}

		return $uid;
	}

    // hangi kasutajainfo

    function person_info($uid) {
		global $l;

		$overview = "";

		if (substr_count($uid, "@"))
			list($uid) = explode("@", $uid);

		if (substr_count($uid, ".")) {
			$url = sprintf("%s/name:". $uid, WS_LDAP_CACHE);
			json_get($url, $c);

			if (count($c)) {
				$overview  = $this->pr_row(@$l->txt_name, $c[0]->displayname);
				$overview .= $this->pr_row(@$l->txt_email, $c[0]->mail);
				$overview .= $this->pr_row(@$l->txt_phone, $c[0]->telephonenumber);
				$overview .= $this->pr_row(@$l->txt_mobile, $c[0]->mobile);
				$overview .= $this->pr_row(@$l->txt_other_mobile, $c[0]->othermobile);
				$overview .= $this->pr_row(@$l->txt_profession, $c[0]->title);
				$overview .= $this->pr_row(@$l->txt_room, $c[0]->roomnumber);
				$overview .= $this->pr_row(@$l->txt_ou, $c[0]->department);
			}
		}

		/*if (!$overview) {
			$overview  = "<div class=\"info_row\"><div class=\"info_val\">Kahjuks ei leidnud sellise</div></div>";
			$overview .= "<div class=\"info_row\"><div class=\"info_val\">kasutajanimega seotud kontot!</div></div>";
		}*/

		return $overview;
    }

	function pr_row($key, $val) {
		if ($val) {
			if (substr_count($val, "@"))
				$val = "<a href=\"mailto:". $val. "\">". $val. "</a>";

			$pr = "<div class=\"info_row\">";
			$pr.= "<div class=\"info_key\">". $key. ":</div>";
			$pr.= "<div class=\"info_val\">". str_replace(";", "", $val). "</div>";
			$pr.= "</div><br/>";

			return $pr;
		}
	}
}

?>
