<?php

/* tabeli üldised omadused																					[variandid] :vaikeväärtus */

$this->title		= $this->data["user"];	// tabeli pealkiri												[<title>]
$this->title_icon	= "odnoklassniki";		// tabeli pealkirjast vasakul olev ikoon						[<font-awesome ikooni nimetus>]
$this->table		= "request";			// põhitabeli nimi baasis										[<table>]
//$this->style		= "ptable";				// mis stiili kasutatakse tabli kujunduses						[<class>]

/* tabeliväljade loetelu ja omadused (kõik peale "field" väärtuse on valikulised) */

// "field"			- väljanimetus tabelis (TODO: kohustuslikkusekontoll; kas selline väli tabelist leiti)	[<field>]
// "joined"			- tähendab, et antud väli on juurdeliidetud tabelist saadud (vajab 'joins'-kirjelduses vastavat välja ja seost - 'alias')
// "title"			- tabeli päises kuvatav väljakirjeldus (mõistlik panna tõlkestring)						[<title>] :<field>
// "class"			- välja stiil, override																	[<class_name>]
// "align"			- välja sisu paiknemine																	["left", "center", "right", "justify"] :"left"
// "nowrap"			- välja ei wrapita																		[true, false] :false
// "searchable"		- kui tehakse üldine otsing, siis lisatakse see väli otsingusse							[true, false] :false
// "search_left"	- otsingu puhul otsitakse ka vasakule (like "%<otsingusõna>")							[true, false] :false
// "search_right"	- otsingu puhul otsitakse ka vasakule (like "<otsingusõna>%"							[true, false] :false
// "sortable"		- antud välja puhul on lubatud kasutaja poolne järjekorra muutmine (üles/alla) 			[true, false] :false
// "extend"			- määra väljale teisendusfunktsioon (ptable_ext all kirjeldatud)						[<method> || [ <method>, <method>.. ] ]
// "field_search"	- TODO: lisada otsingukast klikitud veerule, täppisotsing veeru piires					[true, false] :false
// "hidden"			- TODO: võimalus väljaga opereerida (otsingusse, trigeritele), aga ei kuvata tabelis	[true, false] :false

$this->fields	= [
	[ "field"	=> "id",		"title" => $l->txt_request, "class" => "ohoo" ],
	[ "field"	=> "requester",	"title" => $l->txt_requester, "align" => "right", "searchable" => true ],
	[ "field"	=> "subject",	"title" => $l->txt_subject, "align" => "left", "field_search" => false, "extend" => "break_long", "searchable" => true, "search_left" => false, "search_right" => true ],
	[ "field"	=> "regdate",	"title" => $l->txt_regdate, "align" => "center", "sortable" => false, "extend" => "convert_date", "nowrap" => true ]
	//[ "field"	=> "hybrid",	"title" => $l->txt_joined, "align" => "center", "field_search" => false, "joined" => true ]
];

/* liidetavate tabelite kirjeldused (TODO: hmm, kui mitu liidetavat tabelit, mis siis saab..) */

// "table"			- tabeli nimi
// "method"			- mis tüüpi join
// "on"				- mis tingimustel
// "field"			- millist välja on vaja
// "alias"			- alias liidetavale väljale

//$this->joins		= [
//		[ "table"	=> "task", "method" => "left join", "on" => "parent_id = request.id" ], 
//		[ "field"	=> "task.owner_id", "alias" => "hybrid" ]
//];

//$this->where		= "";		// where tingimused vabas vormis

/* puhas sql-päring (selleasemel, et kasutada päringu moodustamiseks "fields" kirjelduses olevaid ja "joins" & "where" muutujaid) */

//$this->query_count= "select id from request";
//$this->query		= "select * from request";

//$this->values		= [];		// prepared pärinule omistatavad väärtused

/* triggerid */

