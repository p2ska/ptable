<?php

$this->table		= "task";
//$this->where		= "";
//$this->values		= [];
$this->order		= "created";
$this->way			= "desc";

$this->fields		= [
		[ "field"	=> "id",		"title" => "Task" ],
		[ "field"	=> "parent_id",	"title" => "Request" ],
		[ "field"	=> "owner_id",	"title" => "Owner" ],
		[ "field"	=> "created",	"title" => "Created" ],
		[ "field"	=> "closed",	"title" => "Closed" ],
		[ "field"	=> "status",	"title" => "Status" ],
		[ "field"	=> "type",		"title" => "Type" ],
		[ "field"	=> "mode",		"title" => "Mode" ],
		[ "field"	=> "deadline",	"title" => "Deadline" ],
		[ "field"	=> "prio",		"title" => "Prio", "translate" => "txt_prio_" ],
		[ "field"	=> "result",	"title" => "Result" ],
];

$this->title		= "tasks";
$this->refresh		= 5;
$this->header		= false;
$this->prefs		= false;
$this->searchable	= false;
$this->nav_header	= false;
$this->nav_footer	= false;
$this->fields_descr	= false;

?>
