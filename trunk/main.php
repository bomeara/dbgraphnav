<?php
session_start();
require_once 'cache.php';

$graph = new DBGraphNav_Cache;
if (isset($_REQUEST["DBGN_depth"])) {
  $depth = (int)$_REQUEST["DBGN_depth"];
  $_SESSION["DBGN_depth"] = $depth;
} elseif (isset($_SESSION["DBGN_depth"])) {
  $depth = (int)$_SESSION["DBGN_depth"];
} else {
  $depth = 1;
}

//ID NEEDS TO BE ESCAPED! FIX ME SOON. (type does not need escaping)
$graph->graph->build_network($_REQUEST["id"], $_REQUEST["type"], $depth);
$result = $graph->fetch();

echo "Cache Age: " . $result['age'] . "<br>";
echo '<object type="image/svg+xml" data="';
echo $result['img'] . '" usemap = "#G" border="0" />';
//echo image map
readfile($result['map']);

?>
