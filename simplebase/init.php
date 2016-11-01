<?php
// Define path separator
$ps = "/";
define("LIB_PATH", dirname(__FILE__) . $ps);
define("FILTER_PATH", LIB_PATH . $ps . "filters" . $ps);

 
require LIB_PATH . "Autoloader.php";
KobaltAutoloader::autoload();
// Start session
// session_start();
        

    
?>

    
