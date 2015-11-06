<?php

$this->table    = "request";
$this->where    = "";
$this->values   = "";
$this->order	= "request.regdate";
$this->way		= "desc";

//$this->resizable= false;

$this->fields = [
	[ "field" => "id",         "title" => "id",        "subtable"   => "[id]", "searchable" => true, "align" => "left", "fetch" => [ "unread_messages" => "[id]" ], "print" => "{%unread_messages%}" ],
	[ "field" => "prio",	   "title" => "prio",      "translate"	=> "txt_prio_[prio]" ],
	[ "field" => "requester",  "info"  => "{%person%}","nowrap"     => "true", "fetch"      => [ "person" => "[requester]" ], "print" => "{{amazon}}[id]{%person%}", "title" => "{{amazon}} requester", "extend" => "autolink", "searchable"	=> true, "field_search" => true, "placeholder" => "ahaa" ],
	[ "field" => "subject",    "title" => "subject",   "align"      => "left", "searchable"	=> true, "width" => "*", "extend" => "break_long" ],
	//[ "table" => "task",	   "field" => "status",    "title"      => "status",    "translate"     => "txt_[status]_status", "field_search" => true, "placeholder" => "status" ],
    [ "field" => "status",     "title" => "status",    "translate"  => "txt_status_[status]" ],
	//[ "table" => "task",	   "field" => "deadline",  "title"      => "deadline",  "print"			=> "-[deadline]-",	"extend" => "convert_date" ],
    [ "field" => "duedate",    "title" => "duedate",   "print"	    => "-[duedate]-",	"extend" => "convert_date" ],
	[ "field" => "regdate",    "title" => "regdate",   "nowrap"     => true, "extend"	=> "convert_date" ]
	//[ "field" => "domain",   "title" => "owner",     "searchable"	=> true, "alias" => "owner" ]
	//[ "table" => "task",     "field" => "owner_id",  "title"      => "solver",    "searchable"	=> true ]
];

$this->subquery = "select * from task where parent_id = ? order by created desc";
$this->subvalues= $this->subdata;

$this->subfields = [
	[ "field" => "id",         "title" => "ID" ],
	[ "field" => "prio",       "title" => "Prio",      "translate" => "txt_prio_[prio]" ],
	[ "field" => "status",     "title" => "Status",    "translate" => "txt_status_[status]" ],
	[ "field" => "owner_id",   "title" => "Owner",     "align"     => "left", "width" => "*" ],
	[ "field" => "created",    "title" => "Created",   "extend"    => "convert_date" ],
	[ "field" => "closed",     "title" => "Closed",    "extend"    => "convert_date" ],
	[ "field" => "deadline",   "title" => "Deadline",  "extend"    => "convert_date" ]
];

/*
$this->joins = [
    "request"	=> [ "method" => "left join", "on" => "request.id = task.parent_id" ]
];
*/

$this->triggers	= [
	"ROW"		=> [ "title" => "[id]", "data" => [ "id" => "[id]", "href" => "http://www.ttu.ee/?id=[id]" ] ]
	//"id"		=> [ "title" => "dede", "link" => "www.ttu.ee", "external" => true ]
];

$this->subtriggers	= [
	"ROW"		=> [ "title" => "[id]", "data" => [ "id" => "[id]", "href" => "http://www.delfi.ee/?id=[id]" ] ]
];

$this->resizable    = false;
$this->title        = $this->data["example"];
$this->is           = [ P_NULL => "----", 0 => "000", "0000-00-00 00:00:00" => "-" ];
$this->badge        = true;
$this->search_ph    = $l->txt_search_ph;

?>
