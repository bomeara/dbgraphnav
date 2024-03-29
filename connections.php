<?php
  /* Core program functionality is here.

     This file creates the database connection, queries the database,
     stores the results, and converts the results into a graphviz
     compatible format.
  */

require_once 'Image/GraphViz.php';
require_once 'MDB2.php';
require_once 'config/config.php';

/* Database connection class. */
class DBGraphNav_DBCon{

  function __construct() {
    $this->cfg = DBGraphNav_Config::getInstance();
  }

  /* Fetches data from the database according to the queries specified
     in the configuration. */
  function get_data($parentnode, $data_type, $query_type) {
    foreach ($this->cfg->get_queries($parentnode, $data_type, $query_type) as $qry){
      $db =& MDB2::connect($qry["DSN"]);
      if (PEAR::isError($db)) {
	die($db->getMessage().", \n".$db->getDebugInfo());
      }
      $db->setFetchMode(MDB2_FETCHMODE_ASSOC);

      $result =& $db->query($qry["query_string"]);
      if (PEAR::isError($result)) {
	die($db->getMessage().", \n".$db->getDebugInfo());
      }
      while ($row = $result->fetchRow()) {
	//a slight hack, puts the configuration values from the xml file
	//directly into the returned results. This was the best place to do it.
	$row["xml_config_vals"]["callback_url"]=$qry['callback_url'];
	$row["xml_config_vals"]["display_options"]=$qry['display_options'];
	$row["xml_config_vals"]["display_options_limited"]=
	  $qry['display_options_limited'];
	$return[] =$row;
      }
    }
    return $return;
  }
}

/* Builds the actual data representation of the graph. Most of the
   program functionality is here. */
class DBGraphNav_Network {
  function __construct() {
    $this->cfg = DBGraphNav_Config::getInstance();
    $this->db = new DBGraphNav_DBCon; //create the database connection
  }

