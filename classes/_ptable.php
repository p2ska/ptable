<?php

// [ptable]; Andres Päsoke

define("P_PTABLES",	"c:/xampp/htdocs/ptable/ptables");
define("P_ALLOWED",	"/[^a-zA-Z0-9\s\._-]/");
define("P_DOTS",	"/\.+/");

define("P_ALL",		"*");
define("P_ANY",		"%");
define("P_QMARK",	"?");
define("P_LN",		"\n");
define("P_SL",		"/");
define("P_DOT",		".");
define("P_VOID",	"");
define("P_PREFIX",	"ptable_");
define("P_EXACT",	" = ");
define("P_LIKE",	" like ");
define("P_OR",		" || ");
define("P_SELECT",	" select ");
define("P_FROM",	" from ");
define("P_WHERE",	" where ");
define("P_ORDER",	" order by ");
define("P_LIMIT",	" limit ");

class PTABLE {
	// kõik parameetrid (nb! need default'id kirjutatakse üle tabeli kirjeldusfaili ja ka ptable.js poolt tulevate väärtustega üle)

	var $db, $l, $mode, $target, $template, $url, $data, $translations, $nav, $navigation, $refresh,
	$database, $host, $username, $password, $charset, $collation, $query, $query_count, $values,
	$title, $style, $table, $fields, $joins, $where, $order, $way, $search, $pages, $records,
	$autosearch =	false,		// automaatne otsing
	$fullscreen	=	false,		// kas täisekraanivaade on lubatud
	$header_sep	= 	false,		// tabeli ülemine eraldusäär
	$footer_sep =	false,		// tabeli alumine eraldusäär
	$content =		false,		// kogu sisuosa
	$header = 		true,		// kas kuvatakse tabeli päist
	$download = 	true,		// kas tabeli sisu allalaadimine on lubatud
	$fields_descr = true,		// kas kuvatakse väljade kirjeldusi tabeli päises
	$prefs = 		true,		// kas kuvatakse seadistusi
	$searchable = 	true,		// kas kuvatakse otsingukasti
	$sizeable = 	true,		// kas lastakse kasutajal muuta kirjete arvu ühel lehel
	$nav_header = 	false,		// kas kuvatakse ülemist navigatsiooniriba
	$nav_footer = 	true,		// kas kuvatakse alumist navigatsiooniriba
	$nav_length = 	5,			// navigeerimisnuppude arv
	$autoupdate = 	0,			// kas tabelit uuendatakse automaatselt (aeg sekundites)
	$page = 		1,			// mitmendat lehekülge kuvatakse
	$page_size = 	10,			// mitu kirjet ühel lehel kuvatakse
	$order_icon = 	"chevron",	// milliseid ikoone kasutatakse sorteerimisjärjekorra kuvamiseks ()
	$nav_prev = 	"{{angle-double-left}}",	// 'eelmine'-nupp
	$nav_next = 	"{{angle-double-right}}",	// 'järgmine'-nupp
	$autoupdates = 	[ 5 => "5 sek", 10 => "10 sek", 30 => "30 sek", 60 => "1 min", 300 => "5 min", 600 => "10 min" ],	// "automaatsed uuendused"-valikukasti väärtused
	$page_sizes = 	[ 10 => "10", 20 => "20", 50 => "50" ]; // "kirjete arv lehel"-valikukasti väärtused

	// initsialiseeri kõik js poolt määratud muutujad

