<?php 

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
if (!is_writable($_REQUEST['cachepath'])) {
  die("Please make the cache path you specified ($_REQUEST[cachepath]) writeable. PHP (and Graphviz) must be able to write to this directory.");
}

/* Checking to make sure the configuration file is not publicly visible */
if (!ini_get("allow_url_fopen")){
  echo "<br>We can't automatically check to see if your config.xml is publicly visible. Please manually check to make <b>SURE</b> it is not accessible from the web. If it is, your SQL password will be made publicly visible!<br>\n";
}else {
  $config_check = substr("http://" . 
			 $_SERVER['HTTP_HOST'] . 
			 $_SERVER['SCRIPT_NAME'],
			 0,
			 -16);
  require_once("config/config.php");
  $config = DBGraphNav_Config::getInstance();
  
  //not using curl here because it causes all kinds of problems
  if (strpos(@file_get_contents($config_check . $config->CONFIG_FILE_PATH),
	     "<dbgraphnav>") !== FALSE){
    die("<br><b>Error!</b> Config.xml is readable from the web!<br>\n
<br>You <b>MUST</b> place the <b>config.xml</b> file in a directory which is not publicly accessible from the web. A .htaccess file is included which should do this on most Apache webservers, but for IIS and other webservers, you will have to manually configure this. One solution is to move the file above the webserver directory. Consult the documentation for your webserver on how to prevent a file from being served to the web.<br>\n
<br>
The file currently resides in config/config.xml. Move the file, not the directory. Once you have moved the file, you must change the string near the top of config.php to point to your new location. <br>\n
<br>
If you do not take these precautions, you will be exposing your SQL password to the public on the web!<br>\n");
  }
}

?>
<form action="setup.php?setup_stage=3" method="post">
<p>Congratulations, you have Graphviz and all the pre-requisites necessary for running DBGraphNav installed.</p>

  <p>Now you need to edit your config/config.xml file with the appropriate settings. Details on the various options are available from the <a href="http://code.google.com/p/dbgraphnav/wiki/ConfigurationOptions">ConfigurationOptions</a> section in the wiki, as well as comments within that file. Be sure to avoid adding extra spaces or newlines into fields like the username and password. </p>

<br>

</form>
</body>
</html>