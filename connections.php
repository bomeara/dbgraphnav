<?php

  // CURRENTLY VERY HACKY. clean up later

require_once 'db.php';

class DBGraphNav_Network {
  //  $network = Array();

  function __construct() {
    $this->db = new DBGraphNav_DBCon;
  }

  function get_network($basenode, $type) {
    $this->build_network($basenode, $type);
    return $this->outary;
  }

  function build_network($basenode, $type, $maxdepth = 5, $depth = 0) {
    $depth += 1;
    $b = array();
    if ($depth < $maxdepth) {
	foreach ($this->db->get_data($basenode, $type) as $node) {
	  $a =& $this->outary[$node[1]][$node[0]];
	  if (!isset($a)) { //node info is always the same
	    $a['display_name']=$node[2];
	    $a['neighbors'] =
	      $this->build_network($node[0], $node[1], $maxdepth, $depth);
	    $b[$node[1]][$node[0]] = $node[2]; //store friends
	  }
	}
    }
    return $b;
  }
  
  
}
?>