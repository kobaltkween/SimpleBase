<?php
namespace Kobalt\SimpleBase;
/* A request generator for testing an api
 * Makes GET, POST, PUT, and DELETE requests
 *
 */
class RequestGenerator {
    /* The cURL handler
     * @var resource
     */
    protected $ch;
    
    /* The URL being accessed
     * NOTE: If the url is a directory, it should end with a "/"
     * @var string, 
     */
    public $url;
    
    /* The data that the URL returns
     * @var string: might be in json form, might be in HTML, etc.
     */
    public $response;

    /* Open the connection to the URL
     * @param $url: string, the URL you want to access
     * NOTE: 
     */
    function __construct($url) {
        $this->url = $url;
        $this->open();
    }
    
    /* Open the connection this generator's URL
     * Called by the constructor
     * @return: void
     */
    public function open() {
        $this->ch = curl_init($this->url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->ch, CURLOPT_VERBOSE, true);

    }
    
    /* Get the response from the request
     * @param $errorMessage: string, the message to return if there's an error
     * @return: string
     * @throws: \Exception
     */
    protected function getRes($errorMessage) {
        $this->response =curl_exec($this->ch);
        if ($this->response === false) {
            throw new \Exception($errorMessage . ".  " . curl_error($this->ch));
        }
        return $this->response;
    }

    /* Make a GET request
     * @param $data: array, allows you to add data to the GET request via an associative array
     * @param $sep: string, a separator for the data.  
     * If the separator is "?", it adds a query string to the URL
     * If the separator is "/", it builds a "smart URL" by adding directories to the request
     * url?key1=value1&key2=value2  OR  url/key1/value1/key2/value2/
     * @return: string, the response property
     * @throws: \Exception (from the getRes method)
     */
    public function get($data = [], $sep = "") {
        if(!empty($data)) {
            if ($sep == "") {
                $queryURL = http_build_query($data);
                $this->url = $this->url . "?" . $queryURL;
            } if ($sep == "/") {
                $queryURL = "";
                foreach ($data as $k=>$v) {
                    $queryURL .=  "$k/$v/";
                }
                $this->url = $this->url . $queryURL;
            }
            // Close existing curl handle
            $this->close();
            // Open a new one with the new URL
            $this->open();
        }
        return $this->getRes("Could not execute request");
    }
    
    /* Make a POST request
     * @param $data: array, an associative array of data
     * @return: string, the response property
     * @throws: \Exception (from the getRes method)
     */
    public function post($data) {
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return $this->getRes("Could not post data");
    }
    
    /* Make a PUT request
     * @param $data: array, an associative array of data
     * @return: string, the response property
     * @throws: \Exception (from the getRes method)
     */
    public function put($data) {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return $this->getRes("Could not update data");
    }
    
    /* Make a DELETE request
     * @param $data: array, an associative array of data
     * @return: string, the response property
     * @throws: \Exception (from the getRes method)
     */
    public function delete($data) {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        return $this->getRes("Could not delete record");
    }
    
    /* Closes the connection to the URL
     * @return: void
     */
    public function close() {
        curl_close($this->ch);
    }

}
