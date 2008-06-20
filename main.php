<?php
require_once 'db.php';
require_once 'connections.php';
$test = new DBGraphNav_Network;
print_r($test->get_network(1, "person"));
//echo $test->get_data("reference", 807);



?>