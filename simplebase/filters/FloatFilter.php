<?php
namespace Kobalt\SimpleBase;
/* A descendant of the NumFilter class that filters and displays floats
 * The constructor takes a precision value that's used by the display method
 * It uses the PHP filter_var function to validate on input
 * It uses filter_var to sanitize on output
 * It throws an exception if the value isn't a valid float
 */
class FloatFilter extends NumFilter {
    /* The precision for the floating point number
     * @var int
     */
    public $precision;
    
    /* The constructor
     * @param $precision: integer
     */
    function __construct($precision) {
        $this->precision = $precision;
    }
    
    /* Filters floating point number input using the PHP filter_var function to validate
     * @param $val: floating point number
     * @return: valid floating point number
     * @throws: FilterExcept
     */
    public function filter($val) {
        $num = $this->clean($val);
        if (filter_var($num, FILTER_VALIDATE_FLOAT)) {
            return $num;
        } else {
            throw new FilterExcept("Sorry, that doesn't seem to be a valid floating point number.");
        }
    }
    
    /* Prepares a floating point number output for display using the PHP filter_var function to validate
     * @param $val: floating point number
     * @return: valid floating point number rounded to the $precision
     */
    public function display($num) {
        $n = filter_var($num, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_SCIENTIFIC | FILTER_FLAG_ALLOW_FRACTION);
        return round($n, $this->precision);
    }
}
?>
