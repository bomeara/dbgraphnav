<?php
require_once 'connections.php';

class DBGraphNav_Cache {
  function __construct() {
    $this->cfg = DBGraphNav_Config::getInstance();
    $this->graph = new DBGraphNav_Network;
    //    $this->graph->build_network(1, 'person');
  }

  function fetch() {
    $this->queryhash = sha1($_SERVER['QUERY_STRING']);
    //output path to cached file without extension.
    $gcfg =& $this->cfg->graphing;
    $out = $gcfg['caching']['path_to_cache'] . $this->queryhash;
    $img = $gcfg['graphviz']['outputImageFormat'];

    switch (trim($this->cfg->graphing['caching']['behavior'])) {
    case 'simple':
      //we supress errors because the file may not exist, which is fine
      $age= (time()-filemtime("$out.dot"));
      if ($age < $gcfg["caching"]["age_limit"]) {
	return Array("$out.$img","$out.map", $age);
	break;
      }

    case 'complex':

    case 'none':
      $this->graph->save_dot("$out.dot");
      /* Image_Graphviz does not meet our needs for this, so we do it
	 manually */
      /* This is safe without any escaping because it consists of
	 configuration values and hashed user inputs, but no user
	 input is directly used in this string.
       */
      $binpath =$gcfg['graphviz']['binPath']; 
      $exec = "$binpath -Tcmapx -o$out.map -T$img -o$out.$img $out.dot";
      $result = exec($exec);
      return Array("$out.$img", "$out.map", 0);
    }
    
  }

}

?>