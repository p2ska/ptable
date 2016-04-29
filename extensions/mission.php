<?php

/* põhiklassi extension, võimaldamaks päringust saadud väärtusi edasi töödelda */

class PTABLE_EXT extends PTABLE {
	// aruande staatuse kuvamine

	function status(&$data) {
		$status = $data->status;
		$approval = VOID;

		// tavaline staatuse kirjeldus

		if (isset($this->l->{ "txt_status_". $status }))
			$data->status = $this->l->{ "txt_status_". $status };

		// kas on lähetus läbi ja aruanne esitamata?

		if ($status == MISSION_ACCEPTED && $data->last_day < date("Y-m-d"))
			$data->status = $this->l->{ "txt_status_". MISSION_NO_REPORT };

		//if ($status == MISSION_NO_REPORT)
		//	$data->status = $this->l->{ "txt_status_". MISSION_NO_REPORT }. " <button data-url='/planner/". $this->data["sess"]. "/report:add' class='ajax-load'>". $this->l->txt_create_report. "</button>";
		if ($status == MISSION_REVIEWING) {
			// kas on võimalik kuvada, mis seisus kooskõlastamisega ollakse?

			if ($status == MISSION_REVIEWING) {
				$total = 1;
				$approved = 0;

				$this->db->query("select * from finance where parent_id = ?", [ $data->id ]);

				if ($this->db->rows)
					$total += $this->db->rows;

				foreach ($this->db->get_all() as $obj)
					if ($obj->status)
						$approved++;

				if ($data->struct_head_approved)
					$approved++;

				$approval = " (". $approved. "/". $total. ")";
			}

			$data->status .= $approval;
		}
	}

	// protsessi timeline välju

	function timeline(&$data) {
		if (isset($this->data["template"]) && $this->data["template"] == "timeline_task")
			$fields	= [ "owner_id", "deadline", "status", "result" ];
		else
			$fields	= [ "solver", "prio", "duedate", "status", "result" ];

		$changes = json_decode($data->changes);

		foreach ($fields as $field) {
			if (isset($changes->{ $field }))
				$data->{ $field } = $changes->{ $field };
		}
	}

	// eesmärgi kuvamine

	function goal(&$data) {
		// kui eesmärk on "muu", siis kuva täpsustust

		if ($data->goal == "OPJ_MOB_EESMARK_15")
			$data->goal = $data->goal_other;
		else {
			$this->db->query("select value_". $this->lang. " as value from helper_data where parent = ? && var = ? limit 1", [ "goal", $data->goal ]);

			if ($this->db->rows) {
				$result = $this->db->get_obj();

				$data->goal = $result->value;
			}
		}
	}

	// kuva riigi tõlge

	function country($code) {
		$this->db->query("select value_". $this->lang. " as value from helper_data where parent = ? && var = ? limit 1", [ "country", $code ]);

		if ($this->db->rows) {
			$obj = $this->db->get_obj();

			return $obj->value;
		}
		else
			return $code;
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

	function break_long($string) {
		return preg_replace("/([^\s]{80})(?=[^\s])/", "$1<br/>", $string);
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
}

?>
