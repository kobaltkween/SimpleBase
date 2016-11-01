<?php
namespace Kobalt\SimpleBase;
/* A simple message class, to make it easier to output feedback to HTML when testing
 */

class Message {
    /* The text of the mesage
     * @var string
     */
    private $text;


    function __construct() {
        $this->text = "";
    }
    
    /* A way to add a line of text
     * @param $text: string
     * @return: void
     */
    function add($text) {
        $this->text .= $text . "<br>";
    }
    
    /* Add some text and an exception when one is raised
     * @param $text: string
     * @param $e: \Exception
     * @return: void
     */
    function except($text, $e) {
        $this->text .= $text . "<br>";
        $this->text  .= $e->getMessage() . "<br>";
    }
    
    /* Output the full text
     * @return: void
     */
    function out() {
        return $this->text;
    }
    
    /* Reset the text, so can get new messages
     * @return: void
     */
     function reset() {
        $this->text = "";
    }
}
