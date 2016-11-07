<?php
/* A class to autoload classes as they're needed
 */
class KobaltAutoloader {
    /* Whether or not there's been a problem loading a class
     * @var: boolean
     */
    public static $error = false;
    
    /* Feedback on whether a class loaded or not.
     * @var: string
     */
    public static $message = "";
    
    /* The namespace this Autoloader is made for, so it can ignore classes outside of it
     * @var: string
     */
    private static $namespace = "Kobalt\\SimpleBase\\";
    
    /* Loads classes in the library
     * @param $class: string, name of the class
     * @return: boolean
     */
    public static function loader($class) {
        if (strpos($class, self::$namespace) !== false) {
            // Remove the namespace from the class to get the file name
            $filename = end(explode('\\', $class)) . ".php";
            if (strpos($class, "Filter") !== false) {
                $file = FILTER_PATH . $filename;
            } else {
                $file = LIB_PATH . $filename;
            }
            if (file_exists($file)) {
                require $file;
                self::$message .= "Loaded class $class from file $file.<br>";
                return true;
            } else {
                self::$error = true;
                self::$message .= "ERROR: Could not load class $class from file $file.<br>";
                echo self::$message;
                return false;
            }
        } else {
            return false;
        }
        self::$message = "";
    }
    
    
    /* Autoloader
     * @return void
     */
    public static function autoload() {
        spl_autoload_register(array(__CLASS__, "loader"));
    }
} 
