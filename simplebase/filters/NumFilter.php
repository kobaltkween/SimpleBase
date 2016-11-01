<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class, and the parent class for all numeric filters 
 * (integer, float, and boolean).
 * This excludes all content except:

 */
class NumFilter extends DataFilter {
    
    /* A cleaning method that removes all characters except:
     * 0 - 9
     * eE
     * minus: -
     * period: .
     * plus: +
     * @param $num: a number, "True, "False", a number in scientific notation, etc.
     * @return: string, cleaned of extraneous characters
     */
    protected function clean($num) {
        return trim(preg_replace("/[^0-9eE\-\.\+]/", "", strip_tags($num)));
    }
}
?>
