<?php

  // CURRENTLY somewhat HACKY. clean up later

require_once 'Image/GraphViz.php';
require_once 'MDB2.php';
require_once 'config/config.php';

class DBGraphNav {
  private static $instance;

  function __construct() {
    $this->cfg = new DBGraphNav_Config;
  }
 
}

class DBGraphNav_DBCon extends DBGraphNav{

  function get_data($parentnode, $data_type) {
    //    $this->cfg = new DBGraphNav_Config;
    foreach ($this->cfg->get_queries($parentnode, $data_type) as $qry){
      $db =& MDB2::connect($qry["DSN"]);
      $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
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


class DBGraphNav_Network extends DBGraphNav{
  function __construct() {
    parent::__construct();
        $this->db = new DBGraphNav_DBCon; //create the database class
  }
  /*Assumes build_network has been called previously.
    
    Makes a dot representation of the data stored in the network class 
    variable.
   */
  function get_dot() {
    $graph = new Image_Graphviz();
    $graph->binPath = "/usr/local/bin/";
    $graph->dotPath = 'dot';
    //options. Hack to make it work for now.
    $graph->graph = array('directed'=>false,
			  'strict'=>true,
			  'attributes'=>array('overlap'=>'false'));
    //type
    foreach ($this->network as $type=>$value){
      //actual node
      foreach ($value as $id=>$value){
	//this creates a unique name for each node. If this
	//conflicts with a graph's naming scheme, it may be
	//hashed instead. It is not currently hashed for 
	//performance reasons.
	//	$cur_node = $graph->_escape("$type||||$id");
	//FIX ME
	$cur_node = "$type||||$id";
	$graph->addNode($cur_node,
			array( //make wordwrap configurable later
			      'label' => wordwrap($value["display_name"],
						  20)));
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
    $graph->saveParsedGraph("output.dot");
    return $graph->image('png', 'neato');
  }
  
  //assumes build_network has been called. For debugging, mostly.
  function get_network() {
    return $this->network;
  }

  /*This builds the network object using SQL queries. It takes the tree
    structure generated by the queries and flattens it out into a form
    that is easily converted into a dot file.
   */
  function build_network($basenode, $type, $maxdepth = 5) {
    $a =& $this->network[$type][$basenode];
    $a['display_name']='BASE NODE'; //FIX ME
    $a['depth']=0;
    $a['neighbors'] =
      $this->build_network_helper($basenode, $type, $maxdepth, 1);
  }
  
  /*
    This seems functional now. It might appear simpler if it were re-written
    using purely tail recursion, but the tree recursion works.
  */
  private function build_network_helper($basenode, $type, $maxdepth, $depth) {
    $friends = array();
    if ($depth <= $maxdepth) {
	foreach ($this->db->get_data($basenode, $type) as $node) {
	  $a =& $this->network[$node['type']][$node['value']];
	  if (isset($a) ){
	    if ($a['depth'] > $depth) {
	      /* If we find a newer, shorter route to a max-depth node,
		 we want to pull the network where previously it was not
		 queried.
	      */
	      if ($a['depth'] >= $maxdepth) {
		// find neighbors
		$a['neighbors'] =  
		  $this->build_network_helper($node['value'],
					      $node['type'],
					      $maxdepth,
					      $depth + 1);
	      
	      }
	      //we should use the lower depth in any case
	      $a['depth']=$depth; 
	    }
	  } else { //otherwise, pull all the info
	    $a['display_name']=$node['display_name'];
	    $a['depth']=$depth;
	    $a['neighbors'] =  
	      $this->build_network_helper($node['value'],
					  $node['type'],
					  $maxdepth,
					  $depth + 1);
	  }
	  //store friends
	  $friends[$node['type']][$node['value']] = $node['display_name'];
	}
    }
    return $friends;
  }}
?>