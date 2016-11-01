<?php
namespace Kobalt\SimpleBase;
/* An abstract View class meant to be subclassed into specific views
 */
abstract class View {
    /* The output the view gets from the controller
     * @var array
     */
    protected $output = null;
    
    /* The response code the view gets from the controller
     * @var int
     */
    protected $code;
    
    /* The response code label the view gets from the controller
     * Can use it in HTML Views
     * @var string
     */
    public $label;
    
    public function __construct($output, $code, $label) {
        $this->output = $output;
        $this->code = $code;
        $this->label = $label;
    }
}
