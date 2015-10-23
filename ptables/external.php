<?php

$this->fields		= [
	[ "field"	=> "id",		"title" => "ID",       "searchable" => false, "sortable" => false ],
	[ "field"	=> "nimi",		"title" => "Nimi",     "align" => "right", "nowrap" => true, "searchable" => true, "search_left" => true, "search_right" => true ],
	[ "field"	=> "lisatud",	"title" => "Lisatud",  "searchable" => false ],
	[ "field"	=> "olek",		"title" => "Olek",     "searchable" => false ]
];

$this->triggers		= [
	"ROW"		=> [ "link"	=> "http://www.ttu.ee/#[nimi]" ],
	"nimi"		=> [ "link" => "http://www.ttu.ee[id]", "title" => "[nimi]", "external" => true ]
];

$this->autosearch = true;

?>
