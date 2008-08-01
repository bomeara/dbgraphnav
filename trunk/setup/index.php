<?php 
if(trim(file_get_contents("PASSWORD.TXT")) == "changeme")
  die("Please open the file PASSWORD.TXT in this directory and change the password to begin setup. Please note that this password is not very secure, and is only in effect for the duration of this setup process.");
session_start();
//$_SESSION["authenticated"] = True;
?>
<html>
<head>
<title>Setting up DBGraphNav</title>
</head>
<body>
<H1>Prereqs</H1>
<p>For any missing items here, please see the <a href="http://code.google.com/p/dbgraphnav/wiki/Installation">Installation Instructions<a>.<br><br>
Checking Dependencies...<br>
<?php 
//IF YOU SEE THIS MESSAGE, PHP DOES NOT APPEAR TO BE FUNCTIONAL.

if (version_compare(PHP_VERSION, '5.1.0', '<')) {
   die("Your version of PHP is " . PHP_VERSION . ". Unfortunately, this application requires at least version 5.1.0. Please upgrade to continue installation. \n<br>");
 }
if(!include("PEAR.php")) {
  die("you don't seem to have PEAR installed. Please install it to continue. \n<br>");
}
require_once 'PEAR/Registry.php';
require_once 'PEAR/Dependency.php';
$pear_registry = new PEAR_Registry();
$pear_dependency = new PEAR_Dependency($pear_registry);
$opts = Array(
       'type' => 'pkg',
       'rel' => 'ge',
       'version' => '1.3.0RC3',
       'name' => 'Image_GraphViz'
    );
$pear_dependency->callCheckMethod($error, $opts);
if($error) {
  die( $error . "<br>\n Make sure you installed the latest beta version of Image_Graphviz (as of this writing) since 1.2.1 is very old. <br>\n");
}
$opts['name'] = 'MDB2';
$opts['version'] = '2.4.1';

$pear_dependency->callCheckMethod($error, $opts);
if($error) {
  die($error . "<br>\n Make sure you have installed MDB2<br>\n");
}

?>
Congratulations, you have most of the pre-requisites installed!<br>
<form action="setup2.php" method="post">
  Password: (you entered this into password.txt)<br>
  <input type="password" name="password"><br>
  Please select your database type:<br>
<select name="database_type">
<option value="mysql">MySQL</option>
<option value="mysqli">MySQL (mysqli interface)</option>
<option value="pgsql">PostgreSQL</option>
<option value="oci8 ">Oracle 7/8/9/10</option>
<option value="sqlite">SQLite 2</option>
<option value="fbsql">FrontBase</option>
<option value="ibase">InterBase / Firebird (requires PHP 5)</option>
<option value="mssql">Microsoft SQL Server (NOT for Sybase)</option>
<option value="querysim">QuerySim</option>
</select>
<br>
  Please <a href="http://www.graphviz.org/Download..php">Install Graphviz</a> before continuing.<br>
  Input the path to your graphviz binary (for example "/usr/local/bin/neato"):<br>
<input type="text" name="gv_path" value="<?php echo @`which neato` ?>"/>
<br>
  <input type="submit" value="Continue...">
</form>
</body>
</html>