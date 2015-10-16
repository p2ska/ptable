<?php

/* tabeli üldised omadused																					[variandid] :vaikeväärtus */

$this->title		= $l->?;				// tabeli pealkiri												[<title>]
$this->title_icon	= "odnoklassniki";		// tabeli pealkirjast vasakul olev ikoon						[<font-awesome ikooni klass>] :none
$this->table		= "";					// põhitabeli nimi baasis										[<table>]
$this->style		= "ptable";				// mis stiili kasutatakse tabli kujunduses						[<class>]

/* andmebaasi override'd */

$this->host			= "";
$this->database		= "";
$this->username		= "";
$this->password		= "";
$this->charset		= "utf8";
$this->collation	= "utf8_estonian_ci";

/* tabeliväljade loetelu ja omadused (kõik peale "field" väärtuse on valikulised) */

// "field"			- väljanimetus tabelis (TODO: kohustuslikkusekontoll; kas selline väli tabelist leiti)	[<field>]
// "joined"			- tähendab, et antud väli on juurdeliidetud tabelist saadud (vajab 'joins'-kirjelduses vastavat välja ja seost - 'alias')
// "title"			- tabeli päises kuvatav väljakirjeldus (mõistlik panna tõlkestring)						[<title>] :<field>
// "class"			- välja stiil, override																	[<class_name>]
// "align"			- välja sisu paiknemine																	["left", "center", "right", "justify"] :"left"
// "hidden"			- võimalus väljaga opereerida (otsing, triggerid), aga ei kuvata tabelis				[true, false] :false
// "nowrap"			- välja ei wrapita																		[true, false] :false
// "searchable"		- kui tehakse üldine otsing, siis lisatakse see väli otsingusse							[true, false] :false
// "search_left"	- otsingu puhul otsitakse ka vasakule (like "%<otsingusõna>")							[true, false] :false
// "search_right"	- otsingu puhul otsitakse ka vasakule (like "<otsingusõna>%"							[true, false] :false
// "sortable"		- antud välja puhul on lubatud kasutaja poolne järjekorra muutmine (üles/alla) 			[true, false] :false
// "extend"			- määra väljale teisendusfunktsioon (ptable_ext all kirjeldatud)						[<method>, [ <method>, <method>.. ] ]
// "translate"		- ei prindita väärtust vaid tõlge (tõlkestring + väärtus)								[false, <translation>] :false
// "field_search"	- TODO: lisada otsingukast klikitud veerule, täppisotsing veeru piires					[true, false] :false

$this->fields	= [
	[ "field"	=> "",		"title" => $l->? ]
];

/* liidetavate tabelite kirjeldused (TODO: hmm, kui mitu liidetavat tabelit, mis siis saab..) */

// "table"			- tabeli nimi
// "method"			- mis tüüpi join
// "on"				- mis tingimustel
// "field"			- millist välja on vaja
// "alias"			- alias liidetavale väljale

$this->joins		= [
	[ "table"	=> "", "method" => "left join", "on" => "" ], 
	[ "field"	=> "", "alias" => "" ]
];

/* triggerid */

// "ROW"			- trigger määratakse kogu valitud reale																	["ROW", "<field>"]
// "<field>"		- trigger lisatakse konkreetsele väljale reas
// "title"			- triggeri kirjeldus, mida kuvatakse rea/välja kohal ([]-vaheline asendatakse vastava välja väärtusega) ["title"]
// "data"			- siin üksikelement või massiiv, milliseid väärtusi panna kaasa triggerile								[<data>, [<data>, <data>..] ]
//					  (kõik, mis on []-vahel asendatakse selle välja väärtusega (kui leitakse))
// "link"			- kui vähemalt üks 'data'-väli pole kirjeldatud, siis minnakse kirjeldatud lingile (asendatakse [])		[<link>]
// "external"		- kas sisemine või välimine link																		[true, false] :false

$this->triggers		= [
	"ROW"		=> [ "title" => "", "data" => [] ],
	"id"		=> [ "title" => "", "link" => "", "external" => true ]
];

// TODO: per person/tabel meelde jätta vajalikud väljad nendest

$this->where		= "";				// where tingimused vabas vormis													["where"]
$this->values		= [];				// tingimustele vastavad väärtused													[ [<val>, <val>..] ]
$this->sort			= "";				// esmaselt on tabel sorditud selle välja järgi										[<field>]
$this->updown		= "";				// mis suunas järjestatakse tulemused												["asc", "desc"] :asc
$this->order_icon	= "chevron";		// mis tüüpi ikoone kasutatakse otsingutulemuste järjestamiseks						["chevron", "sort", "angle-double"] :"chevron"
$this->page_sizes	= [ 10 => "10 ". $l->records, 25 => "25 ". $l->records, 50 => "50 ". $l->records, "*" => $l->all_records ]; // valitavad lehepikkused
$this->pagesize		= 10;				// esmane lehepikkus (TODO: milline on varasemalt valitud)							[10..50, "*"] :10
$this->nav_length	= 5;				// mitu navigatsiooninuppu on kuvatud esimese ja viimase lehe nuppude vahel			[5] :default (TODO: teised väärtused panna korralikult toimima)
$this->nav_header	= false;			// kas header'i navigatsiooniriba on lubatud										[true, false] :false
$this->nav_footer	= true;				// kas header'i navigatsiooniriba on lubatud										[true, false] :true
$this->nav_prev		= $l->?;			// "eelmine leht"-nupu kirjeldus													["text"] :
$this->nav_next		= $l->?;			// "järgmine leht"-nupu kirjeldus													["text"] :
$this->fields_descr	= true;				// kas väljakirjeldused on lubatud													[true, false] :true
$this->header_sep	= false;			// eralda väljakirjeldused tabeli sisuosast											[true, false] :false
$this->footer_sep	= false;			// eralda alumine nav tabeli sisuosast												[true, false] :false
$this->autoupdate	= false;			// mitme sekundi pärast uuendatakse antud tabelit automaatselt						[false,5-600] :false
$this->autosearch	= false;			// kas otsingukast käitub automaatsena (alates on kirjeldatud js: search_from = l)	[true, false] :false
$this->searchable	= true;				// kas otsing ja otsingukast on rakendatud tabelile									[true, false] :true
$this->prefs		= true;				// kas on lubatud kasutajal muuta tabeli seadeid									[true, false] :true
$this->sizeable		= true;				// kas on lubatud muuta tabeli kirjete arvu ühel lehel								[true, false] :true
$this->download		= true;				// TODO: võimalda tabeli sisu allalaadimine .csv, .pdf või excel'ina				[true, false] :true
$this->smart_select	= true;				// TODO: võimaldab valida märkida tabeli ridasid ja veergusid sõltumatult			[true, false] :true
$this->fullscreen	= true;				// TODO: ava tabel täisekraanis														[true, false] :true
$this->fadein		= false;			// TODO: fade'i tabel alles siis sisse, kui on laetud								[true, false] :false
$this->fadeout		= false;			// TODO: fade'i tabel välja, enne kui trigger viib kuhugi							[true, false] :false

?>