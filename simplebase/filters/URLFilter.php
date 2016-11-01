<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class for validating full URLs, like http://www.google.com/
 * It uses the built in PHP filter_var function to validate URLs for input, and sanitize them for display.
 * It raises an exception if the URL isn't valid.
 * It uses the filter_var function to display the URL
 * Please see the PHP documentation for more information.
 * http://php.net/manual/en/filter.filters.validate.php
 * NOTE: This cannot protect against malicous scripts at the destination URL. 
 * Make offsite links apparent to your users.
 */
class URLFilter extends DataFilter {
    /* Main filter method that uses the base DataFilter clean method to clean input,
     * then filters the input using PHP's filter_var
     * @param $val: string, a full URL
     * @return: string, filter_var sanitized URL
     * @throws: FilterExcept
     */
    public function filter($val) {
        $this->clean($val);
        if (filter_var($val, FILTER_VALIDATE_URL) === false) {
            throw new FilterExcept("Sorry, the URL you provided doesn't seem to be valid.");
        } else {
            return filter_var($val, FILTER_SANITIZE_URL);
        }
    }
    
    /* Display method that sanitizes the URL for display
     * @param $val: string, a full URL
     * @return: string, filter_var sanitized URL
     */
    public function display($val) {
        return filter_var($val, FILTER_SANITIZE_URL);
    }
}

?>
