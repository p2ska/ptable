<?php

require_once("c:/xampp/security/ptable/_connector.php");
require_once("classes/_ptable_ext.php");

$db = new P_DATABASE();
$db->connect(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET, DB_COLLATION);

$kes = array("Andres", "Ketlin", "Thomas", "Simar", "Erkki", "Johann");
$kes_on = mt_rand(0, 5);

$values = array(
	mt_rand(100000, 999999),
	mt_rand(100000, 999999),
	"auto",
	$kes[$kes_on],
	date("Y-m-d H:i:s", time())
);

$db->query("insert into task (id, parent_id, assigned_to, owner_id, created) values (?, ?, ?, ?, ?)", $values);

?>