	function ptable($init, $db = false, $lang = false) {
		if (!isset($init["target"]))
			return false;

		// id

		$this->target = $this->safe($init["target"], 20);

		// kui pole väliseid tõlkeid juba, siis lae tabeli tõlkefailist

		if (!$lang) {
			if (class_exists("TRANSLATIONS")) { /* kui keelestringi pole kaasa antud ja translations klassi ka pole, noh siis polegi tõlkeid */
				$this->translations = new TRANSLATIONS();

				$this->l = $this->translations->import("lang/ptable.lang");
			}
		}
		else {
			$this->l = $lang;
		}

		// kirjuta klassi default'id tabelikirjelduse omadega üle

		if (!$this->init())
			return false;

		// kirjuta default'id JS omadega üle (puhasta input)

		foreach ($init as $key => $val)
			$this->{ $key } = $this->safe($val);

		// esmasel initsialiseerimisel vaadatakse, kas autoupdate sisse lülitada (tabelikirjelduse poolt nõutud)

		if ($this->mode == "init")
			$this->autoupdate = $this->refresh;

		// mis baasist tabel andmeid tahab võtta? tuleb uus ühendus luua?

		if ($this->host && $this->database && $this->username && $this->password)
			$this->db = @new P_DATABASE($this->host, $this->database, $this->username, $this->password, $this->charset, $this->collation);
		elseif (!$db && !$this->db)
			$this->db = @new P_DATABASE();
		else
			$this->db = $db;

		// hangi andmed ja moodusta tabel

		$this->fetch_data();
		$this->display();

		// kuva tabel

		echo $this->content;
	}

	// init 

	function init() {
		// et tabelikirjelduse failid oleks veidi mugavam ja lühem keelestringe välja kutsuda

		$l = &$this->l;

		// tabeli kirjelduse fail

		$this->template = P_PTABLES. P_SL. $this->target. ".php";

		// lae tabeli info

		if (file_exists($this->template)) {
			require_once($this->template);

			// kas navigeerimine lubada?

			if ($this->nav_header || $this->nav_footer)
				$this->navigation = true;
			else
				$this->navigation = false;

			return true;
		}
		else {
			// väga halb, et tabeli kirjeldust ei leidnud!

			return false;
		}
	}

	// hangi tabeli andmed

	function fetch_data() {
		$search = $limit = $field_list = $joined = false;

		// otsingutingumused

		if ($this->search) {
			foreach ($this->fields as $col) {
				if (!isset($col["field"]) || !$col["field"])
					continue;

				if (isset($col["searchable"]) && $col["searchable"]) {
					$left = $right = false;
					$find = P_EXACT;

					if (isset($col["search_left"]) && $col["search_left"]) {
						$left = P_ANY;
						$find = P_LIKE;
					}

					if (isset($col["search_right"]) && $col["search_right"]) {
						$right = P_ANY;
						$find = P_LIKE;
					}

					$search[] = $col["field"]. $find. P_QMARK;
					$this->values[] = $left. trim($this->search). $right;
				}
			}

			$search = implode(P_OR, $search);

			if (!$this->where)
				$search = P_WHERE. $search;
		}

		// mis väljad on defineeritud

		$this->get_fields($field_list, $joined);

		// mitu kirjet kokku on? arvuta lehekülgede arv (ainult esmasel initsialiseerimisel (TODO: aga kuidas on vahepeal täienenud tabeliga? kas tuleb uuesti arvutada))

		if (!$this->pages) {
			if (!$this->query_count) // kui ei ole juba tabelikirjelduses etteantud query_count'i päringut, siis koosta see
				$this->query_count = P_SELECT. $field_list[0]. P_FROM. $this->table. $joined. ($this->where ? P_WHERE. $this->where : P_VOID). $search;

			$this->db->query($this->query_count, $this->values);

			$this->records = $this->db->rows;

			if ($this->page_size == P_ALL)
				$this->pages = 1;
			else {
				$this->pages = intval(($this->records - 1) / intval($this->page_size)) + 1;
				$limit = P_LIMIT. (($this->page - 1) * $this->page_size). ", ". $this->page_size;
			}
		}

		// koosta põhipäring
		// TODO: põhipäringule lisada ka tüübiteisendused (convert_date jms)

		if ($this->records) {
			if ($this->query) // kui query on juba kirjeldatud, siis lisa ainult vajadusel otsing, sorteering ja limiit
				$this->query .= $search. ($this->order ? P_ORDER. $this->order. " ". $this->way : P_VOID). $limit;
			else
				$this->query = P_SELECT. implode(", ", $field_list). P_FROM. $this->table. $joined. 
				($this->where ? P_WHERE. $this->where : P_VOID). $search.
				($this->order ? P_ORDER. $this->order. " ". $this->way : P_VOID). $limit;

			// teosta päring

			$this->db->query($this->query, $this->values);
		}
	}

