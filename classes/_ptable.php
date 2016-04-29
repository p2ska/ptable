<?php

// [ptable]; Andres Päsoke

define("P_ALLOWED",		"/[^\p{L}\p{N}\s\.@_-]/u");	// millised sümbolid on lubatud, sisendina
define("P_DOTS",		"/\.+/");
define("P_ALL",			"*");
define("P_ANY",			"%");
define("P_Q",			"?");
define("P_LN",			"\n");
define("P_SL",			"/");
define("P_DOT",			".");
define("P_VOID",		"");
define("P_NULL",    	"<null>");
define("P_EX",          "-");
define("P_FSS",			"___");
define("P_BR",      	"<br/>");
define("P_2BR",     	"<br/><br/>");
define("P_PREFIX",		"ptable_");
define("P_EXACT",		" = ");
define("P_LIKE",		" like ");
define("P_OR",			" || ");
define("P_SELECT",		" select ");
define("P_FROM",		" from ");
define("P_WHERE",		" where ");
define("P_ORDER",		" order by ");
define("P_LIMIT",		" limit ");
define("P_FIELD_L",		"[");
define("P_FIELD_R",		"]");
define("P_EXTERNAL_L",	"{%");
define("P_EXTERNAL_R",	"%}");
define("P_AWESOME_L",	"{{");
define("P_AWESOME_R",	"}}");

class PTABLE {
    // kõik parameetrid (nb! need default'id kirjutatakse üle tabeli kirjeldusfaili ja ka ptable.js poolt tulevate väärtustega üle)

    var
    $content, $db, $l, $lang, $mode, $target, $template, $url, $class, $data, $translations, $autoupdate, $refresh,
    $database, $host, $username, $password, $charset, $collation, $table, $query, $fields, $where, $values,
	$search, $triggers, $joins, $group, $order, $way, $limit, $records, $field_count, $field_search, $title,
    $style, $navigation, $nav_pre, $nav_post, $pages, $pagesize, $external_data, $external_pos, $col_width,
    $is, $subdata, $subquery, $subvalues, $subfields, $subcontent, $selected, $selection, $mobile,
    $debug			= false,        // debug reziim
    $header			= true,			// kas kuvatakse tabeli päist üldse
    $header_sep		= false,		// tabeli ülemine eraldusäär
    $footer_sep		= false,		// tabeli alumine eraldusäär
    $fields_descr	= true,			// väljade kirjeldused tabeli päises
    $search_ph      = false,        // otsingukasti placeholder
    $prefs			= true,			// tabeli seadeid saab muuta
    $store_prefs	= true,			// kas salvestatakse muudatused (sorteerimisväli, suund, uuendused, lehe pikkus)
    $download		= true,			// TODO: tabeli sisu allalaadimise võimaldamine
    $badge          = false,        // kuva badge
    $autosearch		= true,			// automaatne otsing
    $searchable		= true,			// kas kuvatakse otsingukasti
    $sizeable		= true,			// kas lastakse kasutajal muuta kirjete arvu ühel lehel
	$resizable		= true,   		// kas saab veergude laiust muuta
    $exportable     = true,         // kas saab exportide tabeli sisu
    $minimize		= false,		// kas saab tabelit minimiseerida
	$minimized		= false,		// kas tabel on algselt minimiseeritud
    $maximize		= false,		// TODO: kas saab tabelit maximiseerida
    $nav_header		= false,		// kas kuvatakse ülemist navigatsiooniriba
    $nav_footer		= true,			// kas kuvatakse alumist navigatsiooniriba
    $nav_length		= 5,			// navigeerimisnuppude arv
    $page			= 1,			// mitmendat lehekülge kuvatakse
    $page_size		= 10,			// mitu kirjet ühel lehel kuvatakse
    $column_align   = "center",     // kui ei ole väljakirjelduses määratud teisiti, siis see on default joondumine
    $column_width   = "5%",         // kui veerulaius pole muudetav ega väljakirjelduses paika pandud, siis see on default
    $order_icon		= "chevron",	// milliseid ikoone kasutatakse sorteerimisjärjekorra kuvamiseks (chevron, sort, angle-double)
    $nav_prev		= "{{angle-double-left}}",	// 'eelmine'-nupp
    $nav_next		= "{{angle-double-right}}",	// 'järgmine'-nupp
    $autoupdates	= [ 5 => "5s", 10 => "10s", 30 => "30s", 60 => "1m", 300 => "5m", 600 => "10m" ],	// "automaatsed uuendused"-valikukasti väärtused
    $page_sizes		= [ 10 => "10", 20 => "20", 50 => "50" ]; // "kirjete arv lehel"-valikukasti väärtused

    // initsialiseeri kõik js poolt määratud muutujad

