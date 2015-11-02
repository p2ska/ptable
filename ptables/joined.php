<?php

$this->table    = "request";
$this->where    = "request.prio != ?";
$this->values   = [ 1 ];
$this->order	= "request.regdate";
$this->way		= "desc";

//$this->resizable= false;

$this->fields = [
	[ "field" => "id",         "title" => "id" ],
	[ "field" => "prio",	   "title" => "prio",      "translate"	=> "txt_prio_" ],
	[ "field" => "requester",  "info"  => "{%person%}","fetch"      => [ "person" => "[requester]" ], "print" => "{{amazon}}[id]{%person%}", "title" => "{{amazon}} requester", "extend" => "autolink", "searchable"	=> true, "field_search" => true, "placeholder" => "ahaa" ],
	[ "field" => "subject",    "title" => "subject",   "searchable"	=> true, "extend" => "break_long" ],
	//[ "table" => "task",	   "field" => "status",    "title"      => "status",    "translate"     => "txt_[status]_status", "field_search" => true, "placeholder" => "status" ],
    [ "field" => "status",     "title" => "status",    "translate"  => "txt_[status]_status" ],
	//[ "table" => "task",	   "field" => "deadline",  "title"      => "deadline",  "print"			=> "-[deadline]-",	"extend" => "convert_date" ],
    [ "field" => "duedate",    "title" => "duedate",   "print"	    => "-[duedate]-",	"extend" => "convert_date" ],
	[ "field" => "regdate",    "title" => "regdate",   "extend"		=> "convert_date" ]
	//[ "field" => "domain",   "title" => "owner",     "searchable"	=> true, "alias" => "owner" ]
	//[ "table" => "task",     "field" => "owner_id",  "title"      => "solver",    "searchable"	=> true ]
];

$this->subtable = [
    "query" => "select * from task where parent_id = ?",
    //"values"=> [ $this->subtable["ee"] ],
    "order" => "created",
    "way"   => "desc"
];

/*
$this->joins = [
    "request"	=> [ "method" => "left join", "on" => "request.id = task.parent_id" ]
];
*/

$this->triggers	= [
    "+"         => [ "title" => "kuva ülesandeid" ],
	"ROW"		=> [ "title" => "[id]", "data" => [ "id" => "[id]", "url" => "http://www.ttu.ee" ] ],
	"id"		=> [ "title" => "dede", "link" => "www.ttu.ee", "external" => true ],
	"requester"	=> [ "title" => "ah", "info" => true ],
];

$this->title        = $this->data["example"];
$this->is           = [ P_NULL => "----", 0 => "000" ];
$this->badge        = true;
$this->search_ph    = $l->txt_search_ph;

?>
