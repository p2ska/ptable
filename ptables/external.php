<?php

$this->triggers		= [
	"ROW"		=> [ "link"	=> "http://www.ttu.ee/#[nimi]" ],
	"nimi"		=> [ "link" => "http://www.ttu.ee[id]", "title" => "[nimi]", "external" => true ]
];

$this->fields		= [
	[ "field"	=> "id",		"title" => "ID", "searchable" => true ],
	[ "field"	=> "nimi",		"title" => "Nimi", "searchable" => true ],
	[ "field"	=> "lisatud",	"title" => "Lisatud", "searchable" => true ],
	[ "field"	=> "olek",		"title" => "Olek" ]
];

?>
