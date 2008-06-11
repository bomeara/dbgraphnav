<?php


class DBGraphNav_Config {
  function __construct() {
    if (!$cfg = domxml_open_file("config_default.xml")){
      die( "Error loading config file!");
    }
  }

  function __destruct() {
    $cfg->free();
  }
  
  private function get_default_DSN() {
    
  }

  // called with a (potential) DSN, merges it with the default values
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