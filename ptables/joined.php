<?php

$this->table    = "task";
$this->where    = "task.prio != ?";
$this->values   = [ 1 ];
$this->order	= "task.created";
$this->way		= "desc";

$this->resizable= false;

$this->fields = [
	[ "field" => "id",     "title" => "id",        "width" => "10%" ],
	[ "field" => "prio",   "title" => "prio",      "width" => "10%",   "translate" => "txt_prio_" ],
	[ "table" => "request","field" => "requester", "width" => "10%",   "title"     => "requester", "searchable"	=> true ],
	[ "table" => "request","field" => "subject",   "width" => "10%",   "title"     => "subject",   "searchable"	=> true, "extend" => "break_long" ],
	[ "table" => "task",   "field" => "status",    "width" => "10%",   "title"     => "status",    "translate"  => "txt_task_status_" ],
	[ "table" => "task",   "field" => "deadline",  "width" => "10%",   "title"     => "deadline",  "extend"		=> "convert_date" ],
	[ "table" => "task",   "field" => "created",   "width" => "10%",   "title"     => "created",   "extend"		=> "convert_date" ],
	[ "table" => "request","field" => "owner_id",  "width" => "10%",   "title"     => "owner",     "searchable"	=> true, "alias" => "owner" ],
	[ "table" => "task",   "field" => "owner_id",  "width" => "10%",   "title"     => "solver",    "searchable"	=> true ]
];

$this->joins = [
    "request"   => [ "method" => "left join", "on" => "request.id = task.parent_id" ]
];

$this->triggers	= [
	"ROW"      => [ "title" => "[]", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
	"id"       => [ "title" => "dede", "link" => "www.ttu.ee", "external" => true ]
];

?>
