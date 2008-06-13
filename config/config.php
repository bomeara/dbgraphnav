<?php


class DBGraphNav_Config {
  function __construct() {
    if (!$this->cfg = simplexml_load_file('config/config.xml')) {
      die( "Error loading config file!\n");
    }
  }

  function __destruct() {

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
  function get_queries($type) {
    $outary = Array();
    foreach ($this->cfg->database->friend_finder->$type as $element) {
      //Parse the request attributes (substitution variables) here
      $atr_search = Array();
      $atr_replace = Array();
      
      /* this array defines the mapping between an attribute like parentnode
	 and the actual value */
      $sr_lookup = 
      
      foreach($element->query_string->attributes() as $a=>$b){
	$atr_search[] =$b;
	$atr_replace[]=$lookup[$a];
      }
      $query_string = (string)$element->query_string;
      //str_replace($atr_search, 
      //				  $atr_replace, 
      //			  (string)$element->query_string);
      $outary[] = Array("DSN" => $this->merge_DSN($element->DSN),
			"query_string" => $query_string,
			"callback_url" => (string)$element->callback_url,
			"display_options"=> (string)$element->display_options);
    }
    return $outary;
  }

  /*
    Converts an XML representation of a DSN to one usable by php (that is
    either an array or a string).
  */
  private function DSN2php($DSNin) {
    $trimmed = trim($DSNin);
    if (strlen($trimmed) > 0) { //text in the node, assume string DSN
      return $trimmed;
    } else {
      $outary = Array();
      //Throws a warning when children() is empty. This is expected behavior.
      foreach ($DSNin->children() as $element) {
	$outary[$element->getName()] = (string) $element;
      }
      return $outary;
    }
  }

  /*
    The only time we ever actually need a database connection is when we're
    doing the queries. Since those can each override the default DSN, there's
    no reason to make the default values public.
    returns a php form DSN
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
	//so we default to the override value
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