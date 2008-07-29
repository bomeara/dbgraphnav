<?php session_start(); ?>
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
//this part isn't working yet

echo $_REQUEST["gv_path"] . " -V<br>\n";
//echo shell_exec($_REQUEST["gv_path"] . " -V ");
echo exec("whoami");
echo exec("/usr/local/bin/neato -V", $output);
print_r($output);
echo "done";
?>


</html>