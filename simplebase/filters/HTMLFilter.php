<?php
namespace Kobalt\SimpleBase;
/* A descendant of the DataFilter class for filtering HTML.
 * It uses the HTMLPurifier library found at http://htmlpurifier.org
 * YOU MUST DOWNLOAD AND INSTALL HTMLPurifier FOR THIS FILTER TO WORK.
 */
class HTMLFilter extends DataFilter {
    /* The an instance of the HTMLPurifier
     *
     * @var int
     */
    private $purifier;
    
    /* The constructor where the HTMLPurifier is setup.  If you want to change what HTML tags and properties are allowed, 
     * see the HTMLPurifier Documentation (http://htmlpurifier.org/live/configdoc/plain.html), and change the constructor.
     * It currently allows only the following tags and attributes
     * p
     * ul
     * ol
     * li
     * blockquote
     * a[href]
     * strong
     * em
     * br
     * It's also set to transform b and i to strong and em respectively.
     */
    function __construct() {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set("HTML.Doctype", "HTML 4.01 Transitional");
        $config->set("Attr.AllowedClasses", "right,left");
        $config->set("HTML.Allowed", "p,ul,ol,li,blockquote,a[href],strong,em,br");
        $config->set("AutoFormat.RemoveEmpty", true);
        $def = $config->getHTMLDefinition(true);
        $def->info_tag_transform['b'] = new \HTMLPurifier_TagTransform_Simple('strong');
        $def->info_tag_transform['i'] = new \HTMLPurifier_TagTransform_Simple('em');
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        $this->purifier = new \HTMLPurifier($config);
    }
    
    /* A clean method that turns multiple tabs and spaces into a single space and trims whitespace from the ends.
     * @param $html: string, HTML text
     * @return: string with whitespace trimmed and multiple spaces singled.
     */
    protected function clean($html) {
        return trim((preg_replace("/\s+/", " ", $html)));
    }
    
    /* A filter function that purifies the HTML content for upload to the database
     * @param $html: string, HTML text
     * @return: string, purified HTML
     */
    public function filter($html) {
        $html = $this->clean($html);
        return $this->purifier->purify($html);
    }

    /* A display function that purifies the HTML content display, just in case illegal content has been injected somehow
     * @param $html: string, HTML
     * @return: string, purified HTML
     */
    public function display($html) {
        return $this->purifier->purify($html);
    }
}
?>
