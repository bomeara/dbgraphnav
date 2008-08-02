<?php







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
<br>You <b>MUST</b> place the <b>config.xml</b> file in a directory which is not publicly accessible from the web. A .htaccess file is included which should do this on most Apache webservers, but for IIS and other webservers, you will have to manually configure this. One solution is to move the file above the webserver directory. Consult the documentation for your webserver on prevent a file from being served to the web.<br>\n
<br>
The file currently resides in config/config.xml. Move the file, not the directory. Once you have moved the file, you must change the string near the top of config.php to point to your new location. <br>\n
<br>
If you do not take these precautions, you will be exposing your SQL password to the public on the web!<br>\n");
  }
}
?>