<?php
require_once 'connections.php';
$test = new DBGraphNav_Network;
$test->build_network(1, "person");
//print_r($test->get_network());
//echo $test->get_data("reference", 807);
$test->get_dot();



?>