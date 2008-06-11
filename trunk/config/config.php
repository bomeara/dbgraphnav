<?php


class DBGraphNav_Config {
  function __construct() {
    if (!$this->cfg = simplexml_load_file('config_defaults.xml')) {
      die( "Error loading config file!\n");
    }
  }

  function __destruct() {

  }



  /*
    Converts an XML representation of a DSN to one usable by php (that is
    either an array or a string).
  */
  private function DSN2php($DSNin) {
    if (!strlen($DSNin)) { //this has text in the node, assume string DSN
      return $DSNin;
    } else {
      $outary = "";
      foreach ($DSNin as $element) {
	$outary[$element->getName] = $element;
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
    return DSN2php($this->cfg->database->DSN);
  }

  // called with a (potential) DSN, merges it with the default values
  // takes a DSN in the form of a string or an array, NOT AN XML OBJECT
  private function merge_DSN($newDSN) {
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