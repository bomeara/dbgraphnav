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
      //      $db->setLimit(10); //debugging, prevents server load
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
    $graph->binPath = "/usr/local/bin/";
    //options. Hack to make it work for now.
    $graph->graph = array('directed'=>false,
			  'attributes'=>array('overlap'=>'false'));
    //type
    foreach ($this->network as $type=>$value){
      //actual node
      foreach ($value as $id=>$value){
	//this creates a unique name for each node. If this
	//conflicts with a graph's naming scheme, it may be
	//hashed instead.
	$cur_node = "$type||||$id";
	$graph->addNode($cur_node,
			array( //make wordwrap configurable later
			      'label' => wordwrap($value["display_name"], 20)));
			      //'url' => 'http://google.com/'));
	//node neighbor type
	foreach ($value["neighbors"] as $type2=>$value2) {
	  //actual node neighbor
	  foreach ($value2 as $id2=>$display_val2){
	    $graph->addEdge(Array($cur_node => "$type2||||$id2"));
	  }
	}
      }
    }
    //$graph->saveParsedGraph("output.dot");
    return $graph->image('png', 'neato');
  }

  function get_network() {
    //    $this->build_network($basenode, $type);
    return $this->network;
  }

  function build_network($basenode, $type, $maxdepth = 5, $depth = 0) {
    $a =& $this->network[$type][$basenode];
    $a['display_name']="base node";
    $a['neighbors'] =
      $this->build_network_helper($basenode, $type, $maxdepth, $depth);
  }
  
  function build_network_helper($basenode, $type, $maxdepth = 5, $depth = 0) {
    $depth += 1;
    $b = array();
    if ($depth <= $maxdepth) {
	foreach ($this->db->get_data($basenode, $type) as $node) {
	  $a =& $this->network[$node[1]][$node[0]];
	  if (!isset($a)) { //node info is always the same
	    $a['display_name']=$node[2];
	    $a['neighbors'] =
	      $this->build_network_helper($node[0], $node[1], $maxdepth, $depth);
	    $b[$node[1]][$node[0]] = $node[2]; //store friends
	  }
	}
    }
    return $b;
  }
  
  
}
?>