    function ptable($init, $source = false, $lang = false) {
        // kas target on ikka olemas

		if (!isset($init["target"]))
            return false;

		// tabeli id

        $this->target = $this->safe($init["target"]);

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

		// subdata[] muutuja edastamiseks tabelikirjeldusele

        if (isset($init["subdata"]) && $init["subdata"])
            $this->subdata = explode(P_EX, $this->safe($init["subdata"]));

		// valikute edastamine tabelikirjelduse jaoks

		if (isset($init["selected"]) && $init["selected"])
            $this->selected = $this->safe($init["selected"]);

        // kirjuta klassi default'id tabelikirjelduse omadega üle

        if (!$this->init())
            return false;

        // mitu välja defineeritud on?

        $this->field_count = count($this->fields);

        // pane väljade default'id paika

        $this->field_defaults();

        // kirjuta default'id JS omadega üle (puhasta input)

		foreach ($init as $key => $val)
            $this->{ $key } = $this->safe($val);

        // kui on veergude laiused olemas, siis tee sellest massiiv

        if ($this->col_width)
            $this->col_width = explode(P_EX, $this->col_width);

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

        // kui on tegemist alamtabeliga

        if (isset($init["subdata"])) {
			if (isset($this->subquery) && $this->subquery && isset($this->subvalues) && $this->subvalues)
            	return $this->subtable();
			elseif (isset($this->subcontent) && $this->subcontent)
				return $this->subdata();
			else
				return false;
		}

        // hangi ja töötle andmeid

        if ($this->external_data)
            $this->prepare_external();
        else
            $this->fetch_data();

        // kas on vaja andmed eksportida csv-faili?

        if (isset($init["export"]))
            return $this->export_csv($init["export"]);

        // moodusta tabel

		$this->display();

        // kuvamiseks kasuta
        // echo $this->content;
    }

    // init

    function init() {
        // et tabelikirjelduse failid oleks veidi mugavam ja lühem keelestringe välja kutsuda

        $l = &$this->l;

        // juhul kui on kasutaja poolt lisatud "data-template", siis see override'b "id" template sihtmärgina

        if (isset($this->data["template"]) && $this->data["template"])
            $template = $this->data["template"];
        else
            $template = $this->target;

        // tabeli kirjelduse template

        $this->template = P_TABLES. P_SL. $template. ".php";

        // lae tabeli info

        if (file_exists($this->template)) {
            require_once($this->template);

            return true;
        }
        else {
            // väga halb, et tabeli kirjeldust ei leidnud

            return false;
        }
    }

    // pane mõned default'id paika, mis pole kasutaja poolt väljadele lisatud

    function field_defaults() {
        for ($a = 0; $a < $this->field_count; $a++) {
            // kui kirjelduses pole tabelit paika pandud, siis võta default'iks põhitabel

            if (!isset($this->fields[$a]["table"]))
                $this->fields[$a]["table"] = $this->table;

            // kui ei ole määratud joondumist, siis pane default

            if (!isset($this->fields[$a]["align"]))
                $this->fields[$a]["align"] = $this->column_align;

            // kui veergude laius pole muudetav ja pole ka kirjelduses laiust paika pandud, siis pane 10% laiuseks

            if (!$this->resizable && !isset($this->fields[$a]["width"]))
                $this->fields[$a]["width"] = $this->column_width;
        }

        // kui on ka alamtabel, siis säti selle defaulte ka

        if (isset($this->subquery) && $this->subquery && isset($this->subfields) && $this->subfields) {
            for ($a = 0; $a < count($this->subfields); $a++) {
                // veerulaius

                if (!isset($this->subfields[$a]["width"]))
                    $this->subfields[$a]["width"] = $this->column_width;

                // joondumine

                if (!isset($this->subfields[$a]["align"]))
                    $this->subfields[$a]["align"] = $this->column_align;
            }
        }
    }

    // hangi alamtabeli andmed

    function subtable() {
        $this->db->query($this->subquery, $this->subvalues);

		$this->content .= "<table class=\"subtable\">";

		if (!1) {
			$this->content .= "<tr>";

			foreach ($this->subfields as $subfield) {
				$this->content .= "<th class=\"no_order\"";

				if (isset($subfield["width"]) && $subfield["width"])
					$this->content .= " style=\"width: ". $subfield["width"]. "\"";

				$this->content .= ">". $subfield["title"]. "</th>";
			}

			$this->content .= "</tr>";
		}

        while ($obj = $this->db->get_obj())
            $this->print_row($obj, "sub");

		$this->content .= "</table>";
    }

	// hangi alamsisu

	function subdata() {
        if (method_exists($this, $this->subcontent)) {
			$this->content .= $this->{ $this->subcontent }($this->subdata);
        }
	}

    // hangi tabeli andmed

