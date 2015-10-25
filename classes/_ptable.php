<?php

// [ptable]; Andres Päsoke

define("P_ALLOWED",	"/[^\p{L}\p{N}\s\._-]/u");
define("P_DOTS",	"/\.+/");
define("P_ALL",		"*");
define("P_ANY",		"%");
define("P_Q",		"?");
define("P_LN",		"\n");
define("P_SL",		"/");
define("P_DOT",		".");
define("P_VOID",	"");
define("P_FSS",		"___");
define("P_BR",      "<br/>");
define("P_2BR",     "<br/><br/>");
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

    var
    $content, $db, $l, $mode, $target, $template, $url, $class, $data, $translations, $autoupdate, $refresh,
    $database, $host, $username, $password, $charset, $collation, $table, $query, $fields, $where, $values,
	$search, $triggers, $joins, $order, $way, $limit, $records, $field_count, $field_search, $title, $style,
    $navigation, $nav_pre, $nav_post, $pages, $pagesize, $external_data, $external_pos, $col_width,
    $debug			= false,		// debug reziim (per tabel väljaspool ptable' enda arendamist)
    $header			= true,			// kas kuvatakse tabeli päist üldse
    $header_sep		= false,		// tabeli ülemine eraldusäär
    $footer_sep		= false,		// tabeli alumine eraldusäär
    $fields_descr	= true,			// väljade kirjeldused tabeli päises
    $prefs			= true,			// tabeli seadeid saab muuta
    $store_prefs	= true,			// kas salvestatakse muudatused (sorteerimisväli, suund, uuendused, lehe pikkus)
    $download		= true,			// TODO: tabeli sisu allalaadimise võimaldamine
    $autosearch		= false,		// automaatne otsing
    $searchable		= true,			// kas kuvatakse otsingukasti
    $sizeable		= true,			// kas lastakse kasutajal muuta kirjete arvu ühel lehel
	$resizable		= true,   		// kas saab veergude laiust muuta
    $minimize		= true,			// kas saab tabelit minimiseerida
	$minimized		= false,		// kas tabel on algselt minimiseeritud
    $maximize		= false,		// TODO: kas saab tabelit maximiseerida
    $nav_header		= false,		// kas kuvatakse ülemist navigatsiooniriba
    $nav_footer		= true,			// kas kuvatakse alumist navigatsiooniriba
    $nav_length		= 5,			// navigeerimisnuppude arv
    $page			= 1,			// mitmendat lehekülge kuvatakse
    $page_size		= 10,			// mitu kirjet ühel lehel kuvatakse
    $order_icon		= "chevron",	// milliseid ikoone kasutatakse sorteerimisjärjekorra kuvamiseks (chevron, sort, angle-double)
    $nav_prev		= "{{angle-double-left}}",	// 'eelmine'-nupp
    $nav_next		= "{{angle-double-right}}",	// 'järgmine'-nupp
    $autoupdates	= [ 5 => "5s", 10 => "10s", 30 => "30s", 60 => "1m", 300 => "5m", 600 => "10m" ],	// "automaatsed uuendused"-valikukasti väärtused
    $page_sizes		= [ 10 => "10", 20 => "20", 50 => "50" ]; // "kirjete arv lehel"-valikukasti väärtused

    // initsialiseeri kõik js poolt määratud muutujad

    function ptable($init, $source = false, $lang = false) {
        if (!isset($init["target"]))
            return false;

        // tabeli id

        $this->target = $this->safe($init["target"], 20);

        // kui pole väliseid tõlkeid juba, siis lae tabeli tõlkefailist;
        // kui translations klassi ka pole, noh siis polegi tõlkeid

        if (!$lang && class_exists("TRANSLATIONS")) {
            $this->translations = new TRANSLATIONS();

            $this->l = $this->translations->import("lang/ptable.lang");
        }
        else
            $this->l = $lang;

        // data[] muutuja edastamiseks tabelikirjeldusele

        if (isset($init["data"]) && $init["data"])
            $this->data = $this->safe($init["data"]);

        // kirjuta klassi default'id tabelikirjelduse omadega üle

        if (!$this->init())
            return false;

        // mitu välja defineeritud on?

        $this->field_count = count($this->fields);

        // kui kirjelduses pole tabelit paika pandud, siis võta defaultiks põhitabel

        for ($a = 0; $a < $this->field_count; $a++)
            if (!isset($this->fields[$a]["table"]))
                $this->fields[$a]["table"] = $this->table;

        // kirjuta default'id JS omadega üle (puhasta input)

		foreach ($init as $key => $val)
            $this->{ $key } = $this->safe($val);

        // kas on veergude laiused olemas

        if ($this->col_width)
            $this->col_width = explode("-", $this->col_width);

        // esmasel initsialiseerimisel vaadatakse, kas autoupdate sisse lülitada (tabelikirjelduse poolt nõutud)

        if ($this->mode == "init" && !isset($this->autoupdate))
            $this->autoupdate = $this->refresh;

        // kontrolli, kas sorteerimiseks vajalik on paigas

        if (!$this->order) {
            // kui sorteerimine pole paigas, siis pane selleks esimene deklareeritud väli

            if (isset($this->fields[0]["field"]))
                $this->order = $this->fields[0]["field"];
        }

        // kui tabeli kirjelduses on märgitud uus ühendus

        if ($this->host && $this->database && $this->username && $this->password) {
            $this->db = @new P_DATABASE();
            $this->db->connect($this->host, $this->database, $this->username, $this->password, $this->charset, $this->collation);
        }
        elseif (is_resource($source)) { // kui on antud olemasolev mysql resource link, siis tee uus klass ja topi link kohe külge
            $this->db = @new P_DATABASE();
            $this->db->connection = $source;
        }
        elseif (is_array($source)) { // kui sisendiks on andmemassiiv
            $this->external_data = $source;
        }
        elseif (!$source) { // kui üldse midagi ei antud sisendiks
            $this->db = @new P_DATABASE();

            if (!defined("DB_HOST") || !defined("DB_NAME") || !defined("DB_USER") || !defined("DB_PASS"))
                return false;

            $this->db->connect(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET, DB_COLLATION);
        }

        // hangi ja töötle andmeid

        if ($this->external_data)
            $this->prepare_external();
        else
            $this->fetch_data();

        // moodusta tabel

        $this->display();

        // kuvamiseks kasuta
        // echo $this->content;
    }

    // init

    function init() {
        // et tabelikirjelduse failid oleks veidi mugavam ja lühem keelestringe välja kutsuda

        $l = &$this->l;

        // tabeli kirjelduse fail

        $this->template = P_TABLES. P_SL. $this->target. ".php";

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
        $search = $order = $joined = false;

		// lisa tingimus 'where' kirjeldatule

        if ($this->where)
            $this->where = P_WHERE. $this->where;

        // otsingutingumused

		if ($this->field_search) {
			if (substr_count($this->field_search, P_FSS)) {
				list($f_search, $f_value) = explode(P_FSS, $this->field_search);

				$search[] = $f_search. " like ". P_Q;
				$this->values[] = P_ANY. trim($f_value). P_ANY;
			}
		}
		elseif ($this->search) {
            foreach ($this->fields as $field) {
                if (isset($field["searchable"]) && $field["searchable"]) {
                    $left = $right = P_ANY;
                    $find = P_LIKE;

                    if (isset($field["search_left"]) && !$field["search_left"])
                        $left = false;

                    if (isset($field["search_right"]) && !$field["search_right"])
                        $right = false;

                    if (!$left && !$right)
                        $find = P_EXACT;

					// kui väljal on alias, siis otsi hoopis selle järgi

					$search[] = $field["table"]. ".". $field["field"]. $find. P_Q;
                    $this->values[] = $left. trim($this->search). $right;
                }
            }
        }

		if ($search) {
			// eralda otsingutingimus

            $search = "(". implode(P_OR, $search). ")";

			// kui juba on mingid tingimused paika pandud

            if ($this->where)
                $this->where .= " && ". $search;
            else
                $this->where = P_WHERE. $search;
		}

		// pane päring kokku

        $this->query = $this->build_query();

        // mitu kirjet kokku on? arvuta lehekülgede arv (ainult esmasel initsialiseerimisel (TODO: aga kuidas on vahepeal täienenud tabeliga? kas tuleb uuesti arvutada))

        if (!$this->pages) {
            $this->db->query($this->query, $this->values);

			// mingi sql päringu viga

            if ($this->db->error && $this->debug)
				$this->content .= $this->db->error_msg. P_2BR;

			$this->records = $this->db->rows;

            if ($this->page_size == P_ALL)
                $this->pages = 1;
            else {
                $this->pages = intval(($this->records - 1) / intval($this->page_size)) + 1;

                // kui eelmisest otsingust on lehenumber jäänud suurem, siis vii kasutaja esimesele lehele

                if ($this->page > $this->pages)
                    $this->page = 1;

                $this->limit = P_LIMIT. (($this->page - 1) * $this->page_size). ", ". $this->page_size;
            }
        }

        // põhipäring

        if ($this->records) {
            // lisa päringule sorteerimine ja limiit (õige lehekülg)

            $this->query .= ($this->order ? P_ORDER. $this->order. " ". $this->way : P_VOID). $this->limit;

            // teosta päring

            $this->db->query($this->query, $this->values);

            if ($this->debug)
                $this->content .= "[ ". $this->query. " ]". P_BR. "< ". ($this->values ? implode(", ", $this->values) : ""). " >". P_2BR;

            // mingi sql päringu viga

            if ($this->db->error && $this->debug)
				$this->content .= $this->db->error_msg. P_2BR;
        }
    }

	// ehita päring tabelikirjeldusest

    function build_query() {
        $join_tables = false;
        $fields = $joins = [];

		// käi väljad läbi

        foreach ($this->fields as $field) {
            if (isset($field["alias"]) && $field["alias"])
                $alias = " as ". $field["alias"];
            else
                $alias = P_VOID;

			// kui tabel on eraldi märgitud (liidetud tabel), siis kasuta seda; vastasel juhul arvesta, et tegu on põhitabeli väljaga (default)

            if (isset($field["table"]) && $field["table"])
                $fields[] = $field["table"]. ".". $field["field"]. $alias;
            else
                $fields[] = $this->table. ".". $field["field"]. $alias;
        }

		// kas on joine?

		if ($this->joins) {
        	foreach ($this->joins as $j_table => $join)
            	$joins[] = $join["method"]. " ". $j_table. " on ". $join["on"];

        	if (count($joins))
            	$join_tables = " ". implode(", ", $joins);
		}

        return P_SELECT. implode(", ", $fields). P_FROM. $this->table. $join_tables. $this->where;
    }

    function prepare_external() {
        // otsingutingumused

        if ($this->search) {
            $search_field = array();

            // millistest väljadest otsida

            foreach ($this->fields as $col) {
                if (isset($col["searchable"]) && !$col["searchable"]) // kui ei soovita selle välja puhul otsida
                    continue;

                $left = $right = true; // by default tehakse täisteksti otsing

                if (isset($col["search_left"]) && !$col["search_left"]) // kui ei soovita otsida vasakult
                    $left = false;

                if (isset($col["search_right"]) && !$col["search_right"]) // kui ei soovita otsida paremalt
                    $right = false;

                // lisa väli otsitavate hulka

                $search_field[] = array("field" => $col["field"], "left" => $left, "right" => $right);
            }

            // kui on välju mille järgi otsida

            if (count($search_field)) {
                $records = count($this->external_data);

                for ($a = 0; $a < $records; $a++) {
                    $found = false;
                    $record = $this->external_data[$a];
                    // kas otsitavas väljas sisaldub otsisõna?

                    foreach ($search_field as $field) {
                        if (!isset($record->{ $field["field"] }) && !$record->{ $field["field"] })
                            continue;

                        $field_value = $record->{ $field["field"] };

                        if (!$field["left"] && !$field["right"] && $field_value == $this->search) { // täpne otsing
                            $found = true;
                            break; // kui juba ühest väljast leiti otsitav, siis pole mõtet edasi kontrollida
                        }
                        /* TODO: vasakule/paremale otsingud
						elseif ($field["left"] && !$field["right"]) {
						}
						elseif (!$field["left"] && $field["right"]) {
						}
						*/
                        elseif (substr_count($field_value, $this->search)) { // täisotsing
                            $found = true;
                            break;
                        }
                    }

                    // kui ei leitud antud rea puhul otsitavat, siis viska tulemustest välja

                    if (!$found)
                        unset($this->external_data[$a]);
                }
            }

            reset($this->external_data);
        }

        // mitu kirjet kokku on? arvuta lehekülgede arv

        $this->records = count($this->external_data);

        if ($this->page_size == P_ALL)
            $this->pages = 1;
        else { // kui on rohkem kui üks lehekülg (potensiaalselt), siis lõika massiivist õige tükk
            $this->pages = intval(($this->records - 1) / intval($this->page_size)) + 1;

            // kui eelmisest otsingust on lehenumber jäänud suurem, siis vii kasutaja esimesele lehele

            if ($this->page > $this->pages)
                $this->page = 1;

            $this->external_data = array_slice($this->external_data, ($this->page - 1) * $this->page_size, $this->page_size);
        }

        // kui midagi alles jäi, siis sorteeri kuidas vaja

        if ($this->records)
            usort($this->external_data, array($this, "sort_em"));
    }

    // massiivi sorteerimine vastavalt väljale ja suunale

    function sort_em($a, $b) {
        if (!isset($a->{ $this->order }) || !isset($b->{ $this->order }))
            return 0;

        $a = $a->{ $this->order };
        $b = $b->{ $this->order };

        if ($a == $b)
            return 0;

        if (!$this->way || $this->way == "asc")
            return ($a < $b) ? -1 : 1;
        else
            return ($b < $a) ? -1 : 1;
    }

    // kuva tabel

    function display() {
        if ($this->mode == "init") {
            if ($this->header) {
                $this->content .= "<div id=\"". P_PREFIX. $this->target. "_header\" class=\"header\">";

				// tabeli päise vasak pool (ikoon, tabeli nimi)

				$this->content .= "<div class=\"header_left\">";

                if ($this->title) {
                    $this->content .= "<div class=\"title\">";

                    if (isset($this->title_icon) && $this->title_icon)
                        $this->content .= "<i class=\"fa fa-". $this->title_icon. "\"></i> ";

                    $this->content .= "<u>". $this->title. "</u></div>";
                }

				// header_left lõpp

				$this->content .= "</div>";

				// tabeli päise parem pool (min, max, seaded, otsing)

				$this->content .= "<div class=\"header_right\">";

				// kas võimaldada tabli minimiseerimine

				if ($this->minimize)
					$this->small_btn(P_PREFIX. $this->target, "minimize_btn",
						"caret-". ($this->minimized ? "down" : "up"),
						"caret-". ($this->minimized ? "up" : "down"),
						@$this->l->txt_minimize_btn
					);

				// kas võimaldada tabli minimiseerimine

				if ($this->maximize)
					$this->small_btn(P_PREFIX. $this->target, "maximize_btn", "expand", "compress", @$this->l->txt_maximize_btn);

				// seadete kast

				if ($this->prefs)
					$this->prefbox();

				// otsingukast

				if ($this->searchable)
					$this->searchbox();

				// header_right lõpp

				$this->content .= "</div>";

				// headeri lõpp

                $this->content .= "</div>";

				// lõpeta igasugused floatimised

                $this->content .= "<br clear=\"all\"/>";
            }

			// kui on minimiseeritud initsialiseerimisel, siis peida tabeli sisuosa kohe

            $this->content .= "<div id=\"". P_PREFIX. $this->target. "_container\"". ($this->minimized ? " class=\"hide\"" : P_VOID). ">";
        }

        $this->content .= "<table id=\"". P_PREFIX. $this->target. "\" ";

        // kui on ilma ülemise ääreta tabel, siis muuda tabeli hover'i käitumist (äär kuvatakse hover'i puhul ümber tabeli sisuosa)

        if (substr_count($this->class, "no_border"))
            $this->content .= "class=\"table_hover\" ";

        $this->content .= "data-records=". ($this->records ? $this->records : "0"). " ";
        $this->content .= "data-page=". $this->page. " ";
        $this->content .= "data-pages=". $this->pages. " ";
        $this->content .= "data-page_size=". $this->page_size. " ";
        $this->content .= "data-order=\"". $this->order. "\" ";
        $this->content .= "data-way=\"". $this->way. "\" ";
        $this->content .= "data-navigation=\"". ($this->navigation ? "true" : "false"). "\" ";
        $this->content .= "data-autoupdate=\"". ($this->autoupdate ? $this->autoupdate : "0"). "\" ";
        $this->content .= "data-autosearch=\"". ($this->autosearch ? "true" : "false"). "\" ";
        //$this->content .= "data-minimized=\"". ($this->minimized ? "true" : "false"). "\" ";
        $this->content .= "data-store=\"". ($this->store_prefs ? "true" : "false"). "\">";
        $this->content .= "<thead>";

        // kui on ülemine navigeerimine lubatud

        if ($this->nav_header)
            $this->navigation("header");

        // väljade kirjeldused

        if ($this->fields_descr)
            $this->fields_descr();

        $this->content .= "</thead><tbody>";

        // tulemused

        if ($this->records) {
            if ($this->db) {
                while ($obj = $this->db->get_obj())
                    $this->print_row($obj);
            }
            else {
                foreach ($this->external_data as $obj)
                    $this->print_row($obj);
            }
        }
        else
            $this->content .= "<tr><td colspan=100>". @$this->l->txt_notfound. "</td></tr>";

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

	function small_btn($id, $class, $icon, $icon2, $title) {
		$this->content .= "<span data-parent=\"". $id. "\" class=\"". $class. " small_btn\" title=\"". $title. "\">";
		$this->content .= "<i class=\"fa fa-". $icon. "\"></i>";
		$this->content .= "<i class=\"fa fa-". $icon2. "\" style=\"display: none\"></i>";
		$this->content .= "</span>";
	}

    // väljasta väärtused baasist

    function print_row($obj) {
        // kas kogu real on trigger küljes?

        $row["field"] = "ROW";
        $this->output($row, $obj);

        // nüüd vaata, kas väljale on defineeritud trigger või mitte, ja väljasta väärtus

        $count = 0;

        foreach ($this->fields as $field) {
		  if (isset($field["extend"]))
			$obj->{ $field["field"] } = $this->extend($obj->{ $field["field"] }, $field["extend"]);

          $this->output($field, $obj, $count++);
        }

        $this->content .= "</tr>";
    }

    // muutujate töötlemine

    function extend($value, $extensions) {
        if (!is_array($extensions))
            $extensions = [ $extensions ];

        foreach ($extensions as $extension)
            if (method_exists($this, "ext_". $extension))
                $value = $this->{ "ext_". $extension }($value);

        return $value;
    }

    function output($field, $data, $pos = 0) {
        $link = $title = $class = $style = $colspan = P_VOID;
        $styles = array();

        if (isset($this->triggers[$field["field"]])) {
            $class = "trigger";
            $trigger = $this->triggers[$field["field"]];

            if (isset($trigger["class"]) && $trigger["class"])
                $class .= " ". $trigger["class"];

            if (isset($field["class"]) && $field["class"])
                $class .= " ". $field["class"];

            if (isset($field["colspan"]) && $field["colspan"])
                $colspan = " colspan=". $field["colspan"];

            if (isset($field["align"]) && $field["align"])
                $styles[] = "text-align: ". $field["align"];

            if (isset($field["nowrap"]) && $field["nowrap"])
                $styles[] = "white-space: ". ($field["nowrap"] ? "nowrap" : "none");

            if (count($styles))
                $style = " style=\"". implode("; ", $styles). "\"";

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
                $this->content .= "<td ";

            $link = str_replace("\"", "", $link);

            $this->content .= $colspan. " class=\"". $class. "\"". $style;

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
            else {
				// otsingusõna värvimine

				$value = $this->highlight($field, $data->{ $field["field"] });

				$this->content .= ">". $value. "</td>";

                if ($this->resizable && $pos < ($this->field_count - 1))
                    $this->content .= "<td class=\"resize\"></td>";
            }
        }
        else {
            if ($field["field"] == "ROW")
                $this->content .= "<tr>";
            else {
                $this->content .= "<td ";

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

				// juhuks, kui tabelite liitmisel on vaja kasutada alias'i (erinevates tabelites sama nimega väljad), siis loe väärtust aliase' väljalt

				if (isset($field["alias"]) && $field["alias"])
					$value = $data->{ $field["alias"] };
				else
                	$value = $data->{ $field["field"] };

                // kas on vaja kuvada hoopis vastava indeksiga tõlget?

                if (isset($field["translate"]) && $field["translate"]) {
					$tr_field = sprintf($field["translate"], $value);

					if (isset($this->l->{ $tr_field }))
                        $value = $this->l->{ $tr_field };
                }

				// otsingusõna värvimine

				$value = $this->highlight($field, $value);

				// kuva väärus

                $this->content .= $value. "</td>";

                if ($this->resizable && $pos < ($this->field_count - 1))
                    $this->content .= "<td class=\"resize\"></td>";
            }
        }
    }

	function highlight($field, $value) {
		if ($this->search)
			$value = preg_replace("#". preg_quote($this->search). "#i", "<font class=\"highlight\">\\0</font>", $value);
		elseif ($this->field_search) {
			list($f_field, $f_value) = explode(P_FSS, $this->field_search);

			// väljaotsingu puhul värvi ainult selle veeru otsingusõnasid

			if ($f_field == $field["table"]. ".". $field["field"])
				$value = preg_replace("#". preg_quote($f_value). "#i", "<font class=\"highlight\">\\0</font>", $value);
		}

		return $value;
	}

	// tabeli väljade kirjeldused

    function fields_descr() {
        $fields = count($this->fields);
        $current_field = 0;

        $this->content .= "<tr>";

        foreach ($this->fields as $field) {
            $current_field++;
            $no_order = false;

            if ($this->order == $field["table"]. ".". $field["field"])
                $active = " active";
            else
                $active = P_VOID;

            if ($this->way == "asc")
                $way = "up";
            else
                $way = "down";

            if (!isset($field["title"]))
                $field["title"] = $field["field"];

            if (isset($field["sortable"]) && !$field["sortable"])
                $no_order = "no_";

            $this->content .= "<th class=\"". $no_order. "order". $active. " \" ";

            // kas veeru laiused on muudetavad ja on paika pandud juba JS poolt?

            if ($this->resizable && isset($this->col_width[$current_field - 1]))
                $this->content .= " style=\"width: ". $this->col_width[$current_field - 1]. "px\"";
			elseif (isset($field["width"]) && $field["width"]) // või on tabelikirjelduses paika pandud veergude laiused?
				$this->content .= " style=\"width: ". $field["width"]. "\"";

			// prindi veeru nimi

            $this->content .= "data-field=\"". $field["table"]. ".". $field["field"]. "\">". $field["title"];

            if (!$no_order)
                $this->content .= "<i class=\"sort_icon". $active. " fa fa-". $this->order_icon. "-". ($this->order == $field["table"]. ".". $field["field"] ? $way : "down"). "\"></i>";

			// väljaotsing

			if (isset($field["field_search"]) && $field["field_search"]) {
				$this->content .= "<span class=\"field_search\">";

				$this->content .= "<span id=\"". P_PREFIX. $this->target. "_". $field["field"]. "_search\" ";
				$this->content .= "class=\"field_search_btn small_btn\" title=\"". @$this->l->txt_field_search. "\">";
				$this->content .= "<i class=\"fa fa-search\"></i></span>";

				$this->content .= "<input type=\"text\" id=\"". P_PREFIX. $this->target. "_". $field["field"]. "_searchbox\" ";

				if (isset($field["placeholder"]) && $field["placeholder"])
					$this->content .= "placeholder=\"". $field["placeholder"]. "\" ";

				//if (isset($this->field_search) && $this->field_search)
					//$this->content .= "value=\"". $this->field_search("value"). "\" ";

				$this->content .= "class=\"field_search_input\"/>";

				$this->content .= "</span>";
			}

			$this->content .= "</th>";

            if ($this->resizable && $current_field < $fields)
                $this->content .= "<th class=\"resize no_order\"></th>"; // needed? <img src=\"/ptable/img/blank.gif\" width=1 height=1 border=0>
        }

        $this->content .= "</tr>";

        // kui vaja eraldada tabeliosa väljakirjeldustest

        if ($this->header_sep)
            $this->content .= "<tr class=\"no_hover\"><td class=\"border_top\" colspan=100></td></tr>";
    }

	function field_search($what) {
		if (isset($this->field_search) && $this->field_search) {
			list($f_field, $f_value) = explode(P_FSS, $this->field_search);

			if ($what == "value")
				return $f_value;
			else
				return $f_field;
		}
		else
			return false;
	}

    // otsingukast

    function searchbox() {
        $this->content .= "<span class=\"search\">";
        $this->content .= "<input type=\"text\" id=\"". P_PREFIX. $this->target. "_search\" class=\"search_field_input\" value=\"". $this->search. "\"> ";
        $this->content .= "<span id=\"". P_PREFIX. $this->target. "_commit_search\" class=\"search_btn small_btn\" title=\"". @$this->l->txt_search. "\"><i class=\"fa fa-search\"></i></span>";
        $this->content .= "</span>";
    }

    // valikukast

    function prefbox() {
        $this->content .= "<div id=\"". P_PREFIX. $this->target. "_prefbox\" class=\"prefbox\">";
        $this->content .= $this->awesome_eee(@$this->l->txt_pref). "<br/><br/>";
        $this->content .= $this->print_pref(@$this->l->txt_pagesize, $this->page_sizes, "dropdown", $this->page_size, "pagesize");
        $this->content .= $this->print_pref(@$this->l->txt_autoupdate, $this->autoupdate, "autoupdate_check", $this->autoupdate, "autoupdate");
        //$this->content .= "<br/><br/>";
        //$this->content .= "<span class=\"big_btn\">". @$this->l->txt_save. "</span>";
        //$this->content .= "<span class=\"big_btn\">". @$this->l->txt_close. "</span>";
        $this->content .= "</div>";

		$this->small_btn(P_PREFIX. $this->target, "pref_btn", "cog", "cog", @$this->l->txt_pref_btn);
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
        // kas autoupdate on aktiivne

        $id = P_PREFIX. $this->target;

        $pr  = "<div style=\"float: left\">";
        $pr .= "<i id=\"". $id. "_autoupdate_off\" data-table=\"". $this->target. "\" class=\"autoupdate_check ". ($current_val ? "hide " : ""). "off fa fa-square-o\"></i>";
        $pr .= "<i id=\"". $id. "_autoupdate_on\" data-table=\"". $this->target. "\" class=\"autoupdate_check ". ($current_val ? "" : "hide "). "fa fa-check-square-o\"></i>";
        $pr .= "<input type=\"hidden\" id=\"". $id. "_autoupdate_value\" class=\"". $id. "_value\" value=\"". $current_val. "\">";
        $pr .= "</div>";

        // autoupdate valikud

        $pr .= "<div style=\"float: right\">";
        $pr .= $this->form_dropdown($this->autoupdates, $current_val, "autoupdate_select");
        $pr .= "</div>";

        return $pr;
    }

    // navigatsioon

    function navigation($type) {
        if ($type == "footer" && $this->footer_sep)
            $this->content .= "<tr class=\"no_hover\"><td colspan=100 class=\"border_btm\"></td></tr>";

        if ($this->nav_pre && $this->nav_post) {
            $this->content .= $this->nav_pre. "nav_btm". $this->nav_post;

            return true;
        }

        $nav_page = 0;
        $from = ($this->page - 1) * $this->page_size + 1;
        $to = $from + $this->page_size - 1;

        // et viimane marker poleks suurem kui kirjete arv

        if ($to > $this->records)
            $to = $this->records;

        $this->nav_pre = "<tr class=\"no_hover\"><td colspan=100 class=\"";
        $this->nav_post = "\"><span class=\"records_found\">". @$this->l->txt_found. ": ". $this->records;
        $this->nav_post.= ($this->records && $this->page_size != P_ALL ? " (". $from. "-". $to. ")" : "");
        $this->nav_post.= "</span>";

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

            $this->nav_post.= "<span class=\"navigation\">";

            if ($this->page < 2)
                $this->add_nav_btn(1, $this->awesome_eee($this->nav_prev), true);
            else
                $this->add_nav_btn($this->page - 1, $this->awesome_eee($this->nav_prev));

            // algusleht

            $this->add_nav_btn(1, 1);

            // kas on vaja printida eraldaja (TODO: hetkel toimib korralikult kui nav_length = 5)

            if ($this->page >= $this->nav_length && $this->pages > $x)
                $this->nav_post.= "<span class=\"sep\"></span>";

            // prindi vahepealsed nupud

            while ($a < $x && $a < $this->pages) {
                if (($this->pages > $x && $this->page < $this->nav_length) || $this->pages <= $x)		// kui leht on vasakul pool tsentrit
                    $page = $a;
                elseif ($this->page > ($this->pages - $this->nav_length + 2))							// kui leht on paremalpool tsentrit
                    $page = $this->pages - $this->nav_length + $a - 2;
                else																					// kui leht on tsentris
                    $page = $this->page + $a - $this->nav_length + 1;

                $this->add_nav_btn($page, $page);

                $a++;
            }

            // kas on vaja printida eraldaja

            if ($this->page < ($this->pages - $x2) && $this->pages > $x)
                $this->nav_post.= "<span class=\"sep\"></span>";

            // prindi viimase lehe nupp

            if ($this->pages > 1)
                $this->add_nav_btn($this->pages, $this->pages);

            if ($this->page >= $this->pages)
                $this->add_nav_btn($this->pages, $this->awesome_eee($this->nav_next), true);
            else
                $this->add_nav_btn($this->page + 1, $this->awesome_eee($this->nav_next));

            $this->nav_post.= "</span>";
        }

        $this->nav_post.= "</td></tr>";

        $this->content .= $this->nav_pre. "nav_". ($type == "header" ? "top" : "btm"). $this->nav_post;
    }

    function add_nav_btn($page, $title, $denied = false) {
        $this->nav_post.= "<span class=\"nav". ($denied ? " denied" : ""). ($this->page == $page && !$denied ? " selected" : ""). "\" ";
        $this->nav_post.= "data-page=\"". $page. "\">". $title. "</span>";
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
