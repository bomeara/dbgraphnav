<?php
require_once 'cache.php';
$cache = new DBGraphNav_Cache();
echo "Cleared " . $cache->clear() . " files.";

?>