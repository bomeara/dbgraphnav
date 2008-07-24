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

    $switch = trim($this->cfg->graphing['caching']['behavior']);
    switch ($switch) {
    case 'complex': //I know, I know... It looks odd, but it works.
    case 'simple':
      //we supress errors because the file may not exist, which is fine
      $age= (time()-@filemtime("$out.dot"));
      if (($age < $gcfg["caching"]["age_limit"]) || 
	  ($gcfg["caching"]["age_limit"] < 0)) {
	return Array('img'=>"$out.$img",'map'=>"$out.map", 'age'=>$age);
	break;
      }

      //this is a horrible abuse of the switch structure, but it works.
      if (file_exists("$out.dot") && $switch="complex"){
	/*Yes, this is slightly inefficient. However, it seems better
	  than most of the alternatives, and the efficiency loss of
	  piping a whole dot file through php and back out to the file
	  system may be even worse, when everything is
	  considered. Besides, I didn't want to require yet ANOTHER
	  pear module for comparing file contents.
	 */
	rename("$out.dot", "$out.dot.old");
	$complex_saved = $this->graph->save_dot("$out.dot");
	if (!exec((string)$this->cfg->graphing['caching']['diff'] . " $out.dot $out.dot.old")){
	  unlink("$out.dot.old"); //deletes the old file
	  return Array('img'=>"$out.$img",'map'=>"$out.map", 'age'=>-1);
	  break;
	}
	unlink("$out.dot.old");
      }
    case 'none':
      if (!$complex_saved) {
	$this->graph->save_dot("$out.dot");
      }
      /* Image_Graphviz does not meet our needs for this, so we do it
	 manually */
      /* This is safe without any escaping because it consists of
	 configuration values and hashed user inputs, but no user
	 input is directly used in this string.
       */
      $binpath =$gcfg['graphviz']['binPath']; 
      $exec = "$binpath -Tcmapx -o$out.map -T$img -o$out.$img $out.dot";
      $result = exec($exec);
      return Array('img'=>"$out.$img", 'map'=>"$out.map", 'age'=>0);
    }
    
  }

}

?>