<?php


class DBGraphNav_Config {
  function __construct() {
    if (!$this->cfg = simplexml_load_file('config.xml')) {
      die( "Error loading config file!\n");
    }
  }

  function __destruct() {

  }
  /*
    This function returns an Array containing subelements consisting of
    an array containing:
    0 - the DSN
    1 - The query string
    2 - (optional) callback url
    3 - (optional) display_options

    This allows us to run multiple queries for the same datatype.
   */
  function get_queries($type) {
    $outary = Array();
    foreach ($this->cfg->database->friend_finder->$type as $element) {
      echo (string) $element->DSN;
      $outary[] = Array($this->merge_DSN($element->DSN),
			(string)$element->query_string,
			(string)$element->callback_url,
			(string)$element->display_options);
    }
    return $outary;
  }

  function test() {
    //    print_r(strlen($this->cfg->database->friend_finder->example_type->DSN));

    print_r( $this->merge_DSN($this->cfg->database->friend_finder->example_type2->DSN));
  }

  /*
    Converts an XML representation of a DSN to one usable by php (that is
    either an array or a string).
  */
  private function DSN2php($DSNin) {
    if (strlen(trim($DSNin)) > 0) { //text in the node, assume string DSN
      return trim($DSNin);
    } else {
      $outary = Array();
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