<?php
// Database settings
define("DB_HOST", localhost);
// Replace the following values with your own
define("DB_USER", {Your Database User Name});
define("DB_PASS",  {Your Database Password});
define("DB_NAME", {Your Database Name});
define("PS", "/");
// Set up application and library directories
define("APP_DIR", __DIR__ . PS);
define("LIB_DIR", dirname(APP_DIR) . PS . "simplebase" . PS);
// HTMLPurifier library location, NOT INCLUDED
define("HTMLP_DIR", dirname(APP_DIR) . PS . "purifier" . PS);
// Initialize the relevant libraries
require_once(LIB_DIR . "init.php");
// Add in the helper functions from the library
include(LIB_DIR . "utility.php");
// Include the HTML Purifier to use the HTMLFilter
require_once(HTMLP_DIR . "HTMLPurifier.auto.php");

// Load this app's files
function appLoader($class) {
    $filename = $class . ".php";
    if (inString("controller", $class)) {
        $dir = "controllers/";
    } else if (inString("View", $class)) {
        $dir = "views/";
    } else {
        $dir = "models/";
    }
    $classes = glob(APP_DIR . $dir . "*.php");
    $file = APP_DIR . $dir . $filename;

    if (in_array($file, $classes)) {
        require $file;
        return true;
    } else {
        return false;
    }
}

spl_autoload_register("appLoader");

// Directory for including the ui components
define("UI_DIR", APP_DIR . "ui" . PS);

// Make the site root the base directory (http://www.kobaltkween.com/)
define("SITE_ROOT", PS);
define("IMG_DIR", SITE_ROOT . "images" . PS);
define("UPLOAD_DIR", SITE_ROOT . "uploads" . PS);
define("IMG_UPDIR", UPLOAD_DIR . "images" . PS);
define("TEMP_DIR", SITE_ROOT . "temp" . PS);

// Initialize the router
// Whitelist the endpoints, so that fake ones will just default to the last legitimate one
TestRouter::addEndpoints("images, posts, users", 0);
TestRouter::addEndpoints("", 1);
TestRouter::addEndpoints("products, promoImages, series, galleryImages, brokerages, software, examples", 2);
// Whitelist the various parameters you can set in the $_GET
TestRouter::addOptions("sort, limit, reqMethod");
TestRouter::addIgnore ("tests");

// Add database initialization (table creation) and data loading
include("dbinit.php");





