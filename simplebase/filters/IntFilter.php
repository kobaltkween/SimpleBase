<?php
namespace Kobalt\SimpleBase;
/* A descendant of the NumFilter class that filters and displays integers
 */
namespace Kobalt\SimpleBase;
class IntFilter extends NumFilter {
    /* Uses the PHP filter_var function to validate input
     * @param $val: int
     * @return: int
     * @throws: FilterExcept
     */
    public function filter($val) {
        $num = $this->clean($val);
        if (filter_var($num, FILTER_VALIDATE_INT) === false) {
            throw new FilterExcept("Sorry, the number you provided is not a valid integer.");
        } else {
            return $num;
        }
    }
    
    /* Uses filter_var to sanitize on output, just in case
     * @param $num: int
     * @return: int
     */
    public function display($num) {
        return filter_var($num, FILTER_SANITIZE_NUMBER_INT);
    }
}
?>
