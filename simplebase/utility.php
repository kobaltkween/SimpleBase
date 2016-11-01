<?php
/* Small helper functions that are useful in multiple classes and functions
 */
function showArr($arr) {
  $str = "";
  foreach ($arr as $k => $v) {
    $str .= "$k: $v, ";
  }
  $str = trim($str, ", ");
  return $str;
}
function showProps($obj) {
  $arr = get_object_vars($obj);
  return showArr($arr);
}
function splitString($str, $word) {
  $pos = strpos($str, $word);
  return [substr($str, 0, $pos), substr($str, $pos)];
}
function line($str) {
  echo htmlentities($str, ENT_QUOTES) . "<br>";
}

/* Generate the full URL given a path from the public_html root
 * @param $path: str
 * @return: str, the full URL with http or https
*/
function fullURL($path) {
  $fullURL = ($_SERVER["HTTPS"] == "on") ? "https://" : "http://";
  $fullURL .= $_SERVER["SERVER_NAME"];
  if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
    $fullURL .= ":" . $_SERVER["SERVER_PORT"];
  }
  $fullURL .= $path;
  return $fullURL;
}

/* Get the full URL of the page requested
 * @return: string
 */
function getSelf() {
  return fullURL($_SERVER["REQUEST_URI"]);
}

/* Checks to see if a string is in another string
 * Case insensitive
 * @param $str1: string, needle
 * @param $str2: string, haystack
 * @return: boolean
 */
function inString($str1, $str2) {
    $str1 = strtolower($str1);
    $str2 = strtolower($str2);
    if (strpos($str2, $str1) === false) {
        return false;
    } else {
        return true;
    }
}

/* Recursively prints out all the values in an array or the properties in an object
 * @param $arr: array or object
 * @param $count: int, an optional level counter
 */
function printArr($arr, $count = 0) {
    if ($count == 0) {
        line("count is 0");
        $type = (is_object($arr)) ? "Object" : "Array";
        line($type);
    }
    $count += 1;
    foreach ($arr as $k => $v) {
        if (is_array($v) || is_object($v)) {
            $type = (is_object($v)) ? "Object" : "Array";
            line(str_repeat("----", $count) . "$k: $type");
            printArr($v, $count);
        } else {
            line(str_repeat("----", $count) . "$k: $v");
        }
    }
}
?>
