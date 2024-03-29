<?php
class DBGraphNav_Config {
  /* If you moved config.xml, please modify the following string to
     match your new path. You may need to use a relative path
     including "../" in order to reach a directory which is above this
     one. This path should be relative to the main DBGraphNav
     directory. The important thing is that the configuration file is
     not publicly accessible from the web. */
  public $CONFIG_FILE_PATH = "config/config.xml";

  /* The main configuration class. Implemented as a singleton object
     so that we don't have to reload the configuration XML file
     repeatedly as we move through the program. To get a new instance,
     use DBGraphNav_Config::getInstance().

     Provides a customized interface to config.xml that provides the
     necessary pieces of information in a format more convenient for
     php to handle.
   */
  static private $instance;
  
  //singleton creation object
  public function getInstance() {
    if (!isset(self::$instance)){
      self::$instance = new DBGraphNav_Config();
    }
    return self::$instance;
  }
  
  //don't call this directly with the new keyword, instead use:
  //DBGraphNav_Config::getInstance();
  private function __construct() {
    if (!$this->cfg = simplexml_load_file($this->CONFIG_FILE_PATH)) {
      die( "Error loading config file!\n");
    }
    $this->graphing = $this->xml2array($this->cfg->graphing->children());
  }

  /* Simple and (somewhat) dirty function for converting XML into an
     array. Only used on sections which are certain not to have
     repeated elements. If it is used on a section with repeated
     elements, the last item will override any previous data. */
  function xml2array($xml) {
    foreach ($xml as $name=>$value) {
      $children = $value->children();
      if (count($children) > 0) {
	$outary[$name] = $this->xml2array($children);
      } else {
	$outary[$name] = (string)$value;
      }
    }
    return $outary;
  }

  /*
    This function returns an Array containing subelements consisting of
    an array containing:
    *the DSN (array or string)
    *The query string
    *(optional) callback url
    *(optional) display_options

    This allows us to run multiple queries for the same datatype.
   */
  function get_queries($parentnode, $type, $query_type = "query_string") {
    $outary = Array();
    foreach ($this->cfg->database->friend_finder->$type as $element) {
      $atr_search = Array();
      $atr_replace = Array();

      //Parse the request attributes (substitution variables) here      
      /* this array defines the mapping between an attribute like parentnode
	 and the actual value. Expand for more  */
      $sr_lookup = Array( "parentnode" => $parentnode);
    
      //build the replacement array
      foreach($element->$query_type->attributes() as $a=>$b){
	$atr_search[] =$b;
	$atr_replace[]=$sr_lookup[$a];
      }
      //do the replacement
      $query_string = 
	str_replace($atr_search, 
		    $atr_replace, 
		    (string)$element->$query_type);
      $outary[] = Array("DSN" => $this->merge_DSN($element->DSN),
			"query_string" => $query_string,
			"callback_url" => (string)$element->callback_url,
			"display_options"=> (string)$element->display_options,
			"display_options_limited"=> (string)$element->display_options_limited);
    }
    return $outary;
  }

  /*
    Converts an XML representation of a DSN to one usable by php (that is,
    either an array or a string).
  */
  private function DSN2php($DSNin) {
    $trimmed = trim($DSNin);
    if (strlen($trimmed) > 0) { //text in the node, assume string DSN
      return $trimmed;
    } else {
      $outary = Array();
      $a = error_reporting(1); //temporarily disable warnings
      //Throws a warning when children() is empty. This is expected behavior.
      foreach ($DSNin->children() as $element) {
	$outary[$element->getName()] = (string) $element;
      }
      error_reporting($a); //turn error reporting back on
      return $outary;
    }
  }

  /*
    The only time we ever actually need a database connection is when we're
    doing the queries. Since those can each override the default DSN, there's
    no reason to make the default values public.

    Returns a php form DSN
  */
  private function get_default_DSN() {
    return $this->DSN2php($this->cfg->database->DSN);
  }

  // called with a (potential) DSN, merges it with the default values
  // takes a DSN in the form of an XML OBJECT
  // returns a php form DSN
  private function merge_DSN($newDSN) {
    $newDSN = $this->DSN2php($newDSN);
    if (empty($newDSN)) {
      return $this->get_default_DSN();
    }elseif (is_string($newDSN)){
      // we don't merge strings
      return $newDSN;
    } elseif (is_array($newDSN)){
      if (is_string($default_DSN = $this->get_default_DSN())) {
 	//we can't merge an array and a string,
	//so we default to the string for the override value
	return $newDSN;
      } elseif (is_array($default_DSN)) {
	//Actually merge two arrays
	return array_merge($default_DSN, $newDSN);
      } else {
	die ("weird error in merge_DSN, default_DSN not array or string");
      }
    } else {
      die("weird error in merge_DSN, newDSN not empty, string, or array");
    }    
  }
}


?>