    function fetch_data() {
        $search = $order = $joined = false;

		// lisa tingimus 'where' kirjeldatule

        if ($this->where)
            $this->where = P_WHERE. $this->where;

        // kas on valitud tingimused (checkbox'id)

		if ($this->selection) {
			$selection = [];

            // kui ei ole ühtegi valitud, siis vali kõik (?)

			if (!$this->selected) {
				foreach ($this->selection as $key => $val)
					$this->selected[$key] = $val["checked"];
			}

            // kui on valikud olemas, siis lisa need tingimused

            if (is_array($this->selected)) {
                foreach ($this->selected as $key => $val)
                    if ($val) {
                        $selection["where"][] = $this->selection[$key]["where"];

                        // kui ei ole määratud kuidas valikuid ühendada, siis kasuta OR'i

                        if (isset($this->selection[$key]["method"]))
                            $selection["method"][] = " ". trim($this->selection[$key]["method"]). " ";
                        else
                            $selection["method"][] = " || ";

                        // lisa valikute väärtused otsingu põhi-väärtustele

                        if (isset($this->selection[$key]["values"]) && $this->selection[$key]["values"])
                            $this->values = array_merge($this->values, $this->selection[$key]["values"]);
                    }
            }

            // lisa põhi-tingimustele valiku-tingimused

			if (isset($selection["where"])) {
                $add_where = "";

                for ($a = 0; $a < count($selection["where"]); $a++) {
                    if ($a > 0)
                        $add_where .= $selection["method"][$a];

                    $add_where .= $selection["where"][$a];
                }

				if ($this->where)
					$this->where .= " && (". $add_where. ")";
				else
					$this->where = P_WHERE. "(". $add_where. ")";
			}
		}

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

        // lisa päringule sorteerimine ja limiit (õige lehekülg)

        $this->query .= ($this->order ? P_ORDER. $this->order. " ". $this->way : P_VOID). $this->limit;

        // kuva debug infot tabeli päises, ja dump'i päring välisesse faili

        if ($this->debug) {
			$debug_query = date("H:i:s"). " [ ". $this->query. " ]". P_BR. "< ". ($this->values ? implode(", ", $this->values) : ""). " >". P_2BR;

            $this->content .= $debug_query;

			p_log("ptable_debug.txt", $debug_query);
		}

        if ($this->records) {
            // teosta päring

            $this->db->query($this->query, $this->values);

            // mingi sql päringu viga

            if ($this->db->error && $this->debug)
				$this->content .= $this->db->error_msg. P_2BR;
        }
    }

	// ehita päring tabelikirjeldusest

