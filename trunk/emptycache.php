<?php
  /*This file is intended as an example of how to use the cache
    clearing functions. It is included because it is often useful when
    debugging graphing parameters.  */

require_once 'cache.php';
$cache = new DBGraphNav_Cache();
echo "Cleared " . $cache->clear() . " files.";

?>