  /*This builds the network object using SQL queries. It takes the
    tree structure generated by the queries and flattens it out into
    an array form that is easily converted into a dot file. Stores the
    results in $this->network.

    This can be called more than once to create a graph with multiple
    base nodes.
   */
  function build_network($basenode, $type, $maxdepth = 1) {
    $result = $this->db->get_data($basenode, $type, "query_base");
    $node = $result[0];
    $a =& $this->network[$type][$basenode];
    $a['display_name']= $node['display_name'];
    $a['depth']=0;
    $a['ref_count']=1;
    $a['callback_url'] = $node['callback_url'];
    $a['display_options']= $node['display_options'];
    $a['xml_display_options']=$node['xml_config_vals']['display_options'];
    $a['xml_callback_url']=$node['xml_config_vals']['callback_url'];
    $this->network_node_count = 1;
    $a['neighbors'] =
      $this->build_network_helper($basenode, $type, $maxdepth, 1);
    $this->soft_limited = $this->limit_network();
  }

  
  /*Helper function which calculates all the nodes other than the
    first node on the graph.
  */
  private function build_network_helper($basenode, $type, $maxdepth, $depth) {
    $friends = array();
    if ($depth <= $maxdepth) {
      $cur_node = $this->db->get_data($basenode, $type, "query_string");
      $hard_limit = $this->cfg->graphing['limiting']['hard_limit'];
      if (count($cur_node)<=$hard_limit
	  or $hard_limit < 0 
	  or $depth <= 1) {
	foreach ($cur_node as $node) {
	  $a =& $this->network[$node['type']][$node['value']];
	  if (isset($a) ){
	    if ($a['depth'] > $depth) {
	      /* If we find a newer, shorter route to a max-depth
		 node, we want to pull the network where previously it
		 was not querie	$cur_node
d.
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
	    $a['display_options']= $node['display_options'];
	    $a['callback_url'] = $node['callback_url'];
	    $a['xml_display_options']=$node['xml_config_vals']['display_options'];
	    $a['xml_callback_url']=$node['xml_config_vals']['callback_url'];
	    $a['xml_disp_opts_lim']=$node['xml_config_vals']['display_options_limited'];

	    $a['neighbors'] =  
	      $this->build_network_helper($node['value'],
					  $node['type'],
					  $maxdepth,
					  $depth + 1);
	  }
	  //store friends
	  $friends[$node['type']][$node['value']] = $node['display_name'];
	}
      } else {
	$this->hard_limited[$type] = $cur_node;
      }
    }
    //total number of nodes
    $this->network_node_count += 1;
    //total number of references back to each node
    foreach ($friends as $type=>$value)
      foreach ($value as $id=>$dispval)
      $this->network[$type][$id]['ref_count'] += 1;
    return $friends;
  }

  /*Assumes build_network has been called previously.
    
    Makes a dot representation of the data stored in the network class 
    variable.
   */
  function save_dot($output_filename) {
    $graph = new Image_Graphviz();

    //options.
    $opts =& $this->cfg->graphing['graphviz'];
    $graph->graph = array('directed' =>(bool)$opts['directed'],
			  'strict'   =>(bool)$opts['strict'],
		 	  'name'     =>$opts['name'],
			 'attributes'=>$this->cfg->graphing['display_options']);

    //type
    foreach ($this->network as $type=>$value){
      //actual node
      foreach ($value as $id=>$value){
	/*This creates a unique name for each node. If this conflicts
	with a graph naming scheme, it may be hashed instead. It is
	not currently hashed for performance reasons. The _escape
	function provides a graphviz-compatible ID.
	*/
	$cur_node = $graph->_escape("$type||||$id");
	/* These three different Display Options values are necessarylimited
	   in order to allow values to properly override each
	   other. */
	$dopts1 = array('URL' => 
			  $value['xml_callback_url'] . $value["callback_url"],
			'tooltip' =>$value["display_name"],
			'label' => //merely a default value for convenience
			wordwrap($value["display_name"],
				 $this->cfg->graphing['misc']['wordwrap']));
	$dopts2 = $this->attrib_string2array($value['xml_display_options']);
	$dopts3 = $this->attrib_string2array($value["display_options"]);
	if ($value['limited'] == True) {
	  $dopts4 = $this->attrib_string2array($value['xml_disp_opts_lim']);
	}
	$dopts = array_merge($dopts1, (array)$dopts2, (array)$dopts3, (array)$dopts4);
	$graph->addNode($cur_node, $dopts);
	//node neighbor type
	foreach ($value["neighbors"] as $type2=>$value2) {
	  //actual node neighbor
	  foreach ($value2 as $id2=>$display_val2){
	    $graph->addEdge(Array($cur_node => 
				     $graph->_escape("$type2||||$id2")));
	  }
	}
      }
    }
    return $graph->saveParsedGraph($output_filename);
    }
  
  /*This converts an attribute, as specified in the SQL query into an
    array suitable for sending to Image_Graphviz.

    No longer simplistic, but still a possible point for bugs later
    on. Does not properly handle escaped doublequotes.
  */
  function attrib_string2array($instr) {
    preg_match_all('/[^,"]*"[^"]*"|[^,]+/', $instr, $ary);
    foreach ($ary[0] as $b) {
      $ary2=explode("=", $b);
      $resultary[trim($ary2[0])] = trim(trim($ary2[1],'"'));
    }
    return $resultary;
  }
    
  /* Determines which nodes are overconnected, and trims their
     neighbors down to an acceptible node count. Returns the nodes
     themselves so that they may be displayed elsewhere.
  */
  function limit_network() {
    foreach ($this->network as $type=>&$value) {
      foreach ($value as $id=>&$node) {//for each high level node
	$soft_limit = $this->cfg->graphing['limiting']['soft_limit'];
	if ($this->count_neighbors($node) >= $soft_limit
	    and $soft_limit >= 0) {
	  $node['limited'] = True;
	  $this->limit_node($node);
	}
      }
    }
    foreach ($this->network as $n_type=>$n_value) {
      foreach ($n_value as $n_id=>$node2){
	if ($node2['ref_count'] < 1) {
	  //save the item for return before removing it
	  $outary[$n_type][$n_id] = $this->network[$n_type][$n_id];
	  //altogether remove items with ref_count < 1
	  unset($this->network[$n_type][$n_id]);
	}
      }
    }
    return $outary;
  }

  /* Takes an item (i.e. $this->network['type']['item']) and counts
     the neighbors it is connected to. */
  function count_neighbors($innode) {
    $count = 0;
    foreach ($innode['neighbors'] as $n_type) {
      $count += count($n_type);
    }
    return $count;
  }

  /* Limits the neighbors of a specific node. Designed to help cut
     down on the over-abundance of a few nodes which can dominate a
     graph. 
  */
  function limit_node(&$node) {
    foreach ($node['neighbors'] as $type=>$value) {
      foreach ($value as $id=>$display_value) {
	$cur_friend = &$this->network[$type][$id];
	//lower ref_count of linked friends
	if ($cur_friend['ref_count']+$this->count_neighbors($cur_friend)<=1) {
	  $cur_friend['ref_count'] -= 1;
	  unset($node['neighbors'][$type][$id]);
	}	  
      }
    }
  }

}


?>