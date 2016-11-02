<?php
namespace Kobalt\SimpleBase;
/* An abstract View class meant to be subclassed into specific views
 */
abstract class View {
    /* The output the view gets from the controller
     * @var array or object, Type depends on view and the type of output it should receive
     */
    public $data = null;
    
    /* The response code the view gets from the controller
     * @var int
     */
    public $code;
    
    /* The response code label the view gets from the controller
     * Can use it in HTML Views
     * @var string
     */
    public $label;
    
    /* The path to header file of the page
     * @var string
     */
    public $header;
    
    /* The path to footer file of the page
     * @var string
     */
    public $footer;
    
    /* The path to main nav file for the page
     * @var string
     */
    public $nav
     
     /* The path to main body file of the page if the request is successful
      * @var string
      */
    public $body;
    
    /* The page parts put into an array for easy access
     * @var array
     */
    protected $parts;
    
    /* The title of the page
     * @var string
     */
    public $title;
    
    /* The CSS file specific to this view
     * @var string
     */
    public $css;
    
    /* The view gets constructed with data, response code, and response label from a controller
     * @param $output: object or array, the data the controller gets back from the model
     * @param $code: int, the HTTP response code from the controller
     * @param $label: string, the label for the HTTP response code from the controller
     */
    public function __construct($output, $code, $label) {
        $this->data = $output;
        $this->code = $code;
        $this->label = $label;
        // Build the page
        $this->buildPage();
    }
    
    /* This is a stub that you will need to override in subclasses
     * This simply points at generic headers and footers in an application's UI directory
     * The UI and CSS directory constants should be defined in your application's init file
     * @return: void
     */
    public function buildPage() {
        // Just a placeholder CSS name
        $this->css = CSS_DIR . "viewName.css";
        // Just a placeholder, assuming the data is an object and has a property "title"
        $this->title = $this->data->title
        $this->header = UI_DIR . "header.php";
        $this->nav = UI_DIR . "navigation.php";
        if ($this->code <= 300) {
            $this->body = UI_DIR . "main.php";
        } else {
            $this->body = UI_DIR . "error.php";
        }
        $this->footer = UI_DIR . "footer.php";
        // Put all of the elements into an array for easy rendering
        $this->parts = [$this->header, $this->nav, $this->body, $this->footer];
    }
    
    /* A render function to display the page
     * @return: void
     */
    public function render() {
        foreach ($this->parts as $part) {
            include($part);
        }
    }
}
