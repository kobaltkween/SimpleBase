<?php
require_once("../testApp/init.php");
$relPath = ltrim($_SERVER['REQUEST_URI'], dirname($_SERVER['PHP_SELF']));
// Get rid of ? part of query if it's there
$qPos = strpos($relPath, "?");
if($qPos !== false) {
    $relPath = substr($relPath, 0, $qPos);
}
if (empty($relPath)) {
    line("Empty");
} else { 
    if ($urlElements[0] = "tests") {
        header($_SERVER['REQUEST_URI']);
    }
}

line("Testing Auto Loading");
// Test Autoloading
$appTest = new AppTest();
line($appTest->message);

// Set up router
// In  the future, will use test to figure out whether request is from another app or not
$local = true;
$router = new TestAppRouter($local, $relPath);
$controllerName = $router->controller;
$control = new $controllerName($router);
line("Model: $control->modelName");
line("ID: " . $control->params["id"]);
?>

