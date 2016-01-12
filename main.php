<?php

session_name("ptable");
session_start();

if (!isset($_SESSION["lang"]))
	$_SESSION["lang"] = "ee";

if (isset($_GET["lang"]))
	$_SESSION["lang"] = $_GET["lang"];

$lang = $_SESSION["lang"];
$c_lang["ee"] = $c_lang["en"] = false;
$c_lang[$lang] = " current_lang";

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="fonts/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<title>pTable</title>
</head>
<body>
<div id="lang">
	<span class="lang<?= $c_lang["ee"] ?>" data-lang="ee">EE</span>
	<span class="lang<?= $c_lang["en"] ?>" data-lang="en">EN</span>
</div>
<br/>
<div>
    <span id="joined_badge" class="badge"></span>
    <span id="requests_badge" class="badge"></span>
    <span id="tasks_badge" class="badge"></span>
    <span id="navi_badge" class="badge"></span>
</div>
<!--<div id="joined1" class="ptable no_border" data-template="joined" data-example="midagi"></div><hr/>
<div id="joined2" class="ptable no_border" data-template="joined" data-example="midagi"></div><hr/>
<div id="requests" class="ptable no_border" data-user="andres"></div><hr/>
<div id="tasks" class="ptable no_border"></div><hr/>
<div id="navi" class="ptable"></div><hr/>-->
<div id="external" class="ptable no_border"></div>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/store.min.js"></script>
<script type="text/javascript" src="js/ptable.js"></script>
</body>
</html>
