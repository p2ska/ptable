<?php

/* päringu koostamine */

$this->table		= "task";				// põhitabel																	[<table>]: string
$this->where		= "task.status = ?";	// where tingimused vabas vormis												["where"]: string
$this->values		= [ TASK_CLOSED ];		// väärtused																	[ [<val>, <val>..] ] :array
$this->order		= "task.created";		// esmaselt on tabel sorditud selle välja järgi									[<field>]: string
$this->way			= "desc";				// mis suunas järjestatakse tulemused											["asc", "desc"] :"asc"

/* andmebaasi override'd (vajadusel) */

$this->host			= "localhost";			// host																			[<host>]: string
$this->database		= "test";				// baas																			[<name>]: string
$this->username		= "ptableuser";			// kasutaja																		[<user>]: string
$this->password		= "ptablepass";			// kasutaja parool																[<pass>]: string
$this->charset		= "utf8";				// charset																		[<charset>]: "utf8"
$this->collation	= "utf8_estonian_ci";	// collation																	[<collation>]: "utf8_estonian_ci"

/* andmemassiiv:
	baasi asemel võib kasutada sisendina ka andmemassiivi:

		ex: $data_array = [ [ "id" => "DE1242" ], [ "id" => "RE3251" ], ... ];
		* tuleb anda vastav key-value paaride massiiv, klassi loomise: PTABLE($_GET, $data_array, $translations)
*/

/* tabeliväljade loetelu ja omadused (kõik peale "field" väärtuse on valikulised) */

$this->fields	= [
   ["table" 		=> "task",		// millise tabeli väli on. kui pole kirjeldatud, siis arvestatakse et on põhitabelis    [<table>] :string
	"field"			=> "owner",		// väljanimetus tabelis, kui on märgitud '-', siis kuva fiktiivset välja, (kasuta [])	[<field>] :string
 	"title" 		=> $l->title,	// tabeli päises kuvatav väljakirjeldus (mõistlik panna tõlkestring)					[<title>] :none
	"class"			=> "neat",		// välja klass (väliseks trigger'damiseks või stiili muutmiseks (nagu ikka)				[<class_name>] :none
	"align" 		=> "center",	// väljas sisu joondamine																["left", "center", "right", "justify"] :"left"
	"value"			=> "[id][req]",	// ei kuvata väärtust, vaid kindel string või teiste väljade väärtused (kasuta [])		[none, string]:none
	"nowrap"		=> false,		// välja ei wrapita																		[true, false] :false
	"sortable"		=> true,		// antud välja puhul on lubatud kasutaja poolne järjekorra muutmine (üles/alla) 		[true, false] :true
	"searchable"	=> true,		// kui tehakse üldine otsing, siis lisatakse see väli otsingusse						[true, false] :false
	"search_left"	=> true,		// otsingu puhul otsitakse vasakule (like "%<otsingusõna>")								[true, false] :true
	"search_right"	=> true,		// otsingu puhul otsitakse paremale (like "<otsingusõna>%"								[true, false] :true
	"field_search"	=> true,		// täpisotsingu võimaldamine välja piires												[true, false] :false
	"hidden"		=> false,		// antud välja ei kuvata eraldi veerus, aga saab kasutada seda väärtusena mujal tabelis	[true, false] :false
	"placeholder"	=> "Owner",		// väljaotsingukasti placeholder														[<placeholder>] :none
	"width"			=> "10%",		// kui '$this->resizeable=false', siis saab veergudele panna % või px laiuse			[<width>] :string
	"alias"			=> "solver",	// kui väli on liidetud tabelist, lisa vajadusel alias, väljakonfliktide vältimiseks	[<field>] :string
	"print"			=> "#%s#",		// prindi väärtus antud stringi sisse ([] asendatakse välja väärtusega)					[none, <string>] :none
	"translate"		=> "status_%s"	// prinditakse välja väärtus sprintf'iga tõlke külge (tõlkestring + väärtus)			[none, <translation>] :none
	"extend"		=> "autolink",	// määra väljale teisendusfunktsioon (ptable_ext all kirjeldatud)						[<method>, [ <method>, <method>.. ] ]: string
	]
];

/* liidetavate tabelite kirjeldused */

$this->joins		= [
  ["table"			=> "request",						// milline tabel liidetakse põhitabeliga
   "method"			=> "left join",						// kuidas tabel liita
   "on"				=> "request.id = task.parent_id"	// millised on vastavuses olevad väljad
   ]
];

/* triggerid (eraldi rea- ja väljatriggerid: väljatrigger omab muidugi suuremat prioriteeti) */

