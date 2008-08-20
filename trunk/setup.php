<?php
session_start();

function go($str){
  eval("?> " . file_get_contents("setup/" . $str . ".php") . "<php? ");
}

switch ($_REQUEST["setup_stage"]) {
case "": //First stage
  go("setup1");
  break;
case 2:
  go("setup2");
  break;
case 3:
  go("setup3");
  break;
}

?>