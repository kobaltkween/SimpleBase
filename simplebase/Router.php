<?php
namespace Kobalt\SimpleBase;
/* A generic router that takes in
 * a smart URL
 * a request method
 * NOTE: Since browsers can only send GET (Read) and POST (Create) methods,
 * they should use a parameter named "reqMethod" to identify PUT (Update) and DELETE requests.
 * (optional) JSON or form data
 * and builds a model from that
 */
class Router {
    
    /* An array of arrays, where each index of the main array is the placement in the smart url
     * and the array is a list of possible values at that level that aren't numbers
     * For instance, if your app allowed the endpoints /users/, /users/[id#]/, and /users/avatars/
     * the array would be [0 => "users", 1 => "avatars"]
     * @var: array
     */
    protected static $endpoints = array();
    
    /* An array of possible parameters for searches, initialized to include "limit" and "sort"
     * @var: array
     */
    protected static $options = ["limit", "sort"];
    
    /* The get variables from the request, if any
     * @var: array
     */
    public $getVars = array();
    
    /* The pieces of the URL request
     * @var array
     */
    public $urlElements = null;
     
    /* Whether or not the request is local
     * @var boolean
     */
    public $local;
    
    /* The request method
     * @var string
     */
     public $method;
     
    /* The name of the controller to use
     * @var string
     */
     public $controller;
     
    /* The list of base directories to ignore for URL routing
     * default lists useful directories
     * @var array
     */
    public static $ignore = ["css", "js", "images"];
    
    /* The database manager for the app, passed to the router
     * That way other classes down the line can access it, 
     * and use the central DBM for connections
     * @var DbManager
     */
    public $dbm;
    
    /* Constructor, sets the local value to true or false, converts the smart URL path to an array, 
     * sets only the allowed get variables, and sets the request method from the $_SERVER array
     * @param $local: boolean
     * @param $path: string, the relative path requested
     */
    public function __construct($local, $path, $dbm) {
        $this->local = $local;
        $this->setProperties($path);
        $this->routeRequest();
        $this->dbm = $dbm
    }
    
    
    
    /* Takes in a set of possible endpoint names and a level and adds them to the $endpoints array
     * @param $values: string, one or more comma separated possible values of a smart URL level
     * @param $level: int, a number for what level the possible values apply to.
     * @return: void
     */
    public static function addEndpoints($values, $level) {
        // If the level is empty, initialize it
        if (empty(self::$endpoints[$level])) {
            self::$endpoints[$level] = array();
        }
        // Make the values into an array
        $values = explode(", ", $values);
        foreach($values as $val) {
            // Make sure each value is lowercase
            self::$endpoints[$level][] = strtolower($val);
        }
    }
    
    /* Takes in a set of allowed GET variables and adds them to the $options array
     * @param $values: string, one or more comma and space spearated possible GET variables
     * @return: void
     */
    public static function addOptions($values) {
        if (!empty($values)) {
            $newOptions = explode(", ", $values);
            foreach($newOptions as $opt) {
                self::$options[] = $opt;
            }
        }
    }
    
    /* Takes in one or more names of base folders to be ignored
     *  and adds them to the $ignore array
     * @param $values: string, one or more comma and space spearated possible GET variables
     * @return: void
     */
    public static function addIgnore($values) {
        if (!empty($values)) {
            $newIgnore = explode(", ", $values);
            foreach($newIgnore as $i) {
                self::$ignore[] = $i;
            }
        }
    }
    
    /* Takes in a path, breaks it into components, makes them into ints and lowercase strings,
     * and then checks if they're allowed endpoints.  If they aren't, the path 
     * @param $path: string, a path relative to base 
     * @return: void
     */
    public function setProperties($path) {
        $path = trim($path, "/");
        if (!empty($path)) {
            // Only use explode if it's got something in it, otherwise it makes a non-empty array
            $this->urlElements = explode("/", $path);
            if (!in_array(strtolower($this->urlElements[0]), self::$ignore)) {
                foreach($this->urlElements as $k => $ue) {
                    if (is_numeric($ue)) {
                        $this->urlElements[$k] = (int)$ue;
                    } else if (array_key_exists($k, self::$endpoints)) {
                        $compare = strtolower($ue);
                        if(in_array($compare, self::$endpoints[$k])) {
                            $this->urlElements[$k] = $compare;
                        } else {
                            $this->urlElements = array_slice($this->urlElements, 0, $k);
                            break;
                        }
                    } else {
                        $this->urlElements = array_slice($this->urlElements, 0, $k);
                        break;
                    }
                }
            }
            $this->method = $_SERVER['REQUEST_METHOD'];
            
            foreach($_GET as $k => $v) {
                if (in_array($k, self::$options)) {
                    if ($k == "limit") {
                        $lims = explode(",", $v);
                        $this->getVars["limAmt"] = $lims[0];
                        $this->getVars["limOffset"] = $lims[1];
                    } else if ($k == "sort") {
                        $parts = explode(",", $v);
                        $this->getVars["sort"] = $parts[0];
                        $this->getVars["sortDir"] = $parts[1];
                    } else {
                        $this->getVars[$k] = $v;
                    }
                }
            }
        }
    }
    
    /* An empty stub for the routing method.  It needs to be overidden
     * in the subclasses
     * @return: void
     */
    public function routeRequest() {
        if (in_array(strtolower($this->urlElements[0]), self::$ignore)) {
            header($_SERVER['REQUEST_URI']);
        } else {
            // Code to set the controlller
        }
    }
        
    
}
