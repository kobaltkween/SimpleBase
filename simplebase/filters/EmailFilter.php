<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class 
 * It uses the built in PHP filter_var function to sanitize email inputs, then validate them.
 * It uses its own display method with filter_var
 * According to the comments in the PHP manual, there are some obscure email configurations
 * that won't validate using this filter.  Please see the PHP documentation for more information.
 * http://php.net/manual/en/filter.filters.validate.php
 */
class EmailFilter extends DataFilter {
    
    /* Cleans the input value before testing
     * @param $val: string input value
     * @return: string value with whitespaces trimmed from either end and filter_var email sanitation
     */
    protected function clean($val) {
        return filter_var(trim($val), FILTER_SANITIZE_EMAIL);
    }
    
    /* Filters email values using PHP filter_var
     * @param $val: string email
     * @return: string, valid email
     * @throws: FilterExcept
     */
    public function filter($val) {
        $email = $this->clean($val);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new FilterExcept("Sorry, the email you provided doesn't seem to be valid.");
        } else {
            return $email;
        }
    }
    
    /* Prepares email address output for display
     * @param: string, email
     * @return: string, PHP filter_var sanitized email address
     */
    public function display($val) {
        return filter_var($val, FILTER_SANITIZE_EMAIL);
    }
}
?>
