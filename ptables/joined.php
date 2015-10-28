<?php

$this->table    = "task";
$this->where    = "task.prio != ?";
$this->values   = [ 1 ];
$this->order	= "task.notes";
$this->way		= "desc";

//$this->resizable= false;

$this->fields = [
	[ "field" => "notes",	"hidden"=> true ],
	[ "field" => "id",		"title" => "id",        "width" => "5%",	"print" 	=> "mida iganes" ],
	[ "field" => "closed",	"title" => "combined",	"width"	=> "5%",	"print"		=> "[status]", "is" => [ 0 => "null" ] ],
	[ "field" => "prio",	"title" => "prio",      "width" => "5%",	"translate"	=> "txt_prio_" ],
	[ "table" => "request",	"field" => "requester", "width" => "20%",	"info"		=> "{%person%}", "fetch"		=> [ "person" => "[requester]" ], "print" => "{{amazon}}[id]{%person%}", "title" => "{{amazon}} requester", "extend" => "autolink", "searchable"	=> true, "field_search" => true, "placeholder" => "ahaa" ],
	[ "table" => "request",	"field" => "subject",   "width" => "",		"title"     => "subject",   "searchable"	=> true, "extend" => "break_long" ],
	[ "table" => "task",	"field" => "status",    "width" => "5%",	"title"     => "status",    "translate"     => "txt_[status]_status", "field_search" => true, "placeholder" => "status" ],
	[ "table" => "task",	"field" => "deadline",  "width" => "10%",	"title"     => "deadline",  "print"			=> "-[deadline]-",	"extend" => "convert_date" ],
	[ "table" => "task",	"field" => "created",   "width" => "10%",	"title"     => "created",   "extend"		=> "convert_date", "field_search" => true ],
	[ "table" => "request",	"field" => "domain",    "width" => "5%",	"title"     => "owner",     "searchable"	=> true, "alias" => "owner" ],
	[ "table" => "task",	"field" => "owner_id",  "width" => "10%",	"title"     => "solver",    "searchable"	=> true ]
];

$this->joins = [
    "request"	=> [ "method" => "left join", "on" => "request.id = task.parent_id" ]
];

$this->triggers	= [
	"ROW"		=> [ "title" => "[id]", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
	"id"		=> [ "title" => "dede", "link" => "www.ttu.ee", "external" => true ],
	"requester"	=> [ "title" => "ah", "info" => true ]
];

$this->title        = $this->data["example"];
$this->is           = [ P_NULL => "----", 0 => "000" ];
$this->badge        = true;
$this->search_ph    = $l->txt_search_ph;

?>
