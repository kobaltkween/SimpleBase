<?php
namespace Kobalt\SimpleBase;
/* A descendant of the NumFilter class that filters and displays integers
 */
class BoolFilter extends NumFilter {
    /* Filters boolean input
     * @param $val: true (case insensitive), false (case insensitive), true (PHP boolean), false (PHP boolean), 1, 0
     * @return 1 or 0
     * @throws FilterExcept
     */
    public function filter($val) {
        if ($val === 1 || strtolower($val) == "true" || $val === true) {
            return 1;
        } else if ($val === 0 || strtolower($val) == "false" || $val === false) {
            return 0;
        } else {
            throw new FilterExcept("Sorry, that is not a valid boolean input.");
        }
    }
    
    /* Outputs boolean value for use in view
     * @param $val:  1, 0
     * @return 1 or 0
     */
    public function display($val) {
        if ($val == 1 || $val == 0) {
            return $val;
        }
    }
}
?>
