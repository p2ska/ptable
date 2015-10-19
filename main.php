<?php

session_name("ptable");
session_start();

if (!isset($_SESSION["lang"]))
	$_SESSION["lang"] = "ee";

if (isset($_GET["lang"]))
	$_SESSION["lang"] = $_GET["lang"];

$lang = $_SESSION["lang"];
$c_lang["ee"] = $c_lang["en"] = false;
$c_lang[$lang] = " current";

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="fonts/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<script type="text/javascript" src="/jquery.js"></script>
<script type="text/javascript" src="js/store.min.js"></script>
<script type="text/javascript" src="js/ptable.js"></script>
<title>pTable</title>
</head>
<body>
ahh
<div id="lang">
	<span class="lang<?= $c_lang["ee"] ?>" data-lang="ee">EE</span>
	<span class="lang<?= $c_lang["en"] ?>" data-lang="en">EN</span>
</div>
<br/>
<div id="requests" class="ptable no_border" data-user="andres"></div><hr/>
<div id="tasks" class="ptable no_border"></div><hr/>
<div id="navi" class="ptable"></div><hr/>
<!--<div id="external" class="ptable no_border"></div>-->
<script>
	$().ptable();
</script>
</body>
</html>