	// kuva tabel

	function display() {
		if ($this->mode == "init") {
			if ($this->header) {
				$this->content .= "<div id=\"". P_PREFIX. $this->target. "_header\" class=\"header\">";

				if ($this->title) {
					$this->content .= "<div class=\"title\">";

					if (isset($this->title_icon) && $this->title_icon)
						$this->content .= "<i class=\"fa fa-". $this->title_icon. "\"></i> ";

					$this->content .= "<u>". $this->title. "</u></div>";
				}

				if ($this->prefs || $this->searchable) {
					$this->content .= "<div class=\"pref_search\">";

					if ($this->prefs) {
						$this->prefbox();

						$this->content .= "<span class=\"pref\">";
						$this->content .= "<span id=\"". P_PREFIX. $this->target. "_pref\" class=\"pref_btn\" title=\"". $this->l->txt_pref_btn. "\"><i class=\"fa fa-cog\"></i></span>";
						$this->content .= "</span>";
					}

					if ($this->searchable)
						$this->searchbox();

					$this->content .= "</div>";
				}

				$this->content .= "</div>";
				$this->content .= "<br clear=\"all\"/>";
			}

			$this->content .= "<div id=\"". P_PREFIX. $this->target. "_container\">";
		}

		$this->content .= "<table id=\"". P_PREFIX. $this->target. "\" ";
		//$this->content .= ($this->style ? " class=\"". $this->style. "\"" : P_VOID). " ";
		$this->content .= "data-records=". ($this->records ? $this->records : "0"). " ";
		$this->content .= "data-page=". $this->page. " ";
		$this->content .= "data-pages=". $this->pages. " ";
		$this->content .= "data-page_size=". $this->page_size. " ";
		$this->content .= "data-order=\"". $this->order. "\" ";
		$this->content .= "data-way=\"". $this->way. "\" ";
		$this->content .= "data-navigation=\"". ($this->navigation ? "true" : "false"). "\" ";
		$this->content .= "data-autoupdate=\"". ($this->autoupdate ? $this->autoupdate : "0"). "\" ";
		$this->content .= "data-autosearch=\"". ($this->autosearch ? "true" : "false"). "\">";
		$this->content .= "<tbody>";

		// kui on ülemine navigeerimine lubatud

		if ($this->nav_header)
			$this->navigation("header");

		// väljade kirjeldused

		if ($this->fields_descr)
			$this->fields_descr();

		// tulemused

		if ($this->records) {
			while ($obj = $this->db->get_obj()) {
				// käi väärtused üle ja töötle vastavalt vajadustele

				foreach ($this->fields as $field)
					if (isset($field["extend"]))
						$obj->{ $field["field"] } = $this->extend($obj->{ $field["field"] }, $field["extend"]);

				// kas kogu real on trigger küljes?

				$row["field"] = "ROW";

				$this->output($row, $obj);

				// nüüd vaata, kas väljale on defineeritud trigger või mitte, ja väljasta väärtus

				foreach ($this->fields as $field)
					if (!(isset($field["hidden"]) && $field["hidden"]))
						$this->output($field, $obj);

				$this->content .= "</tr>";
			}
		}
		else {
			$this->content .= "<tr><td colspan=100>". $this->l->txt_notfound. "</td></tr>";
		}

		// footer

		if ($this->nav_footer)
			$this->navigation("footer");

		$this->content .= "</tbody>";
		$this->content .= "</table>";

		if ($this->mode == "init") {
			$this->content .= "</div>";

			// kui on otsingukast olemas ja auto-otsing, siis säti fookus ja kursor ka viimaseks otsingukastis

			if ($this->search && $this->searchable && $this->autosearch) {
				$this->content .= "<script>";
				$this->content .= "var searchbox = $(\"#". P_PREFIX. $this->target. "_search\");";
				$this->content .= "searchbox.focus();";
				$this->content .= "searchbox[0].setSelectionRange(100, 100);";
				$this->content .= "</script>";
			}
		}
	}

