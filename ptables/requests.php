<?php

/* tabeli üldised omadused																					[variandid] :vaikeväärtus */

$this->table		= "request";			// põhitabeli nimi baasis										[<table>]
//$this->where		= "";		// where tingimused vabas vormis
//$this->values		= [];		// prepared pärinule omistatavad väärtused
$this->order		= "request.subject";		// esmaselt on tabel sorditud selle välja järgi (TODO: per person/tabel meelde jätta) ["<field>"]
$this->way			= "desc";			// mis suunas järjestatakse tulemused

$this->fields	= [
	[ "field"	=> "id",		"title" => $l->txt_request, "class" => "ohoo" ],
	[ "field"	=> "requester",	"title" => $l->txt_requester, "align" => "right", "searchable" => true ],
	[ "field"	=> "subject",	"title" => $l->txt_subject, "align" => "left", "field_search" => false, "extend" => "break_long", "searchable" => true, "search_left" => false, "search_right" => false ],
	[ "field"	=> "regdate",	"title" => $l->txt_regdate, "align" => "center", "extend" => "convert_date" ]
	//[ "field"	=> "hybrid",	"title" => $l->txt_joined, "align" => "center", "field_search" => false, "joined" => true ]
];

//$this->joins		= [
//		[ "table"	=> "task", "method" => "left join", "on" => "parent_id = request.id" ], 
//		[ "field"	=> "task.owner_id", "alias" => "hybrid" ]
//];

$this->triggers		= [
		"ROW"		=> [ "title" => "kena [reguester]", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
		"id"		=> [ "title" => "[requester][regdate]", "link" => "http://[heh]www.ttu.ee/#[amet][perenimi][midagi]", "external" => true ]
];

$this->title		= $this->data["user"];	// tabeli pealkiri												[<title>]
$this->title_icon	= "odnoklassniki";		// tabeli pealkirjast vasakul olev ikoon						[<font-awesome ikooni nimetus>]
$this->order_icon	= "chevron";		// mis tüüpi ikoone kasutatakse otsingutulemuste järjestamiseks ["chevron", "sort", "angle-double"] :"chevron"
$this->page_sizes	= [ 10 => "10 ". $l->txt_records, 25 => "25 ". $l->txt_records, 50 => "50 ". $l->txt_records, "*" => $l->txt_all_records ]; // valitavad lehepikkused
$this->page_size	= 10;				// esmane lehepikkus (TODO: milline on varasemalt valitud)
$this->refresh		= 30;				// mitme sekundi pärast uuendatakse antud tabelit automaatselt (esmane)				[0, 5..600] :0
$this->nav_length	= 5;				// mitu navigatsiooninuppu on kuvatud esimese ja viimase lehe nuppude vahel			[5] :default (TODO: teised väärtused panna korralikult toimima)
$this->nav_header	= false;			// kas header'i navigatsiooniriba on lubatud										[true, false] :false
$this->nav_footer	= true;				// kas header'i navigatsiooniriba on lubatud										[true, false] :true
$this->nav_prev		= $l->txt_prev;		// "eelmine leht"-nupu kirjeldus													["text"] :
$this->nav_next		= $l->txt_next;		// "järgmine leht"-nupu kirjeldus													["text"] :
$this->fields_descr	= true;				// kas väljakirjeldused on lubatud													[true, false] :true
$this->header_sep	= true;				// eralda väljakirjeldused tabeli sisuosast											[true, false] :false
$this->footer_sep	= false;			// eralda alumine nav tabeli sisuosast												[true, false] :false
$this->autosearch	= true;				// kas otsingukast käitub automaatsena (alates on kirjeldatud js: search_from = l)	[true, false] :false
$this->searchable	= true;				// kas otsing ja otsingukast on rakendatud tabelile									[true, false] :false
$this->prefs		= true;				// kas on lubatud kasutajal muuta tabeli seadeid									[true, false] :true
$this->store_prefs	= true;				// salvestab tabeli põhiandmed (välja laiused salvestatakse siiski)					[true, false] :true
$this->sizeable		= true;				// kas on lubatud muuta tabeli kirjete arvu ühel lehel								[true, false] :true
$this->download		= true;				// TODO: võimalda tabeli sisu allalaadimine .csv, .pdf või excel'ina				[true, false] :true
$this->smart_select	= true;				// TODO: võimaldab valida märkida tabeli ridasid ja veergusid sõltumatult			[true, false] :true
$this->fullscreen	= true;				// TODO: ava tabel täisekraanis														[true, false] :true

?>
