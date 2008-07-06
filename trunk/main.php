<?php
require_once 'connections.php';
$test = new DBGraphNav_Network;
$test->build_network($_REQUEST["id"], $_REQUEST["type"], $_REQUEST["depth"]);
//print_r($test->get_network());
//echo $test->get_data("reference", 807);
echo $test->get_dot();



?>