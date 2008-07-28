<?php
  /* This file contains the code which allows the program to utilize
     the previously calculated results. It is possible to use the
     program without interface, but the caching system improves
     performance and lowers server load.
   */

require_once 'connections.php';

class DBGraphNav_Cache {
  function __construct() {
    $this->cfg = DBGraphNav_Config::getInstance();
    $this->graph = new DBGraphNav_Network;
  }

  //clears the cache from the disk
  function clear() {
    $cache_path=$this->cfg->graphing['caching']['path_to_cache'];
    $handler = opendir($cache_path);
    $cwd = getcwd(); //store the current working directory
    chdir($cache_path); //change the current working dir to the cache dir

    $count = 0;
    while ($file = readdir($handler)) {
      if (($file != '.') && ($file != '..')){
	/* This prevents us from deleting anything but the files
	   graphviz created. This provides a bit of a safety measure,
	   and allows the use of (for example) .htaccess files. */
	switch (substr($file, -4)) {
	case ".dot":
	case ".map":
	case "." . $this->cfg->graphing['graphviz']['outputImageFormat']:
	  unlink($file); //delete the file
	  $count +=1;
	    
	}
      }
    }
    closedir($handler);
    chdir($cwd); //restore the working directory
    return $count; //return how many files were deleted
  }
  
  /* Core functionality.

     Call this to (depending on configuration) check the cache and
     either return the cached images or re-draw new ones. */

  function fetch() {
    /* Hash the query string. This is the way we determine unique
       cached filenames. If the query string is different, it is
       assumed that we will be redrawing the graph. */
    $this->queryhash = sha1($_SERVER['QUERY_STRING']);
    //output path to cached file without extension.
    $gcfg =& $this->cfg->graphing;
    $out = $gcfg['caching']['path_to_cache'] . $this->queryhash;
    $img = $gcfg['graphviz']['outputImageFormat'];

    $switch = trim($this->cfg->graphing['caching']['behavior']);
    switch ($switch) {
    case 'complex': //This switch case is odd but produces the correct results
    case 'simple':
      //we supress errors because the file may not exist, which is fine
      $age= (time()-@filemtime("$out.dot"));
      if (($age < $gcfg["caching"]["age_limit"]) || 
	  ($gcfg["caching"]["age_limit"] < 0)) {
	return Array('img'=>"$out.$img",'map'=>"$out.map", 'age'=>$age);
	break;
      }

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
	//run a diff on the old dot file and the new one
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
      $binpath =$gcfg['graphviz']['binPath']; 
      /* Image_Graphviz does not meet our needs for this, so we do it
	 directly. (Image_Graphviz does not conveniently support the
	 output of both an image map and the image itself in a single
	 operation)

	 This is safe without any escaping because it consists of
	 configuration values and hashed user inputs, but no user
	 input is directly used in this string.
       */
      $exec = "$binpath -Tcmapx -o$out.map -T$img -o$out.$img $out.dot";
      $result = exec($exec);
      return Array('img'=>"$out.$img", 'map'=>"$out.map", 'age'=>0);
    }
    
  }

}

?>