<?php

$this->order = "nimi";

$this->fields	= [
	[ "field"	=> "id",		"title" => "ID",       "hidden" => true, "sortable" => false ],
	[ "field"	=> "nimi",		"title" => "Nimi",     "subdata" => "[id]", "align" => "right", "nowrap" => true, "searchable" => true ],
	[ "field"	=> "lisatud",	"title" => "Lisatud" ],
	[ "field"	=> "olek",		"title" => "Olek" ]
];

$this->selection= [
	0 => [ "title" => "Kuva kustutatuid", "checked" => false, "remove" => [ "deleted" => 1 ] ]
];

$this->subcontent = "msg_content";

$this->triggers	= [
	"ROW"		=> [ "link"	=> "http://www.ttu.ee/#[nimi]" ]
];

$this->autosearch = true;

?>
