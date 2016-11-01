<?php
namespace Kobalt\SimpleBase;
/* Base filtering class for user input.
 * It takes a set whitelist of values, which it will use to judge any input against.
 * The clean method takes in a value, which is generally assumed to be a string.
 * It uses trim and strip_tags to do some basic cleaning before testing it against the values.
 * It raises an exception if the value is not in the whitelist.
 * The display uses htmlentities, and most of the filter descendants use it.
 * NOTE: If input has already been htmlencoded, the display function will double the encoding.  
 * There is a filter specifically for HTML input, which uses the HTMLPurifier library to filter out
 * malicious code.  All other filters assume they're handling non-HTML input and output
 */
class DataFilter {
    /* An array of whitelisted values to compare against
     * @var array
     */
    private $whitelist = []; // An array of whitelisted values
    
    /* Sets the whitelist value
     * @param $whitelist: an array of acceptable
     * @return: void
     */
    public function setWhitelist($whitelist) {
        $this->whitelist = $whitelist;
    }
    
    /* Cleans the input value before testing
     * @param $val: string input value
     * @return: string value with whitespaces trimmed from either end and HTML tags removed
     */
    protected function clean($val) {
        return trim(strip_tags($val));
    }

    /* Filters any type of input that has a limited number of acceptable values
     * @param $val: any value
     * @return: valid value
     * @throws: FilterExcept
     */
    public function filter($val) {
        $val = $this->clean($val);
        if (count($this->whitelist) > 0 && in_array($val, $this->whitelist)) {
            return $val;
        } else if (empty($this->whitelist)) {
            throw new FilterExcept("Sorry, the whitelist needs to be set.");
        } else {
            throw new FilterExcept("Sorry, the value you provided is not a valid value.");
        }
    }
    
    /* Prepares output for display as HTML
     * @param: any type of value that can be cast to a string
     * @return: HTML escaped output
     */
    public function display($val) {
        return htmlentities((string)$val, ENT_QUOTES);
    }
}
?>
