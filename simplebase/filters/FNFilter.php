<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class for filtering full path filenames
 * It removes everything but
 * A - z
 * 0 - 9
 * underscore: _
 * dash: -
 * exclaimation: !
 * question: ?
 * ampersand: &
 * dollar sign: $
 * period: .
 * forward slash: /
 * It eliminates any illegal characters in the filename
 * Unlike most filters, the filter method takes a second argument for a file extension (for example, "jpg")
 * For a display function, it makes sure that the file exists and throws an exception if it doesn't
 */
class FNFilter extends  DataFilter {
    
    /* Cleans the input value before testing
     * @param $val: string input value
     * @return: string value without illegal characters
     */
    protected function clean($val) {
        $val = preg_replace("/[^A-Za-z0-9_.!\?\/\-\&\$]/", "", trim($val));
        $val = preg_replace("/([_.!\?\/\-\&\$])\\1+/", "$1", $val);
        return $val;
    }
    
    /* Filters a full path filename to end in a particular extension
     * @param $val: string, a full path filename
     * @param $ext: string, a file extension without period
     * @return: a cleaned full path file name
     */
    public function filter($val, $ext) {
        $fn = $this->clean($val);
        $path = pathinfo($fn);
        $cleanFN = $path["dirname"] . "/" . $path["filename"] . "." . $ext;
        return $cleanFN;
    }
    
    /* Prepares a full path filename for display
     * @param $fn: string
     * @return: a full path filename that has been verified to exist
     * @throws: FilterExcept
     */
    public function display($fn) {
        if (file_exists($fn)) {
            return $fn;
        } else {
            throw new FilterExcept("File not found.");
        }
    }
}
?>
