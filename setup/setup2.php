<?php 
if (!isset($_SESSION["authenticated"]))
  die("Please start setup with the first file, <a href='index.php'>index.php</a>.");
if (trim($_REQUEST["password"]) != trim(file_get_contents("PASSWORD.TXT"))){
  die("Your password does not match. Please go back and try again.");
}
session_start(); ?>
<htm>
<head>
<title>Setting up DBGraphNav Step 2</title>
</head>
<body>
<H1>Step 2</H1>
<?php
require_once 'PEAR/Registry.php';
require_once 'PEAR/Dependency.php';
$dbtype = $_REQUEST['database_type'];
$pear_registry = new PEAR_Registry();
$pear_dependency = new PEAR_Dependency($pear_registry);
$opts = Array(
       'type' => 'pkg',
       'rel' => 'has',
       'name' => "MDB2_Driver_$dbtype"
    );
$pear_dependency->callCheckMethod($error, $opts);
if($error) {
  die( $error . "<br>\n Make sure you installed the correct database driver for MDB2. <br>\n");
}

/*We want to read from the standard error here since graphviz prints
  version info there, for some reason. */
$process = proc_open($_REQUEST["gv_path"] . " -V", 
		     array(2 => array("pipe", "w")),
		     $pipes);
if (is_resource($process)){
  $gv_version = stream_get_contents($pipes[2]);
  fclose($pipes[2]);
  proc_close($process);
  /* We have to actually check for a portion of the graphviz version
     return string, since this pipe may also contain a "file not
     found" type error message. */
  if (strpos($gv_version,"- Graphviz version ") !== FALSE){ 
    echo "You are running \"<b>$gv_version</b>\".<br>\n";
    $pos = strpos($gv_version,"- Graphviz version ") + 19;
    $ver = explode(".", //extract the version number
		   substr($gv_version, 
			  $pos, 
			  strpos($gv_version, " ", $pos) - $pos));
    if (($ver[0] < 2) or (($ver[0] = 2) and ($ver[1] < 21))) {
      echo "Your version of Graphviz is pretty old. It is likely to work, but this program was tested with version 2.21.0. Some package managers have a very ancient version (2.16.0), which contains a number of known bugs. An upgrade is strongly recommended.<br>\n";
    }
  } else {
    die("Graphviz not found. You entered the path \"<b>$_REQUEST[gv_path]</b>\". <br>\n Make sure it is installed, make sure your path is correct (you may need to use an absolute path), make sure that php has sufficient rights to execute graphviz.<br>\n");
  }
} else {
  echo "Strange error with proc_open(). This code should never execute. Manually check your graphviz path.";
}



?>


</html>