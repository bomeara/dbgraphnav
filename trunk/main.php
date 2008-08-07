<?php
  /* This file is intended as an example of how to use the facilities
     provided by DBGraphNav. Feel free to modify it, or create
     something completely different. */

  /* We use session (in this example) to store the depth of the
     displayed graph. Higher values traverse deeper into the displayed
     tree. Experiment with values, since the appropriate value will
     depend on how dense your data is. */
session_start();
if (isset($_REQUEST["depth"])) {
  $depth = (int)$_REQUEST["depth"];
  $_SESSION["DBGN_depth"] = $depth;
} elseif (isset($_SESSION["DBGN_depth"])) {
  $depth = (int)$_SESSION["DBGN_depth"];
} else {
  $depth = 1; //a safe, but uninteresting default
}

//give the user some indication that the page is working and not stuck
//this is the only DBGraphNav file needed since it includes the others
require_once 'cache.php'; 
$cache = new DBGraphNav_Cache;
/* Note that DBGraphNav does not do any query escaping for you. The
   first (ID) field is the only field which must be escaped, but it is
   done here rather than deeper in the program to improve
   compatibility with databases which might have non-standard
   formats. Be sure to use the correct function (pg_escape_string,
   mysql_escapestring, etc.) for your database type. */
$id = $cache->graph->build_network(pg_escape_string($_REQUEST["id"]),
				   $_REQUEST["type"],
				   $depth);
/* Returns an array consisting of the age of the cached image (if the
   image was found in the cache - 0 indicates a fresh image, -1
   indicates a "complex" cached image [see the docs for more info]),
   the path to the actual image, and the path to the image map. */
$result = $cache->fetch();

//you don't have to display this to users, but it's convenient for debugging
echo "Cache Age: " . $result['age'] . "<br>"; 
echo '<img src="';
echo $result['img'] . '" usemap = "#G" border="0" />';
readfile($result['map']);//print the image map into the HTML source

?>