// "ROW"			- trigger määratakse kogu valitud reale																	["ROW", "<field>"]
// "<field>"		- trigger lisatakse konkreetsele väljale reas
// "title"			- triggeri kirjeldus, mida kuvatakse rea/välja kohal ([]-vaheline asendatakse vastava välja väärtusega) ["title"]
// "data"			- siin üksikelement või massiiv, milliseid väärtusi panna kaasa triggerile								[<data> || [<data>, <data>..] ]
//					  (kõik, mis on []-vahel asendatakse selle välja väärtusega (kui leitakse))
// "link"			- kui vähemalt üks 'data'-väli pole kirjeldatud, siis minnakse kirjeldatud lingile (asendatakse [])		[<link>]
// "external"		- kas sisemine või välimine link																		[true, false] :false

$this->triggers		= [
		"ROW"		=> [ "title" => "kena [reguester]", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
		"id"		=> [ "title" => "[requester][regdate]", "link" => "http://[heh]www.ttu.ee/#[amet][perenimi][midagi]", "external" => true ]
];

$this->order		= "subject";		// esmaselt on tabel sorditud selle välja järgi (TODO: per person/tabel meelde jätta) ["<field>"]
$this->way			= "desc";			// mis suunas järjestatakse tulemused
$this->order_icon	= "chevron";		// mis tüüpi ikoone kasutatakse otsingutulemuste järjestamiseks ["chevron", "sort", "angle-double"] :"chevron"
$this->page_sizes	= [ 10 => "10 ". $l->txt_records, 25 => "25 ". $l->txt_records, 50 => "50 ". $l->txt_records, "*" => $l->txt_all_records ]; // valitavad lehepikkused
$this->page_size	= 10;				// esmane lehepikkus (TODO: milline on varasemalt valitud)
$this->autoupdate	= 0;				// mitme sekundi pärast uuendatakse antud tabelit automaatselt						[false, 1..600] :false
$this->nav_length	= 5;				// mitu navigatsiooninuppu on kuvatud esimese ja viimase lehe nuppude vahel			[5] :default (TODO: teised väärtused panna korralikult toimima)
$this->nav_header	= false;			// kas header'i navigatsiooniriba on lubatud										[true, false] :false
$this->nav_footer	= true;				// kas header'i navigatsiooniriba on lubatud										[true, false] :true
$this->nav_prev		= $l->txt_prev;		// "eelmine leht"-nupu kirjeldus													["text"] :
$this->nav_next		= $l->txt_next;		// "järgmine leht"-nupu kirjeldus													["text"] :
$this->fields_descr	= true;				// kas väljakirjeldused on lubatud													[true, false] :true
$this->header_sep	= false;			// eralda väljakirjeldused tabeli sisuosast											[true, false] :false
$this->footer_sep	= false;			// eralda alumine nav tabeli sisuosast												[true, false] :false
$this->autosearch	= true;				// kas otsingukast käitub automaatsena (alates on kirjeldatud js: search_from = l)	[true, false] :false
$this->searchable	= true;				// kas otsing ja otsingukast on rakendatud tabelile									[true, false] :false
$this->prefs		= true;				// kas on lubatud kasutajal muuta tabeli seadeid									[true, false] :true
$this->sizeable		= true;				// kas on lubatud muuta tabeli kirjete arvu ühel lehel								[true, false] :true
$this->download		= true;				// TODO: võimalda tabeli sisu allalaadimine .csv, .pdf või excel'ina				[true, false] :true
$this->smart_select	= true;				// TODO: võimaldab valida märkida tabeli ridasid ja veergusid sõltumatult			[true, false] :true
$this->fullscreen	= true;				// TODO: ava tabel täisekraanis														[true, false] :true
$this->fadein		= false;			// TODO: fade'i tabel alles siis sisse, kui on laetud								[true, false] :false
$this->fadeout		= false;			// TODO: fade'i tabel välja, enne kui trigger viib kuhugi							[true, false] :false

?>