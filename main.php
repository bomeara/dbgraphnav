<?php
require_once 'cache.php';
$test = new DBGraphNav_Cache;
$test->graph->build_network($_REQUEST["id"], $_REQUEST["type"], $_REQUEST["depth"]);
//print_r($test->get_network());
//echo $test->get_data("reference", 807);
//echo $test->get_image();
$test->fetch();


?>