	// 

	// muutujate töötlemine

	function extend($value, $extensions) {
		if (!is_array($extensions))
			$extensions = [ $extensions ];

		foreach ($extensions as $extension)
			if (method_exists($this, "ext_". $extension))
				$value = $this->{ "ext_". $extension }($value);

		return $value;
	}

	function output($field, $data) {
		if (isset($this->triggers[$field["field"]])) {
			$link = $title = P_VOID;
			$class = "trigger";
			$trigger = $this->triggers[$field["field"]];

			if (isset($field["class"]) && $field["class"])
				$class .= " ". $field["class"];

			if (isset($trigger["title"]))
				$title = $this->replace_markup($trigger["title"], $data);

			if (isset($trigger["link"])) {
				$link = $this->replace_markup($trigger["link"], $data);

				if (!$title)
					$title = $link;
			}

			if ($field["field"] == "ROW")
				$this->content .= "<tr";
			else
				$this->content .= "<td";

			$link = str_replace("\"", "", $link);

			$this->content .= " class=\"". $class. "\"";			

			// 'data' overrideb lingi

			if (isset($trigger["data"]) && $trigger["data"]) {
				if (!is_array($trigger["data"]))
					$trigger["data"] = [ $trigger["data"] ];

				foreach ($trigger["data"] as $ext_field => $ext_data) {
					$ext_data = $this->replace_markup($ext_data, $data);

					$this->content .= " data-". $ext_field. "=\"". $ext_data. "\"";
				}

				if ($title)
					$this->content .= " title=\"". $title. "\"";
			}
			elseif ($link) {
				$this->content .= " data-link=\"". $link. "\"";

				if (isset($trigger["external"]) && $trigger["external"])
					$this->content .= " data-ext=\"true\" title=\"-> ". $title. "\"";
				else
					$this->content .= " title=\"". $title. "\"";
			}

			if ($field["field"] == "ROW")
				$this->content .= ">";
			else
				$this->content .= ">". $data->{ $field["field"] }. "</td>";
		}
		else {
			if ($field["field"] == "ROW")
				$this->content .= "<tr>";
			else {
				if (isset($data->{ $field["field"] })) {
					$colspan = $class = $style = P_VOID;
					$styles = array();

					$this->content .= "<td";

					if (isset($field["colspan"]) && $field["colspan"])
						$colspan = " colspan=". $field["colspan"];

					if (isset($field["class"]) && $field["class"])
						$class = " class=\"". $field["class"]. "\"";

					if (isset($field["align"]) && $field["align"])
						$styles[] = "text-align: ". $field["align"];

					if (isset($field["nowrap"]) && $field["nowrap"])
						$styles[] = "white-space: ". ($field["nowrap"] ? "nowrap" : "none");

					if (count($styles))
						$style = " style=\"". implode("; ", $styles). "\"";

					$this->content .= $colspan. $class. $style. ">";

					$value = $data->{ $field["field"] };

					// kas on vaja kuvada hoopis vastava indeksiga tõlget?

					if (isset($field["translate"]) && $field["translate"]) {
						if (isset($this->l->{ $field["translate"]. $value }))
							$value = $this->l->{ $field["translate"]. $value };
					}

					$this->content .= $value. "</td>";
				}
				else {
					$this->content .= "<td></td>";
				}
			}
		}
	}

	// tabeli väljade kirjeldused

