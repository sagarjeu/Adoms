<?php

namespace Adoms\wireframe;

$my = function ($pClassName) {
    include_once("c:\\xampp\\htdocs\\adoms\\" . strtolower($pClassName) . ".php");
};
spl_autoload_register($my, true, 1);

$pgsv = new PageViews("adp","../../BestPHPEverNow");

$pgsv->viewPartial("index.php");

?>
