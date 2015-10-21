<?php

$this->table    = "task";
$this->where    = "";
$this->values   = [];
$this->order	= "created";
$this->way		= "desc";

$this->fields = [
	[ "table" => "task",   "field"	=> "id",         "title" => "id" ],
	[ "table" => "task",   "field"	=> "prio",       "title" => "prio",		"translate" => "txt_prio_" ],
	[ "table" => "request","field"	=> "requester",  "title" => "requester","searchable" => true, "alias" => "req" ],
	[ "table" => "request","field"	=> "subject",    "title" => "subject",	"searchable" => true ],
	[ "table" => "task",   "field"	=> "status",     "title" => "status",	"translate" => "txt_task_status_" ],
	[ "table" => "task",   "field"	=> "deadline",   "title" => "deadline",	"extend" => "convert_date" ],
	[ "table" => "task",   "field"	=> "created",    "title" => "created",	"extend" => "convert_date" ],
	[ "table" => "task",   "field"	=> "owner_id",   "title" => "solver",	"searchable" => true ]
];

$this->joins = [
    "request"   => [ "method" => "left join", "on" => "request.id = task.parent_id" ]
];

$this->triggers	= [
	"ROW"      => [ "title" => "kena", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
	"id"       => [ "title" => "dede", "link" => "www.ttu.ee", "external" => true ]
];

?>
