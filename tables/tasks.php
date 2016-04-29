<?php

$this->table		= "task";
//$this->where		= "";
//$this->values		= [];
$this->order		= "task.created";
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
		[ "field"	=> "deadline",	"title" => "Deadline", "is" => [ "0000-00-00" => "-" ], "extend" => "convert_date" ],
		[ "field"	=> "prio",		"title" => "Prio", "translate" => "txt_prio_[prio]" ],
		[ "field"	=> "result",	"title" => "Result" ],
];

$this->title		= "tasks";
$this->refresh		= 5;
$this->header		= true;
$this->prefs		= true;
$this->searchable	= false;
$this->nav_header	= false;
$this->nav_footer	= false;
$this->fields_descr	= false;

?>