	function fields_descr() {
		$this->content .= "<tr>";

		foreach ($this->fields as $field) {
			if (!isset($field["field"]) || !$field["field"] || (isset($field["hidden"]) && $field["hidden"]))
				continue;

			if (isset($field["sortable"]) && !$field["sortable"]) {
				$this->content .= "<th class=\"no_order". ($this->order == $field["field"] ? " active" : ""). "\">". $field["title"]. "</th>";
			}
			else {
				if ($this->order == $field["field"])
					$active = " active";
				else
					$active = P_VOID;

				if ($this->way == "asc")
					$order = "up";
				else
					$order = "down";

				if (!isset($field["title"]))
					$field["title"] = $field["field"];

				$this->content .= "<th class=\"order". $active. "\" ";
				$this->content .= "data-field=\"". $field["field"]. "\">". $field["title"];
				$this->content .= "<i class=\"sort_icon". $active. " fa fa-". $this->order_icon. "-". ($this->order == $field["field"] ? $order : "down"). "\"></i>";

				if (isset($field["field_search"]) && $field["field_search"]) {
					$this->content .= "<span class=\"field_search\">";
					$this->content .= "<input type=\"text\" id=\"". P_PREFIX. $this->target. "_". $field["field"]. "_searchbox\" class=\"field_search_input\"></span>";
					$this->content .= "<span id=\"". P_PREFIX. $this->target. "_". $field["field"]. "_search\" class=\"search_btn field_search_btn small\" title=\"". $this->l->txt_field_search. "\">";
					$this->content .= "<i class=\"fa fa-search\"></i></span>";
				}

				$this->content .= "</th>";
			}
		}

		$this->content .= "</tr>";

		// kui vaja eraldada tabeliosa väljakirjeldustest

		if ($this->header_sep)
			$this->content .= "<tr class=\"no_hover\"><td class=\"border_top\" colspan=100></td></tr>";
	}

	// otsingukast

	function searchbox() {
		$this->content .= "<span class=\"search\">";
		$this->content .= "<input type=\"text\" id=\"". P_PREFIX. $this->target. "_search\" class=\"search_field\" value=\"". $this->search. "\"> ";
		$this->content .= "<span id=\"". P_PREFIX. $this->target. "_commit_search\" class=\"search_btn\" title=\"". $this->l->txt_search. "\"><i class=\"fa fa-search\"></i></span>";
		$this->content .= "</span>";
	}

	// valikukast

	function prefbox() {
		$this->content .= "<div id=\"". P_PREFIX. $this->target. "_prefbox\" class=\"prefbox\">";
		$this->content .= $this->awesome_eee($this->l->txt_pref). "<br/><br/>";
		$this->content .= $this->print_pref($this->l->txt_pagesize, $this->page_sizes, "dropdown", $this->page_size, "pagesize");
		$this->content .= $this->print_pref($this->l->txt_autoupdate, $this->autoupdate, "autoupdate_check", $this->autoupdate, "autoupdate");
		//$this->content .= "<br/><br/>";
		//$this->content .= "<span class=\"big_btn\">". $this->l->txt_save. "</span>";
		//$this->content .= "<span class=\"big_btn\">". $this->l->txt_close. "</span>";
		$this->content .= "</div>";
	}

	// valikukasti väljade printimine

	function print_pref($key, $val, $type = false, $c_val = false, $name = false) {
		if (method_exists($this, "form_". $type))
			$val = $this->{ "form_". $type }($val, $c_val, $name);

		$pr = "<div class=\"pref_row\">";
		$pr.= "<div class=\"pref_key\">". $key. ":</div>";
		$pr.= "<div class=\"pref_val\">". $val. "</div>";
		$pr.= "</div><br/>";

		return $pr;
	}

	// keera tekstis {ikoon} font-awesome ikooniks

	function awesome_eee($str) {
		$str = str_replace("{{", "<i class=\"fa fa-", $str);
		$str = str_replace("}}", "\"></i>", $str);

		return $str;
	}

	// vormi dropdown

	function form_dropdown($values, $current_val, $element) {
		$pr = "<select id=\"". P_PREFIX. $this->target. "_". $element. "\" data-table=\"". $this->target. "\" class=\"". $element. "\"";
		$pr.= ($current_val ? "" : " disabled"). ">";

		foreach ($values as $key => $val) {
			$pr .= "<option value=\"". $key. "\"";

			if ($current_val == $key)
				$pr .= " selected";

			$pr .= ">". $val. "</option>";
		}

		$pr .= "</select>";

		return $pr;
	}

	// vormi checkbox ja tekstiväli

