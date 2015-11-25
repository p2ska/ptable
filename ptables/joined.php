<?php

$this->table    = "request";
$this->where    = "";
$this->values   = "";
$this->order	= "request.regdate";
$this->way		= "desc";

$this->selection= [
	0 => [ "title" => "Esitatud",		"checked" => true,	"where" => "status = 0" ],
    1 => [ "title" => "Lahendamisel",	"checked" => false,	"where" => "status = 1" ],
    2 => [ "title" => "Lõpetatud",		"checked" => false, "where" => "status = 2" ]
];

$this->fields = [
	[ "field" => "id",         "title" => "id",        "subtable"   => "[id]", "searchable" => true, "align" => "left", "fetch" => [ "unread_messages" => "[id]" ], "print" => "{%unread_messages%}" ],
	[ "field" => "prio",	   "title" => "prio",      "hidden"     => "width<700", "translate"	=> "txt_prio_[prio]" ],
	[ "field" => "requester",  "info"  => "{%person%}","align"		=> "left", "nowrap"     => "true", "fetch"      => [ "person" => "[requester]" ], "print" => "{{amazon}}[id]{%person%}", "title" => "{{amazon}} requester", "extend" => "autolink", "searchable"	=> true, "field_search" => true, "placeholder" => "ahaa" ],
	[ "field" => "subject",    "title" => "subject",   "align"      => "left", "searchable"	=> true, "width" => "*", "extend" => "break_long" ],
    [ "field" => "status",     "title" => "status",    "translate"  => "txt_status_[status]" ],
    [ "field" => "duedate",    "title" => "duedate",   "print"	    => "-[duedate]-",	"extend" => "convert_date" ],
	[ "field" => "regdate",    "title" => "regdate",   "nowrap"     => true, "extend"	=> "convert_date" ]
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

$this->triggers	= [
	"ROW"		=> [ "title" => "[id]", "data" => [ "id" => "[id]", "href" => "http://www.ttu.ee/?id=[id]" ] ]
];

$this->subtriggers	= [
	"ROW"		=> [ "title" => "[id]", "data" => [ "id" => "[id]", "href" => "http://www.delfi.ee/?id=[id]" ] ]
];

$this->resizable    = false;
//$this->title        = $this->data["example"];
$this->autoupdates  = false;
$this->is           = [ P_NULL => "----", 0 => "000", "0000-00-00 00:00:00" => "-" ];
$this->search_ph    = $l->txt_search_ph;

?>
