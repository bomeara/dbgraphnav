<?php
session_start();
require_once 'cache.php';

$cache = new DBGraphNav_Cache;
if (isset($_REQUEST["depth"])) {
  $depth = (int)$_REQUEST["depth"];
  $_SESSION["DBGN_depth"] = $depth;
} elseif (isset($_SESSION["DBGN_depth"])) {
  $depth = (int)$_SESSION["DBGN_depth"];
} else {
  $depth = 1;
}

//ID NEEDS TO BE ESCAPED! FIX ME SOON. (type does not need escaping)
$cache->graph->build_network($_REQUEST["id"], $_REQUEST["type"], $depth);
$result = $cache->fetch();

echo "Cache Age: " . $result['age'] . "<br>";
echo '<img src="';
echo $result['img'] . '" usemap = "#G" border="0" />';
//echo image map
readfile($result['map']);

?>