	function form_autoupdate_check($values, $current_val, $element) {
		/* kas autoupdate on aktiivne */

		$id = P_PREFIX. $this->target;

		$pr  = "<div style=\"float: left\">";
		$pr .= "<i id=\"". $id. "_autoupdate_off\" data-table=\"". $this->target. "\" class=\"autoupdate_check ". ($current_val ? "hide " : ""). "off fa fa-square-o\"></i>";
		$pr .= "<i id=\"". $id. "_autoupdate_on\" data-table=\"". $this->target. "\" class=\"autoupdate_check ". ($current_val ? "" : "hide "). "fa fa-check-square-o\"></i>";
		$pr .= "<input type=\"hidden\" id=\"". $id. "_autoupdate_value\" class=\"". $id. "_value\" value=\"". $current_val. "\">";
		$pr .= "</div>";

		/* autoupdate valikud */

		$pr .= "<div style=\"float: right\">";
		$pr .= $this->form_dropdown($this->autoupdates, $current_val, "autoupdate_select");
		$pr .= "</div>";

		return $pr;
	}

	// navigatsioon

	function navigation($type) {
		if ($type == "footer" && $this->footer_sep)
			$this->content .= "<tr class=\"no_hover\"><td colspan=100 class=\"border_btm\"></td></tr>";

		if ($this->nav) {
			$this->content .= $this->nav;

			return true;
		}

		$nav_page = 0;
		$from = ($this->page - 1) * $this->page_size + 1;
		$to = $from + $this->page_size - 1;

		// et viimane marker poleks suurem kui kirjete arv

		if ($to > $this->records)
			$to = $this->records;

		$this->nav = "<tr class=\"no_hover\"><td colspan=100 class=\"nav_row\">";
		$this->nav.= "<span class=\"count\">". $this->l->txt_found. ": ". $this->records;
		$this->nav.= ($this->records && $this->page_size != P_ALL ? " (". $from. "-". $to. ")" : "");
		$this->nav.= "</span>";

		// kui mõni tulemus ikka leiti, siis kuva navigatsiooninupud (tagurpidi, kuna meil on float: right)

		/* navigeerimisloogika

			LEHT, P = x
			LAIUS = 5
			LEHTI, f = 15							LEHTI <= 7
			X = LAIUS + 2
			X2 = LAIUS - 2

			1.									2.

			P	1|  LAIUS  | P >= f				kui P < X (või tagantpoolt ettepoole)
			------------------					----------
			1  <1>2 3 4 5 6|f				   <1>2 3 4 5 6 7
			2	1<2>3 4 5 6|f					1<2>3 4 5 6 7
			3	1 2<3>4 5 6|f					1 2<3>4 5 6 7
			4	1 2 3<4>5 6|f					1 2 3<4>5 6 7
			5	1|3 4<5>6 7|f					1 2 3 4<5>6 7
			6	1|4 5<6>7 8|f					1 2 3 4 5<6>7
			7	1|5 6<7>8 9|f					1 2 3 4 5 6<7>
			8	1|6 7<8>9 a|f					1|3 4 5 6 7<8>
			9	1|7 8<9>a b|f					1|4 5 6 7 8<9>
			10	1|8 9<a>b c|f					
			11	1|9 a<b>c d|f					
			12	1|a b<c>d e f
			13	1|a b c<d>e f
			14	1|a b c d<e>f
			15  1|a b c d e<f>

			1) "..." peale <1> prinditakse kui: LEHT >= LAIUS && LEHTI > X
			2) "..." enne <LEHTI> kui: LEHT < (LEHTI - X2) && LEHTI > X
			3) "<x>" aktiivne = LEHT
			4) 1 prinditakse alati
			5) prinditakse ülejäänud elemendid
			6) viimane nr prinditakse kui LEHTI > 1
		*/

		if ($this->records && $this->page_size != P_ALL) {
			//$w = intval($this->nav_length / 2);
			$x = $this->nav_length + 2;
			$x2 = $x - 4;
			$a = 2;

			$this->nav.= "<span class=\"navigation\">";

			if ($this->page < 2)
				$this->add_nav_btn(1, $this->awesome_eee($this->nav_prev), true);
			else
				$this->add_nav_btn($this->page - 1, $this->awesome_eee($this->nav_prev));

			/* algusleht */

			$this->add_nav_btn(1, 1);

			/* kas on vaja printida eraldaja */
			/* TODO: hetkel toimib korralikult kui nav_length = 5 */

			if ($this->page >= $this->nav_length && $this->pages > $x)
				$this->nav.= "<span class=\"sep\"></span>";

			/* prindi vahepealsed nupud */

			while ($a < $x && $a < $this->pages) {
				if (($this->pages > $x && $this->page < $this->nav_length) || $this->pages <= $x)		/* kui leht on vasakul pool tsentrit */
					$page = $a;
				elseif ($this->page > ($this->pages - $this->nav_length + 2))							/* kui leht on paremalpool tsentrit */
					$page = $this->pages - $this->nav_length + $a - 2;
				else																					/* kui leht on tsentris */
					$page = $this->page + $a - $this->nav_length + 1;

				$this->add_nav_btn($page, $page);

				$a++;
			}

			/* kas on vaja printida eraldaja */

			if ($this->page < ($this->pages - $x2) && $this->pages > $x)
				$this->nav.= "<span class=\"sep\"></span>";

			/* prindi viimase lehe nupp */

			if ($this->pages > 1)
				$this->add_nav_btn($this->pages, $this->pages);

			if ($this->page >= $this->pages)
				$this->add_nav_btn($this->pages, $this->awesome_eee($this->nav_next), true);
			else
				$this->add_nav_btn($this->page + 1, $this->awesome_eee($this->nav_next));

			$this->nav.= "</span>";
		}

		$this->nav.= "</td></tr>";

		$this->content .= $this->nav;
	}

