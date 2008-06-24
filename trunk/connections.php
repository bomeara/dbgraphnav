<?php

  // CURRENTLY somewhat HACKY. clean up later

require_once 'Image/GraphViz.php';
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


class DBGraphNav_Network {
  //  $network = Array();

  function __construct() {
    $this->db = new DBGraphNav_DBCon;
  }

  //expects the build_network function to have already been called
  function get_dot() {
    $graph = new Image_Graphviz();
    //type
    foreach ($this->network as $type=>$value){
      //actual node
      foreach ($value as $id=>$value){
	$cur_node = "\"$type||||$id\"";
	$graph->addNode(
			//this creates a unique name for each node. If this
			//conflicts with a graph's naming scheme, it may be
			//hashed instead.
			$cur_node,
			array(
			      'label' => $value["display_name"]));
			      //'url' => 'http://google.com/'));
	//node neighbor type
	foreach ($value["neighbors"] as $type) {
	  //actual node neighbor
	  foreach ($type as $id=>$display_val){
	    $graph->addEdge(Array($cur_node => "\"$type||||$id\""));
	  }
	}
      }
    }
    $graph->binPath = "/usr/local/bin";
    echo "here it is:";
    return $graph->fetch("png");
  }

  function get_network() {
    //    $this->build_network($basenode, $type);
    return $this->network;
  }

  function build_network($basenode, $type, $maxdepth = 5, $depth = 0) {
    $depth += 1;
    $b = array();
    if ($depth < $maxdepth) {
	foreach ($this->db->get_data($basenode, $type) as $node) {
	  $a =& $this->network[$node[1]][$node[0]];
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