<?php 
if (trim($_REQUEST["password"]) != trim(file_get_contents("setup/PASSWORD.TXT"))){
  die("Your password does not match. Please go back and try again.");
} else { 
  $_SESSION["authenticated"] = 1;
}

$cfg = simplexml_load_file("config/config.template.xml");
$cfg->database->DSN->phptype = $_REQUEST["database_type"];
$cfg->graphing->graphviz->binPath = $_REQUEST['gv_path'];

$_SESSION["config"] = (string)$cfg;
?>
<html>
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
  die( $error . "<br>\n Error! Database driver not found. Make sure you installed the correct database driver for MDB2. <br>\n");
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

<p>Congratulations, you have Graphviz and your database software working properly. Now lets start configuring DBGraphNav.</p>

<p>The first thing we need to set up is the database connection information. Please enter it below:</p>
  Database Type: <?php echo $_REQUEST["database_type"] ?><br>
  Hostname: <input type="text" name="hostspec"> (this is often "localhost". use "hostname:port" if you are on a nonstandard port)<br>
  Database Name: <input type="text" name="database"><br>
  Username: <input type="text" name="username"><br>
  Password: <input type="text" name="dbpass"><br>
  
<p>Next we will configure Graphviz.</p>
  Graphviz Binary path: <?php echo $_REQUEST["gv_path"] ?> <br>
  Output Image Format: <input type="text" size="5" value="png"> (See the <a href="http://www.graphviz.org/doc/info/output.html">graphviz documentation</a> for more options)<br>

<p>Now we need to set up the image cache.</p>
  Cache directory: <input type="text" name="cachepath" value="cache/"> (this is relative to the base DBGraphNav directory. Include the trailing slash.)<br>
<b>You MUST make this directory writeable by the php user.</b><br>

<p>The next value specifies the length of time during which we return a cached image instead of checking to see if it should be updated. This value should be relatively high (hours or days or longer) for datasets that don't change much, and relatively low for testing purposes and databases that change rapidly.</p>
Cache Image Age Limit: <input type="text" size="5" value="3600"> (in seconds)<br>

</html>