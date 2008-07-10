<?php
session_start();
require_once 'cache.php';

$test = new DBGraphNav_Cache;
if (isset($_REQUEST["depth"])) {
  $depth = $_REQUEST["depth"];
  $_SESSION["depth"] = $depth;
} elseif (isset($_SESSION["depth"])) {
  $depth = $_SESSION["depth"];
} else {
  $depth = 1;
}

$test->graph->build_network($_REQUEST["id"], $_REQUEST["type"], $depth);
//print_r($test->get_network());
//echo $test->get_data("reference", 807);
//echo $test->get_image();
?>
<img src="<?php
$result = $test->fetch();
echo $result[0] . '" usemap = "#G" border="0" />';
readfile($result[1]); echo $result[2];?>
