<?php

$example_data = [
    "32ddwe;andres;midagi;2015-01-09",
    "c2dewd;peeter on huvitav tegelane;eeeh;2015-01-11",
    "jkh43c;kalev;!hoo;2015-01-10",
    "yr3fvv;zyrinx;kool;2015-01-04",
    "32ddwe;weber;midagi;2014-03-01",
    "c2dewd;erki on huvitav tegelane;eeeh;2013-04-21",
    "jkh43c;urmo;!hoo;2012-01-14",
    "yr3fvv;oliver;kool;2014-05-04",
    "32ddwe;kia;midagi;2013-09-19",
    "c2dewd;pomps on huvitav tegelane;eeeh;2013-11-11",
    "jkh43c;koll;!hoo;2015-11-12",
    "yr3fvv;jaanus;kool;2015-09-03",
    "32ddwe;triinu;midagi;2015-07-19",
    "c2dewd;jarmo on huvitav tegelane;eeeh;2015-05-16",
    "jkh43c;koer;!hoo;2015-06-15",
    "yr3fvv;loom;kool;2015-05-24"
];

// moodusta massiivist objektimassiiv (demo)

foreach ($example_data as $ex) {
    list($a, $b, $c, $d) = explode(";", $ex);

    $el = new stdClass();

    $el->id = $a;
    $el->nimi = $b;
    $el->lisatud = $c;
    $el->olek = $d;
    $el->deleted = 0;

    if ($d == 2)
        $el->deleted = 1;

    $data[] = $el;
}

?>
