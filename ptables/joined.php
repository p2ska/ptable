<?php

$this->table    = "task";
$this->where    = "task.prio != ?";
$this->values   = [ 1 ];
$this->order	= "task.created";
$this->way		= "desc";

$this->fields = [
	[ "field" => "id",     "title" => "id",        "width" => "5%",		"searchable" => true ],
	[ "field" => "prio",   "title" => "prio",      "width" => "5%",		"translate" => "txt_prio_" ],
	[ "table" => "request","field" => "requester", "width" => "20%",	"title"     => "requester", "searchable"	=> true, "field_search" => true, "placeholder" => "ahaa" ],
	[ "table" => "request","field" => "subject",   "width" => "",		"title"     => "subject",   "searchable"	=> true, "extend" => "break_long" ],
	[ "table" => "task",   "field" => "status",    "width" => "5%",		"title"     => "status",    "translate"  	=> "txt_%s_status", "field_search" => true, "placeholder" => "status" ],
	[ "table" => "task",   "field" => "deadline",  "width" => "10%",	"title"     => "deadline",  "extend"		=> "convert_date" ],
	[ "table" => "task",   "field" => "created",   "width" => "10%",	"title"     => "created",   "extend"		=> "convert_date", "field_search" => true ],
	[ "table" => "request","field" => "owner_id",  "width" => "5%",		"title"     => "owner",     "searchable"	=> true, "alias" => "owner" ],
	[ "table" => "task",   "field" => "owner_id",  "width" => "10%",	"title"     => "solver",    "searchable"	=> true ]
];

$this->joins = [
    "request"   => [ "method" => "left join", "on" => "request.id = task.parent_id" ]
];

$this->triggers	= [
	"ROW"      => [ "title" => "[]", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
	"id"       => [ "title" => "dede", "link" => "www.ttu.ee", "external" => true ]
];

$this->title = $this->data["example"];

?>