	function add_nav_btn($page, $title, $denied = false) {
		$this->nav.= "<span class=\"nav". ($denied ? " denied" : ""). ($this->page == $page && !$denied ? " selected" : ""). "\" ";
		$this->nav.= "data-page=\"". $page. "\">". $title. "</span>";
	}

	// otsi lingist väljade indikaatorid

	function replace_markup($link, $data) {
		$datalink = $fields = [];

		foreach (explode("[", $link) as $field) {
			$ex = explode("]", $field);

			if (!isset($ex[1]))
				$fields[] = trim($ex[0]);
			else {
				if (isset($ex[0]) && $ex[0] && isset($data->{ $ex[0] }))
					$fields[] = trim($data->{ $ex[0] });

				if (isset($ex[1]) && $ex[1])
					$fields[] = trim($ex[1]);
			}
		}

		return implode(P_VOID, $fields);
	}

	// hangi väljade list

	function get_fields(&$fields, &$joined) {
		$fields = $joined_field = [];
		$joined = P_VOID;

		// lisa põhitabeli väljad (liidetava tabeli omasid mitte)

		foreach ($this->fields as $field)
			if (!(isset($field["joined"]) && $field["joined"])) {
				if (isset($field["field"]) && $field["field"])
					$fields[] = $this->table. ".". $field["field"];
			}
		else
			$joined_field[$field["field"]] = true;

		// lisa liidetava tabeli väljad

		if (isset($this->joins) && is_array($this->joins)) {
			foreach ($this->joins as $join)
				if (isset($join["table"]))
					$joins[] = $join["method"]. " ". $join["table"]. " on ". $join["on"];
			elseif (isset($joined_field[$join["alias"]]))
				$fields[] = $join["field"]. " as ". $join["alias"];

			$joined = " ". implode(" && ", $joins);
		}
	}

	// tee JS tulev sisend turvaliseks

	function safe($input, $length = false) {
		if (!is_array($input)) {
			$output = preg_replace(P_DOTS, P_DOT, preg_replace(P_ALLOWED, P_VOID, trim($input)));

			if ($length)
				$output = substr($output, 0, $length);
		}
		else {
			foreach ($input as $key => $val) {
				$output[$key] = preg_replace(P_DOTS, P_DOT, preg_replace(P_ALLOWED, P_VOID, trim($val)));

				if ($length)
					$output[$key] = substr($output[$key], 0, $length);
			}
		}

		return $output;
	}
}

?>
