<?php
require_once 'MDB2.php';
require_once 'config/config.php';

class DBGraphNav_DBCon {
  function get_data($parentnode, $data_type) {
    $cfg = new DBGraphNav_Config; //broken, decide on config style
    foreach ($cfg->get_queries($parentnode, $data_type) as $qry){
      $db =& MDB2::connect($qry["DSN"]);
      if (PEAR::isError($db)) {
	die($db->getMessage());
      }
      $db->setLimit(5); //debugging, prevents server load
      $result =& $db->query($qry["query_string"]);
      if (PEAR::isError($result)) {
	die($result->getMessage());
      }
      
      while ($row = $result->fetchRow()) {
	$return[] =$row;
      }
    }
    return $return;
  }


  
}

?>
