<?php
namespace Kobalt\SimpleBase;
/* A generalized JSON view that takes output from the controller, 
 * converts it to JSON, and renders it out
 */
class JSONView extends View {
    
    /* Render view's data out to JSON
     */
    public function render() {
        // Remove header just in case
        header_remove();
        http_response_code($this->code);
        if (!empty($this->data)) {
            // headers for not caching the results, if necessary
            // header('Cache-Control: no-cache, must-revalidate');    
            header('Content-Type: application/json; charset=utf8');
            echo json_encode($this->data);
        }
    }
}
