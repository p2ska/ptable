<?php

$this->table		= "personal_navi";
//$this->where		= "";
//$this->values		= [];
//$this->order		= "perenimi";
$this->way = "desc";

$this->fields		= [
		[ "field"	=> "eesnimi",	"title" => "Eesnimi",	"searchable" => true ],
		[ "field"	=> "perenimi",	"title" => "Perenimi",	"searchable" => true ],
		[ "field"	=> "struktuur",	"title" => "Struktuur",	"searchable" => true ],
		[ "field"	=> "amet",		"title" => "Amet" ],
		[ "field"	=> "aadress",	"title" => "Aadress" ],
		[ "field"	=> "epost",		"title" => "E-post" ]
];

$this->triggers		= [
		"ROW"		=> [ "link"	=> "http://www.ttu.ee/#[eesnimi]" ],
		"perenimi"	=> [ "link" => "http://[heh]www.ttu.ee/#[amet][perenimi][midagi]oo", "title" => "[amet][perenimi]", "external" => true ]
];

$this->host			= "localhost";
$this->database		= "test";
$this->username		= DB_USER;
$this->password		= DB_PASS;
$this->charset		= "utf8";
$this->collation	= "utf8_estonian_ci";

$this->page_sizes	= [ 10 => "10", 50 => "50", "*" => $l->txt_all_records ];
$this->order_icon	= "angle-double";
$this->sizeable		= true;
$this->header_sep	= true;
$this->nav_header	= true;
$this->footer_sep	= true;

?>
