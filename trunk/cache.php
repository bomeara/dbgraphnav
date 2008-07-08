<?php
require_once 'connections.php';

class DBGraphNav_Cache {
  function __construct() {
    $this->cfg = DBGraphNav_Config::getInstance();
    $this->queryhash = sha1($_SERVER['QUERY_STRING']);
    $this->graph = new DBGraphNav_Network;
  }

  function fetch() {
    $dir = $this->cfg->graphing['caching']['path_to_cache'];
    $h =& $this->queryhash;
    $out = $dir . '/' . $h;

    $this->graph->build_network(1, 'person');
    $this->rgraph = $this->graph->get_graph();

    switch (trim($this->cfg->graphing['caching']['behavior'])) {
    case 'simple':
      if ($time) {
	
      }

    case 'complex':

    case 'none':
      $this->rgraph->saveParsedGraph("$out.dot");
      /* Image_Graphviz does not meet our needs for this portion, so
	 we do it manually */
      $result = exec("neato -Tcmapx -o$out.map -Tpng -o$out.png $out.dot");
      echo $result;
    }
    
  }

}

?>