    function build_query() {
        $join_tables = $group_by = false;
        $fields = $joins = [];

		// käi väljad läbi

        foreach ($this->fields as $field) {
            if (isset($field["alias"]) && $field["alias"])
                $alias = " as ". $field["alias"];
            else
                $alias = P_VOID;

			// kui tabel on eraldi märgitud (liidetud tabel), siis kasuta seda; vastasel juhul arvesta, et tegu on põhitabeli väljaga (default)

            if (!isset($field["fakefield"]) || !$field["fakefield"]) {
                if (isset($field["table"]) && $field["table"])
                    $fields[] = $field["table"]. ".". $field["field"]. $alias;
                else
                    $fields[] = $this->table. ".". $field["field"]. $alias;
            }
        }

		// kas on joine?

		if ($this->joins) {
        	foreach ($this->joins as $join)
            	$joins[] = $join["method"]. " ". $join["table"]. " on ". $join["on"];

        	if (count($joins))
            	$join_tables = " ". implode(", ", $joins);
		}

        // kui on vaja grupeerida

        if ($this->group)
            $group_by = " group by ". $this->group;

        return P_SELECT. implode(", ", $fields). P_FROM. $this->table. $join_tables. $this->where. $group_by;
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
                        elseif (stripos($field_value, $this->search) !== false) { // täisotsing
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

        // kas on vaja vastavalt kasutaja klikkimisele checkbox'idel tulemit kitsendada

		if ($this->selection) {
			if (!$this->selected) {
				foreach ($this->selection as $key => $val)
					$this->selected[$key] = $val["checked"];
			}

            $records = count($this->external_data);

            if (is_array($this->selected)) {
                for ($a = 0; $a < $records; $a++) {
                    foreach ($this->selected as $selected_key => $selected_val) {
                        if (isset($this->external_data[$a]) && isset($this->selection[$selected_key]["add"]))
                            foreach ($this->selection[$selected_key]["add"] as $where_key => $where_val) {
                                if (!$selected_val && $this->external_data[$a]->{ $where_key } == $where_val)
                                    unset($this->external_data[$a]);
								elseif ($selected_val && $this->external_data[$a]->{ $where_key } != $where_val)
									unset($this->external_data[$a]);
							}

                        if (isset($this->external_data[$a]) && isset($this->selection[$selected_key]["remove"]))
                            foreach ($this->selection[$selected_key]["remove"] as $where_key => $where_val) {
                                if ($selected_val && $this->external_data[$a]->{ $where_key } == $where_val)
                                    unset($this->external_data[$a]);
								//elseif (!$selected_val && $this->external_data[$a]->{ $where_key } != $where_val)
									//unset($this->external_data[$a]);
							}
					}
                }
            }
		}

        reset($this->external_data);

        // mitu kirjet kokku on? arvuta lehekülgede arv

        $this->records = count($this->external_data);

        // kui midagi alles jäi, siis sorteeri kuidas vaja

        if ($this->records)
            usort($this->external_data, array($this, "sort_em"));

        if ($this->page_size == P_ALL)
            $this->pages = 1;
        else { // kui on rohkem kui üks lehekülg (potensiaalselt), siis lõika massiivist õige tükk
            $this->pages = intval(($this->records - 1) / intval($this->page_size)) + 1;

            // kui eelmisest otsingust on lehenumber jäänud suurem, siis vii kasutaja esimesele lehele

            if ($this->page > $this->pages)
                $this->page = 1;

            $this->external_data = array_slice($this->external_data, ($this->page - 1) * $this->page_size, $this->page_size);
        }
    }

    // massiivi sorteerimine vastavalt väljale ja suunale

    function sort_em($a, $b) {
        if (!isset($a->{ $this->order }) || !isset($b->{ $this->order }))
            return 0;

        $a = $a->{ $this->order };
        $b = $b->{ $this->order };

        if ($a === $b)
            return 0;

        if (!$this->way || $this->way == "asc")
            $result = strcasecmp($a, $b);
        else
            $result = strcasecmp($b, $a);

        if ($result < 0)
            return -1;
        else
            return 1;
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
                        $this->content .= $this->awesome($this->title_icon). " ";

                    $this->content .= "<u>". $this->title. "</u></div>";
                }

				if (is_array($this->selection)) {
					$this->content .= "<div class=\"selection\">";

					foreach ($this->selection as $key => $val) {
						if (isset($this->selected[$key]) && $this->selected[$key])
							$checked = true;
						else
							$checked = false;

						$this->checkbox($key, $val, $checked);
					}

					$this->content .= "</div>";
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

        // kas navigeerimine on lubatud?

        if ($this->nav_header || $this->nav_footer)
            $this->navigation = true;
        else
            $this->navigation = false;

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
        $this->content .= "data-badge=\"". ($this->badge ? "true" : "false"). "\" ";
        $this->content .= "data-store=\"". ($this->store_prefs ? "true" : "false"). "\">";
        $this->content .= "<thead>";

        // kui on ülemine navigeerimine lubatud

        if ($this->nav_header)
            $this->navigation("header");

        // väljade kirjeldused

		if (!$this->mobile && $this->fields_descr)
            $this->fields_descr();

        $this->content .= "</thead><tbody>";

        // tulemused

        if ($this->records) {
            if ($this->db) {
				foreach ($this->db->get_all() as $obj)
                   	$this->print_row($obj);
            }
            else {
                foreach ($this->external_data as $obj)
                    $this->print_row($obj);
            }
        }
        else {
            $this->content .= "<tr><td colspan=100>". @$this->l->txt_notfound. "</td></tr>";
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

	function checkbox($cid, $data, $checked) {
		$id = $this->target. "_checkbox_". $cid;
		$disabled = P_VOID;

		if (isset($data["disabled"]) && $data["disabled"])
			$disabled = "disabled ";

		$this->content .= "<span id=\"". $id. "\" class=\"check". $disabled. "\" data-table=\"". $this->target. "\" data-cid=\"". $cid. "\">";
		$this->content .= "<i class=\"check_off ". $disabled. "fa fa-square-o\"". ($checked ? " style='display:none'" : ""). "></i>";
        $this->content .= "<i class=\"check_on ". $disabled. "fa fa-check-square-o\"". ($checked ? "" : " style='display:none'"). "></i>";
		$this->content .= " ". $data["title"]. " ";
        $this->content .= "<input type=\"hidden\" id=\"". $id. "_val\" value=\"". $checked. "\">";
		$this->content .= "</span>";
	}

	function small_btn($id, $class, $icon, $icon2, $title) {
		$this->content .= "<span data-parent=\"". $id. "\" class=\"". $class. " small_btn\" title=\"". $title. "\">";
		$this->content .= "<i class=\"fa fa-". $icon. "\"></i>";
		$this->content .= "<i class=\"fa fa-". $icon2. "\" style=\"display: none\"></i>";
		$this->content .= "</span>";
	}

    // väljasta väärtused baasist

    function print_row($obj, $type = "main") {
        // kas kogu real on trigger küljes?

        $row["field"] = "ROW";
        $this->output($row, $obj, $type);

        // nüüd vaata, kas väljale on defineeritud trigger või mitte, ja väljasta väärtus

        $count = 0;
		$subcontent = false;

        // kui on põhiväli, siis kuva vajadusel alamtabeli trigger

        if ($type == "main") {
            foreach ($this->fields as $field) {
                if ((isset($field["subtable"]) && $field["subtable"]) || (isset($field["subdata"]) && $field["subdata"]))
                    $subcontent = true;

                $this->output($field, $obj, $type, $count++);
            }

            // kui on alamtabel, siis kuva lisarida selle jaoks

            if ($subcontent) {
                $this->content .= "<tr><td class=\"subrow\" id=\"subrow_". $obj->subrow_id. "\" colspan=100></td></tr>";
                $this->content .= "<tr><td class=\"hide\" colspan=100></td></tr>";
            }
        }
        else {
            foreach ($this->subfields as $subfield)
                $this->output($subfield, $obj, $type, $count++);
        }

        $this->content .= "</tr>";

		if ($this->mobile)
			$this->content .= "<tr class=\"mobile_sep\"><td colspan=100></td></tr>";
    }

	// hangi väline info tabeli jaoks

    function fetch_external(&$field, $data) {
        foreach ($field["fetch"] as $method => $value)
            if (method_exists($this, $method)) {
                // kas on ka markuppi?

                $values = $this->replace_markup($value, $field, $data);

                $field["external_". $method] = $this->{ $method }($values);
            }
    }

    // muutujate töötlemine

    function extend($field, &$value) {
        if (!is_array($field["extend"]))
            $field["extend"] = [ $field["extend"] ];

        foreach ($field["extend"] as $extension)
            if (method_exists($this, $extension))
                $value = $this->{ $extension }($value);
    }

    // prindi

    function output($field, &$data, $type = "main", $pos = 0) {
        $trigger = $link = $title = $class = $style = $colspan = $subcount = P_VOID;
        $styles = array();

        // protsessi muutujaid

        if (isset($field["process"]) && method_exists($this, $field["process"]))
            $this->{ $field["process"] }($data);

        if (isset($field["hidden"]) && $this->hide_column($field["hidden"]))
            return true;

        if ($type == "main" && isset($this->triggers[$field["field"]]))
            $trigger = $this->triggers[$field["field"]];
        elseif ($type != "main" && isset($this->subtriggers[$field["field"]]))
            $trigger = $this->subtriggers[$field["field"]];

        if ($trigger) {
            $class = "trigger";

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
                $title = $this->replace_markup($trigger["title"], $field, $data);

            if (isset($trigger["link"])) {
                $link = $this->replace_markup($trigger["link"], $field, $data);

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
                    $ext_data = $this->replace_markup($ext_data, $field, $data);

                    $this->content .= " data-". $ext_field. "=\"". $ext_data. "\"";
                }

                if ($title)
                    $this->content .= " title=\"". $title. "\"";
            }
			elseif (isset($trigger["info"]) && $trigger["info"]) {
				$this->content .= " title=\"". $trigger["title"]. "\" data-info=\"true\"";
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
				$this->content .= ">". $this->format_value($field, $data). "</td>";

                if ($type == "main")
                    $last = $this->field_count - 1;
                else
                    $last = count($this->subfields) - 1;

                if ($this->resizable && $pos < $last)
                    $this->content .= "<td class=\"resize\"></td>";
            }
        }
        else {
            if ($field["field"] == "ROW")
                $this->content .= "<tr>";
            else {
				if ($this->mobile)
					$this->content .= $this->field_descr($field);

                $this->content .= "<td ";

                if (isset($field["colspan"]) && $field["colspan"])
                    $colspan = " colspan=". $field["colspan"];

                if (isset($field["class"]) && $field["class"])
                    $class = " class=\"". $field["class"]. "\"";

				if (!$this->mobile && isset($field["align"]) && $field["align"])
                    $styles[] = "text-align: ". $field["align"];

                if (isset($field["nowrap"]) && $field["nowrap"])
                    $styles[] = "white-space: ". ($field["nowrap"] ? "nowrap" : "none");

                if (count($styles))
                    $style = " style=\"". implode("; ", $styles). "\"";

                $this->content .= $colspan. $class. $style. ">";

                if (isset($field["subtable"]) && $field["subtable"]) {
					$data->subrow_id = $this->replace_markup($field["subtable"], $field, $data);

					if (isset($this->subquery) && $this->subquery) {
						$this->db->query($this->subquery, array($data->subrow_id));

						if ($this->db->rows) {
                        	$this->content .= "<span class=\"subdata\" data-values=\"". $data->subrow_id. "\">";
                        	$this->content .= "<span class=\"sub_closed\">". $this->awesome("{{plus-square}}"). "</span>";
                        	$this->content .= "<span class=\"sub_opened\">". $this->awesome("{{minus-square}}"). "</span>";
                        	$this->content .= "</span> ";
                    	}
                    	else {
							$this->content .= "<span class=\"subdata\">". $this->awesome("{{square-o}}"). "</span> ";
                    	}
					}
                }
                elseif (isset($field["subdata"]) && $field["subdata"]) {
					$data->subrow_id = $this->replace_markup($field["subdata"], $field, $data);

					if (isset($this->subcontent) && $this->subcontent) {
                       	$this->content .= "<span class=\"subdata\" data-values=\"". $data->subrow_id. "\">";
                       	$this->content .= "<span class=\"sub_closed\">". $this->awesome("{{plus-square}}"). "</span>";
                       	$this->content .= "<span class=\"sub_opened\">". $this->awesome("{{minus-square}}"). "</span>";
                       	$this->content .= "</span> ";
					}
                }

                $this->content .= $this->format_value($field, $data);

                if (isset($field["info"]) && $field["info"]) {
                    $info = $this->replace_markup($field["info"], $field, $data);

                    if ($info) {
                        $this->content .= "<span class=\"infobox\">". $this->awesome("{{info-circle}}", "#870042");
                        $this->content .= "<div class=\"bubble\">";
                        $this->content .= "<span class=\"close_btn\" title=\"". @$this->l->txt_close. "\">". $this->awesome("{{close}}"). "</span>";
                        $this->content .= $info;
                        $this->content .= "</div></span>";
                    }
				}

                $this->content .= "</td>";

				if ($this->mobile)
					$this->content .= "</tr>";

                if ($type == "main")
                    $last = $this->field_count - 1;
                else
                    $last = count($this->subfields) - 1;

                if ($this->resizable && $pos < $last)
                    $this->content .= "<td class=\"resize\"></td>";
            }
        }
    }

    // ekspordi andmed

    function export_csv($range) {
        echo "helpdesk-test11";
    }

    // tee rida väärtuse muutmisi

	function format_value(&$field, $data) {
		// kas väljatüüp on alias või tavaline väli

		if (isset($field["alias"]) && $field["alias"])
			$field_type = $field["alias"];
        else
            $field_type = $field["field"];

        // kas tuleks hankida tabeli jaoks välist infot

		if (isset($field["fetch"]))
			$this->fetch_external($field, $data);

        // kas on väljale laiendus?

        if (isset($field["extend"]))
			$this->extend($field, $data->{ $field_type });

        if (isset($data->{ $field_type }))
            $value = $data->{ $field_type };
        else
            $value = P_VOID;

        // kas on vaja kuvada hoopis vastavat tõlget?

		if (isset($field["translate"]) && $field["translate"]) {
            $translation = $this->replace_markup($field["translate"], $field, $data);

			if (isset($this->l->{ $translation }))
				$value = $this->l->{ $translation };
		}
        elseif (isset($field["print"]) && $field["print"]) {
            // prindi vastavalt kirjeldusele

            $value = $this->replace_markup($field["print"], $field, $data);
        }

		// otsingusõna värvimine

		$value = $this->highlight($value, $field);

        // kas on vaja hoopis kuvada selle väärtusega seotud kirjeldust?

        $value = $this->is_value($value, $field, $data);

        return $value;
	}

	// värvi otsingusõnad tabelis

	function highlight($value, $field) {
		if ($this->search) {
			//$value = preg_replace("#". preg_quote($this->search). "#i", "<font class=\"highlight\">\\0</font>", $value);

            $value = preg_replace("#(?!<.*?)(". preg_quote($this->search). ")(?![^<>]*?>)#i", "<font class=\"highlight\">\\1</font>", $value);
        }
		elseif ($this->field_search) {
			list($f_field, $f_value) = explode(P_FSS, $this->field_search);

			// väljaotsingu puhul värvi ainult selle veeru otsingusõnasid

			if ($f_field == $field["table"]. ".". $field["field"])
				$value = preg_replace("#(?!<.*?)(". preg_quote($f_value). ")(?![^<>]*?>)#i", "<font class=\"highlight\">\\0</font>", $value);
		}

		return $value;
	}

    // TODO: veeru peitmine mingitel tingimustel

    function hide_column($hide) {
        // kui on väljakirjelduses sunnitud peitmine

        if ($hide === true)
            return true;

        $operator = false;
        $operators = array("<", ">", "=", "<=", ">=");

        // kas mõni operaator on olemas?

        foreach ($operators as $op)
            if (strpos($hide, $op)) {
                $operator = $op;

                break;
            }

        // kui mitte, siis peida väli

        if ($operator === false)
            return true;

        // kas on arusaadav millega võrreldakse üldse

        list($key, $val) = explode($operator, $hide);

        $key = trim(strtolower($key));
        $val = trim($val);

        // hetkel ainult tabeli üldine laius 'width' on aksepteeritav parameeter

        if ($key != "width")
            return true;

        // kontrolli, kas tingimus vastab tõele

        //var_dump($this->col_width);
    }

	// väljakirjeldus (mobiilivaate jaoks)

    function field_descr($field) {
		// kas on vaja välja peita?

		if (isset($field["hidden"]) && $this->hide_column($field["hidden"]))
			continue;

        $no_order = false;

		// kui tabelit pole kirjeldatud (väline massiiv), siis sorteerimiseks ainult paljas väljanimi

        if (isset($field["table"]) && $field["table"])
            $field_name = $field["table"]. ".". $field["field"];
        else
            $field_name = $field["field"];

        // lisa aktiivsele sorteerimisväljale värvi

		if ($this->order == $field_name)
            $active = " active";
        else
            $active = P_VOID;

        // mis pidi siis sorteeritakse, kuva vastava ikoon

        if ($this->way == "asc")
            $way = "up";
        else
            $way = "down";

        // kui väljale pole lisatud kirjelduses pealkirja, siis pane selleks välja enda nimi

        if (!isset($field["title"]))
            $field["title"] = $field["field"];

        // kui sorteerimine on keelatud selle välja järgi

        if ((isset($field["sortable"]) && !$field["sortable"]) || (isset($field["fakefield"]) && $field["fakefield"]))
            $no_order = "no_";

        $this->content .= "<td class=\"". $no_order. "order". $active. " mobile\" data-field=\"". $field_name. "\">";

		// prindi veeru kirjeldus

        $this->content .= $this->awesome($field["title"]);

        // kui on sorteeritav

        if (!$no_order)
            $this->content .= "<i class=\"sort_icon". $active. " fa fa-". $this->order_icon. "-". ($this->order == $field_name ? $way : "down"). "\"></i>";

		$this->content .= "</td>";
    }

	// tabeli väljade kirjeldused

    function fields_descr() {
        $fields = count($this->fields);
        $current_field = 0;

        $this->content .= "<tr>";

        foreach ($this->fields as $field) {
            // kas on vaja välja peita?

			if (isset($field["hidden"]) && $this->hide_column($field["hidden"]))
				continue;

            $current_field++;
            $no_order = false;

            // kui tabelit pole kirjeldatud (väline massiiv), siis sorteerimiseks ainult paljas väljanimi

            if (isset($field["table"]) && $field["table"])
                $field_name = $field["table"]. ".". $field["field"];
            else
                $field_name = $field["field"];

            // lisa aktiivsele sorteerimisväljale värvi

            if ($this->order == $field_name)
                $active = " active";
            else
                $active = P_VOID;

            // mis pidi siis sorteeritakse, kuva vastava ikoon

            if ($this->way == "asc")
                $way = "up";
            else
                $way = "down";

            // kui väljale pole lisatud kirjelduses pealkirja, siis pane selleks välja enda nimi

            if (!isset($field["title"]))
                $field["title"] = $field["field"];

            // kui sorteerimine on keelatud selle välja järgi

            if ((isset($field["sortable"]) && !$field["sortable"]) || (isset($field["fakefield"]) && $field["fakefield"]))
                $no_order = "no_";

            $this->content .= "<th class=\"". $no_order. "order". $active. " \" ";

			// kui veerulaiused pannakse paika JS poolt

			if ($this->resizable && isset($this->col_width[$current_field - 1]))
                $this->content .= " style=\"width: ". $this->col_width[$current_field - 1]. "px\"";
			elseif (isset($field["width"]) && $field["width"]) // või on tabelikirjelduses paika pandud veergude laiused?
				$this->content .= " style=\"width: ". $field["width"]. "\"";

			// prindi veeru kirjeldus

            $this->content .= "data-field=\"". $field_name. "\">". $this->awesome($field["title"]);

            // kui on sorteeritav

            if (!$no_order)
                $this->content .= "<i class=\"sort_icon". $active. " fa fa-". $this->order_icon. "-". ($this->order == $field_name ? $way : "down"). "\"></i>";

			// väljaotsing

			if (isset($field["field_search"]) && $field["field_search"]) {
				$this->content .= "<span class=\"field_search\">";

				$this->content .= "<span id=\"". P_PREFIX. $this->target. "_". $field["field"]. "_search\" ";
				$this->content .= "class=\"field_search_btn small_btn\" title=\"". @$this->l->txt_field_search. "\">";
				$this->content .= "<i class=\"fa fa-search\"></i></span>";

				$this->content .= "<input type=\"text\" id=\"". P_PREFIX. $this->target. "_". $field["field"]. "_searchbox\" ";

				if (isset($field["placeholder"]) && $field["placeholder"])
					$this->content .= "placeholder=\"". $field["placeholder"]. "\" ";

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

	// veeruotsing

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
        $this->content .= "<input type=\"text\" id=\"". P_PREFIX. $this->target. "_search\" class=\"search_field_input\" placeholder=\"". $this->search_ph. "\" value=\"". $this->search. "\"> ";
        $this->content .= "<span id=\"". P_PREFIX. $this->target. "_commit_search\" class=\"search_btn small_btn\" title=\"". @$this->l->txt_search. "\"><i class=\"fa fa-search\"></i></span>";
        $this->content .= "</span>";
    }

    // valikukast

    function prefbox() {
        $this->content .= "<div id=\"". P_PREFIX. $this->target. "_prefbox\" class=\"prefbox\">";
        $this->content .= "<span class=\"close_btn\" title=\"". @$this->l->txt_close. "\">". $this->awesome("{{close}}"). "</span>";
        $this->content .= $this->awesome(@$this->l->txt_pref). "<br/><br/>";

        if ($this->page_sizes)
            $this->content .= $this->print_pref(@$this->l->txt_pagesize, $this->page_sizes, "dropdown", $this->page_size, "pagesize");

        if ($this->autoupdates)
            $this->content .= $this->print_pref(@$this->l->txt_autoupdate, $this->autoupdate, "autoupdate_check", $this->autoupdate, "autoupdate");

        if ($this->exportable)
            $this->content .= $this->print_pref(@$this->l->txt_export, $this->exportable, "export_links", $this->exportable, "export");

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

    // keera tekstis {{ikoon}} font-awesome ikooniks

    function awesome($str, $color = P_VOID, $pre = "fa fa-", $post = P_VOID) {
        if ($color)
            $color = " style=\"color: ". $color. "\"";

        $str = str_replace(P_AWESOME_L, "<i class=\"". $pre, $str);
        $str = str_replace(P_AWESOME_R, $post. "\"". $color. "></i>", $str);

        return $str;
    }

    // vormi dropdown

    function form_dropdown($values, $current_val, $element) {
        $pr = "<select id=\"". P_PREFIX. $this->target. "_". $element. "\" data-table=\"". $this->target. "\" class=\"". $element. "\"";
        $pr.= ($current_val ? "" : " disabled border"). ">";

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
        $pr .= "<i id=\"". $id. "_autoupdate_off\" data-table=\"". $this->target. "\" class=\"autoupdate_check off fa fa-square-o\"". ($current_val ? " style='display:none'" : ""). "></i>";
        $pr .= "<i id=\"". $id. "_autoupdate_on\" data-table=\"". $this->target. "\" class=\"autoupdate_check fa fa-check-square-o\"". ($current_val ? "" : " style='display:none'"). "></i>";
        $pr .= "<input type=\"hidden\" id=\"". $id. "_autoupdate_value\" class=\"". $id. "_value\" value=\"". $current_val. "\">";
        $pr .= "</div>";

        // autoupdate valikud

        $pr .= "<div style=\"float: right\">";
        $pr .= $this->form_dropdown($this->autoupdates, $current_val, "autoupdate_select");
        $pr .= "</div>";

        return $pr;
    }

    // exportimise lingid

    function form_export_links($values, $current_val, $element) {
        // kas autoupdate on aktiivne

        $id = P_PREFIX. $this->target;

        $pr  = "<div style=\"float: left\">";
        $pr .= "<button class=\"export\" data-range=\"current_page\">". $this->l->txt_current_page. "</button>";
        $pr .= "<button class=\"export\" data-range=\"all_pages\">". $this->l->txt_all_pages. "</button>";
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
                $this->add_nav_btn(1, $this->awesome($this->nav_prev), true);
            else
                $this->add_nav_btn($this->page - 1, $this->awesome($this->nav_prev));

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
                $this->add_nav_btn($this->pages, $this->awesome($this->nav_next), true);
            else
                $this->add_nav_btn($this->page + 1, $this->awesome($this->nav_next));

            $this->nav_post.= "</span>";
        }

        $this->nav_post.= "</td></tr>";

        $this->content .= $this->nav_pre. "nav_". ($type == "header" ? "top" : "btm"). $this->nav_post;
    }

    // navigatsiooninupp

    function add_nav_btn($page, $title, $denied = false) {
        $this->nav_post.= "<span class=\"nav". ($denied ? " denied" : ""). ($this->page == $page && !$denied ? " selected" : ""). "\" ";
        $this->nav_post.= "data-page=\"". $page. "\">". $title. "</span>";
    }

    // vaheta kirjeldatud väljade kirjeldused nende väärtustega

    function replace_markup($value, $field, $data) {
        $values = [];

		foreach (explode(P_FIELD_L, $value) as $markup) {
            $ex = explode(P_FIELD_R, $markup);

            if (!isset($ex[1]))
                $values[] = trim($ex[0]);
            elseif (isset($ex[0]) && $ex[0]) {
                if (isset($data->{ $ex[0] }))
                    $values[] = $this->is_value(trim($data->{ $ex[0] }), $field);

                if (isset($ex[1]) && $ex[1])
                    $values[] = trim($ex[1]);
            }
        }

		$output = implode(P_VOID, $values);

		// kas on väliseid andmeid?

		$values = [];

        foreach (explode(P_EXTERNAL_L, $output) as $markup) {
            $ex = explode(P_EXTERNAL_R, $markup);

            if (!isset($ex[1]))
                $values[] = trim($ex[0]);
            elseif (isset($ex[0]) && $ex[0]) {
                if (isset($field["external_". $ex[0]]))
                    $values[] = $this->is_value(trim($field["external_". $ex[0]]), $field);

                if (isset($ex[1]) && $ex[1])
                    $values[] = trim($ex[1]);
            }
        }

		return implode(P_VOID, $values);
    }

    // kontrolli, kas on kirjeldatud kuidas mõnda väärtust printida

    function is_value($value, $field) {
        // kas on tabeli üldises kirjeldused olemas reegel selle väärtuse kohta

        if ($this->is && $value == P_VOID && isset($this->is[P_NULL]))
            $value = P_NULL;

        if ($this->is && isset($this->is[$value]))
            $value = $this->is[$value];

        // kas on väljakirjelduses reegel selle väärtuse kohta

        if ($value == P_VOID && isset($field["is"][P_NULL]))
            $value = P_NULL;

		// kas on font-awesome kirjeldusi?

		$value = $this->awesome($value);

        if (isset($field["is"]) && isset($field["is"][$value]))
            return $field["is"][$value];
        else
            return $value;
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
