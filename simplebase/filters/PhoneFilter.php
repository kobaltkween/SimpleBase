<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class 
 * It uses the parent display method (htmlentities)
 */
class PhoneFilter extends DataFilter {
    
    /* The filter method filters out all but the following from the value:
     * 0 - 9
     * dash: - 
     * [space]
     * parentheses: ()
     * period: .
     * x: for an extension
     * If there are illegal characters, they're just filtered out.
     * @param $val: string, phone number
     * @return: string, phone number with valid characters
     */
    public function filter($val) {
        $phone = $this->clean($val);
        return preg_replace("/[^ 0-9 x\.\-\(\)]/", "", $phone);
    }
}
?>