$this->triggers		= [
	"ROW"		=> [						// "ROW" - trigger määratakse kogu valitud reale								["ROW", "<field>"]: string
		"title" => "",						// kuvatakse rea kohal ([] kirjelduses asendatakse välja väärtusega)			[<title>] :string
		"data" => [] ],						// siin üksikelement või massiiv, milliseid väärtusi panna kaasa triggerile		[<data>, [<data>, <data>..] ]
	 	"link"		=> "www.ttu.ee/#[id]",	// kui 'data'-väli POLE kirjeldatud, siis suunatakse kasutaja lingile)			[<link>]: string
	 	"external"	=> true					// kas link avatakse uues aknas link											[true, false] :false

	"id"		=> [						// trigger lisatakse konkreetsele väljale reas									[<field>] :string
		"title" 	=> "[subject]", 		// kuvatakse rea/välja kohal ([] kirjelduses asendatakse välja väärtusega)		[<title>] :string
	 	"data"		=> [ "id" => "[id]" ],	// siin üksikelement või massiiv, milliseid väärtusi panna kaasa triggerile		[<data>, [<data>, <data>..] ]
	 	"link"		=> "www.ttu.ee/#[id]",	// kui 'data'-väli POLE kirjeldatud, siis suunatakse kasutaja lingile)			[<link>]: string
	 	"external"	=> true					// kas link avatakse uues aknas link											[true, false] :false
	]
];

/* tabeli seadistused (need kirjutavad üle default seaded; aga omakorda võib antud seaded üle kirjutada JS kaudu) */

$this->title		= $l->txt_task_table;	// tabeli pealkiri																[<title>]: string
$this->title_icon	= "odnoklassniki";		// tabeli pealkirjast eesolev ikoon												[<font-awesome ikooni klass>] : none
$this->order_icon	= "chevron";			// mis tüüpi ikoone kasutatakse otsingutulemuste järjestamiseks					["chevron", "sort", "angle-double"] :"chevron"
$this->nav_length	= 5;					// mitu navigatsiooninuppu on kuvatud esimese ja viimase lehe nuppude vahel		[1-10] :5 (TODO: teised väärtused panna korralikult toimima)
$this->nav_header	= false;				// kas header'i navigatsiooniriba on lubatud									[true, false] :false
$this->nav_footer	= true;					// kas header'i navigatsiooniriba on lubatud									[true, false] :true
$this->nav_prev		= $l->txt_prev;			// "eelmine leht"-nupu kirjeldus												["text"] : string
$this->nav_next		= $l->txt_next;			// "järgmine leht"-nupu kirjeldus												["text"] : string
$this->page_size	= 10;					// esmane lehepikkus															[10..50, "*"] :10
$this->page_sizes	= [ 10 => "10 ". $l->rec, 25 => "25 ". $l->rec, 50 => "50 ". $l->rec, "*" => $l->all ];	// lehepikkus	[10..50, "*"] :array
$this->autoupdates	= [ 10 => "10s", 30 => "30s", 60 => "1m", 300 => "5m", 600 => "10m" ], // millised uuendusajad			[[1]..[600]..] :array
$this->refresh		= false;				// mitme sekundi pärast uuendatakse antud tabelit automaatselt					[false,5-600] :false
$this->header_sep	= false;				// eralda väljakirjeldused tabeli sisuosast										[true, false] :false
$this->footer_sep	= false;				// eralda alumine nav tabeli sisuosast											[true, false] :false
$this->fields_descr	= true;					// kas väljakirjeldused on lubatud												[true, false] :true
$this->autosearch	= false;				// kas otsingukast käitub automaatsena (otsitakse alates: (JS) search_from = l)	[true, false] :false
$this->searchable	= true;					// kas otsing ja otsingukast on rakendatud tabelile								[true, false] :true
$this->prefs		= true;					// kas on lubatud kasutajal muuta tabeli seadeid								[true, false] :true
$this->store_prefs	= true;					// salvestab tabeli põhiandmed (välja laiused salvestatakse siiski alati)		[true, false] :true
$this->sizeable		= true;					// kas on lubatud muuta tabeli kirjete arvu ühel lehel							[true, false] :true
$this->resizable	= true;					// kas tabeli veergude laiust saab muuta; kui ei, siis 'width'-ga saab muuta	[true, false] :true
$this->minimize		= true;					// võimaldab tabeli kokkurullida (mõistlik kus sel juhul on tabelil pealkiri)	[true, false] :true
$this->minimised	= false;				// algselt on tabel kokkurullitud (vajab pea-div'ile 'rolled'-klassi lisamist) 	[true, false] :false
$this->maximize		= true;					// TODO: ava tabel täisekraanis													[true, false] :true
$this->download		= true;					// TODO: võimalda tabeli sisu allalaadimine .csv, .pdf või excel'ina			[true, false] :true
$this->smart_select	= true;					// TODO: võimaldab valida märkida tabeli ridasid ja veergusid sõltumatult		[true, false] :true
$this->debug		= false;				// kuvab päringuid arendaja jaoks jms											[true, false] :false

?>
