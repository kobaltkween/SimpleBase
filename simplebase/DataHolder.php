<?php
namespace Kobalt\SimpleBase;
/* A helper class for uploading several rows of data
 * Creates an array of associative arrays of data
 * It's meant to be used when setting up a database
 * and filling it with data either for testing or for
 * initializing a site.
 */
class DataHolder {
    /* The names of the columns in the table to be inserted
     * @var array
     */
    public $cols;
    
    /* The array holding the rows of data, which are themselves arrays
     * @var array
     */
    public $rows  = array();

    /* Constructor sets the columns from input
     * @param $colString: string, comma and space separated string of columns
     */
    function __construct($colString) {
        $this->cols = explode(", ",  $colString);
    }

    /* Assumes value input as array  or a single value (string, integer, etc.)
     * The values must be in the same order as the columns
     * @param $vals: array or basic single value (int, bool, string, etc.)
     * @return: void
     */
    function addRow($vals) {
        $row = [];
        if (is_array($vals)) {
            $i = 0;
            while ($i < count($this->cols)) {
                $row[$this->cols[$i]] = $vals[$i];
                $i++;
            }
        } else {
            $row[$this->cols[0]] = $vals;
        }
        $this->rows[] = $row;
    }
}