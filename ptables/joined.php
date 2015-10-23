<?php

$this->table    = "task";
$this->where    = "task.prio != ?";
$this->values   = [ 1 ];
$this->order	= "task.created";
$this->way		= "desc";

$this->fields = [
	[ "field" => "id",     "title" => "id" ],
	[ "field" => "prio",   "title" => "prio",      "translate" => "txt_prio_" ],
	[ "table" => "request","field" => "requester", "title"     => "requester", "searchable"	=> true ],
	[ "table" => "request","field" => "subject",   "title"     => "subject",   "searchable"	=> true, "extend" => "break_long" ],
	[ "table" => "task",   "field" => "status",    "title"     => "status",    "translate"  => "txt_task_status_" ],
	[ "table" => "task",   "field" => "deadline",  "title"     => "deadline",  "extend"		=> "convert_date" ],
	[ "table" => "task",   "field" => "created",   "title"     => "created",   "extend"		=> "convert_date" ],
	[ "table" => "request","field" => "owner_id",  "title"     => "owner",     "searchable"	=> true, "alias" => "owner" ],
	[ "table" => "task",   "field" => "owner_id",  "title"     => "solver",    "searchable"	=> true ]
];

$this->joins = [
    "request"   => [ "method" => "left join", "on" => "request.id = task.parent_id" ]
];

$this->triggers	= [
	"ROW"      => [ "title" => "[]", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
	"id"       => [ "title" => "dede", "link" => "www.ttu.ee", "external" => true ]
];

?>
