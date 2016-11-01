<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class for filtering regular plain text
 * It turns multiple spaces and tabs with a single space.  
 * It also uses strip_tags to get rid of any HTML tags.  
 * If you want to when you or your users want to put "<" or ">" in your text,
 * you should put spaces before and after them so they don't get filtered out or cause harm.
 * This filter's display uses the default htmlentities
 */
class TextFilter extends DataFilter {
    
    /* Clean function singles multiple tabs and spaces and trims them from the ends
     * @param $val: string
     * @return: string
     */
    protected function clean($val) {
        return trim(preg_replace("/\s+/", " ", $val));
    }
    
    /* Cleans and filters input to remove HTML tags
     * @param $val: string
     * @return: string, cleaned of HTML tags
     */
    public function filter($val) {
        $text = $this->clean($val);
        $text = strip_tags($text);
        return $text;
    }
}
?>
