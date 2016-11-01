<?php
namespace Kobalt\SimpleBase;
/* A generalized JSON view that takes output from the controller, 
 * converts it to JSON, and renders it out
 */
class JSONView extends View {
    
    /* The rendering method
     * @param $output: array
     * @param $code: The response code from the controller
     * @param $label: The response label from the controller
     * @return: boolean
     */
    public function render() {
        // Remove header just in case
        header_remove();
        http_response_code($this->code);
        if (!empty($this->output)) {
            // headers for not caching the results, if necessary
            // header('Cache-Control: no-cache, must-revalidate');    
            header('Content-Type: application/json; charset=utf8');
            echo json_encode($this->output);
        }
    }
}
