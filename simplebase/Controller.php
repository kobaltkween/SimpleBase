<?php
namespace Kobalt\SimpleBase;
/* A generic controller that takes in
 * a smart URL such that 
 *      a number at the end is always the ID of the model
 *      the word before an id is always the model
 *      a single word is always the model
 * a request method 
 * NOTE: Since browsers can only send GET (Read) and POST (Create) methods, 
 * they should use a parameter named "reqMethod" to identify PUT (Update) and DELETE requests.
 * (optional) JSON or form data
 * and builds a model from that
 * NOTE: This base controller has no support for GET variables, relying only on smart URLs
 */
class Controller {
    
    /* The name of particular model that the controller will send its parameters to
     * @var string
     */
    public $modelName;
    
    /* The model generated from the model's name
     * @var object
     */
    public $model; 
    
    /* Array of possible models
     * @var array
     */
    protected $models;
    
    /* The name of the view to use
     * @var string
     */
    public $viewName;
    
    /* The view generated from the view's name
     * @var object
     */
     public $view;
    
    /* One of the four values that the method of the request can have: 
     * GET, POST, PUT, or DELETE
     * @var string
     */
    public $method;
    
    /* The name of the method to call on the model.
     * Set by the setAction method, which should be customized in subclasses
     * @var string
     */
     public $action = null;
    
    /* An array of data sent to the controller 
     * @var array
     */
    public $params;
    
    /* Whether the view is speaking HTML or JSON
     * @var string
     */
     public $format
     
    /* An error message, that describes what has gone wrong
     * @var string
     */
     public $errorMessage = "";
    
    /* An error code that helps say what has gone wrong
     * @var int
     */
    public $responseCode;
    
    /* What the response code means
     * @var string
     */
     public $responseLabel;
     
     
    /* The data to send back as a response
     * @var string
     */
     public $output = null;
     
    /* The view that the controller sends the response to
     * @var View
     */
     public $view;
     
     

    /* Set the basic properties of the controller based on the request
     * @param $models: array, a white list of acceptable model names
     */
    public function __construct($router) {
        $this->urlElements = $router->urlElements;
        // Set the format, it's only HTML if it's local, JSON if it's not
        $this->format = ($router->local) ? "html" : "json";
        try {
            $this->getInput($router->getVars);
            $this->setMethod($router->method);
            $this->setMV($router->dbm);
            $this->setAction();
            $this->setOutput();
        } catch (\Exception $e) {
            // Catch any type of exception raised
            $this->errorMessage = $e->getMessage();
        }
    }
    
    /* Set the parameters to those sent in the body of the request
     * @return: void
     * @throws: \Exception 
     */
    protected function getInput($getVars) {
        $parameters = $getVars;
        $contentType =  ($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : NULL;
        if ($contentType) {
            switch($contentType) {
                case "application/json":
                    $body = file_get_contents("php://input");
                    $bodyParams = json_decode($body);
                    if ($bodyParams) {
                      foreach($bodyParams as $k => $v) {
                        $parameters[$k] = $v;
                      }
                    }
                    break;
                case "application/x-www-form-urlencoded":
                    foreach($_POST as $k => $v) {
                      $parameters[$k] = $v;
                    }
                    break;
                case "multipart/form-data":
                    foreach($_POST as $k => $v) {
                      $parameters[$k] = $v;
                    }
                    $parameters["files"] = $_FILES;
                    break;
                default:
                    $this->notAccepted();
            }
        }
        $this->params = $parameters;
    }
    
    /* Parses model name out of an URL element, 
     * for instance "Product" out of "products"
     * NOTE: Assumes a relationship such that the endpoint name is plural and ends in "s",
     * but the model name is singular and title cased
     * @param $element: string
     * @param $trim: boolean, OPTIONAL, whether or not to remove the "s" at the end, defaults to yes
     * @return: string - the model name
     */
    protected function getModelName($element, $trim = true) {
        if ($trim) {
            $element = rtrim($element, "s");
        }
        return ucwords($element);
    }
    
    /* Sets the method based on the request method fed to the controller
     * and (for views in browsers) a parameter named "reqMethod"
     * @return void
     */
    protected function setMethod($reqMethod) {
        if ($reqMethod == "GET" || $reqMethod == "PUT" || $reqMethod == "DELETE") {
            $this->method = $reqMethod;
        } else if ($reqMethod == "POST") {
            if (array_key_exists("reqMethod", $this->params)) {
                if (strtoupper($this->params["reqMethod"]) == "DELETE") {
                    $this->method = "DELETE";
                } else {
                    $this->method = "PUT";
                }
            } else {
                $this->method = $reqMethod;
            }
        }
    }
    
    /* A simple function for figuring out the model and the view based on the smart URL
     * This method is dependent on the application API and its endpoints
     * This method should work for a basic API, but will probably need to be overrriden by subclasses
     * @param: DbManager
     * @return: void
     * @throws: \Exception
     */
    protected function setMV($dbm) {
        // Set the possible/allowed models for this controller
        $this->models = ["Image", "Page"];  // Just a placeholder
        // If there's only one element in the URL, get a list of that model
        if (count($this->urlElements) == 1) {
            $modelName = $this->getModelName($this->urlElements[0]);
        } else {
            // Assume that if the last item in the URL is a number, that's the id
            $end = end($this->urlElements);
            if (is_numeric($end)) {
                $this->params["id"] = (int)$end;
                // Assume the item preceeding the ID is the model
                $modelName = $this->getModelName(prev($this->urlElements));
                $viewSuffix = "ViewOne";
            } else {
                $modelName  = $end;
                $viewSuffix = "ViewAll";
            }
        }
        if (in_array(strtolower($modelName), $this->models)) {
            $this->modelName = $modelName;
            $this->model = new $this->modelName($this->router->dbm);
            if ($this->format == "html") {
                $this->viewName = $modelName . $viewSuffix;
            } else {
                $this->viewName = "JSONView";
            }
        } else {
            $this->badRequest();
        }
    }
    
    /* A method to set the action based on the request method and whether or not there's an ID
     * It just covers the CRUD actions, and assumes method names based on the base Model class
     * This method is very likely to be overriden by subclasses as well
     * @return: void
     * @throws: \Exception
     */
    public function setAction() {
        $haveId = (array_key_exists("id", $this->params) && $this->params["id"] >= 1) ? true : false;
        switch($this->method) {
            case "GET":
                if ($haveId) {
                    $this->action = "getOne";
                } else {
                    $this->action = "getAll";
                }
                break;
            case "POST":
            case "UPDATE":
                $this->action = "insert";
                break;
            case "DELETE":
                if ($haveId) {
                    $this->action = "delete";
                } else {
                    $this->notAllowed();
                }
                break;
            default:
                $this->notAllowed();
        
        }
    }
    
    
    
    /* Method that calls the method's action and gets results
     * As with the setModel and setAction methods, you will probably need to 
     * override this in subclasses to fit the specific needs of an app
     * and its models.
     * @return: void
     * @throws: FilterExcept, DbExcept, or \Exception
     */
    protected function setOutput() {
        if ($this->action !== null) {
            $method = $this->action;
            $this->output = $this->model->$method($this->params);
            if ($this->method == "GET") {
                if (empty($this->output)) {
                    if (array_key_exists("id", $this->params)) {
                        $this->notFound();
                    } else {
                        // Asked for a full listing, and got no results
                        $this->noContent();
                    }
                } else {
                    // Got results from a get request
                    $this->success();
                }
            } else if ($this->method == "POST") {
                // Successfully entered new row(s) in the database
                $this->created();
            } else {
                $this->noContent();
            }
        } else {
            $this->notAllowed();
        }
    }
    
    /* Call the appropriate view - Definitely needs to be overriden
     * JSON has one view - turns the database response to JSON
     * HTML has multiple views, based on the model name
     * @return: View, the view made from the controller
     */
    public function respond() {
        $view = $this->viewName;
        $this->view = new $view($this->output, $this->responseCode, $this->responseLabel, $this->errorMessage);
        return $this->view;
    }
    /* Method for successful content request
     * @return: void
     */
    protected function success() {
        $this->responseCode = 200;
        $this->responseLabel = "OK";
    }
    
    /* Method for successful creation
     * @return: void
     */
    protected function created() {
        $this->responseCode = 201;
        $this->responseLabel = "Created";
    }
    
     /* Method for no results
     * @return: void
     */
    protected function noContent() {
        $this->responseCode = 204;
        $this->responseLabel = "No Content";
    }
    
    /* Method for response when executing the action has raised an exception (for instance, illegal input)
     * @return: void
     * @throws: \Exception
     */
    protected function badRequest() {
        $this->responseCode = 400;
        $this->responseLabel = "Bad request";
        throw new \Exception("Invalid request");
    }
    
    /* Method for response when the user isn't authenticated
     * @return: void
     * @throws: \Exception
     */
    protected function notAuthenticated() {
        $this->responseCode = 401;
        $this->responseLabel = "Unauthorized Request";
        throw new \Exception("You need to login to access that resource.");
    }
    
    /* Method for response when the user is authenticated but doesn't have necessary permissions
     * @return: void
     * @throws: \Exception
     */
    protected function notPermitted() {
        $this->responseCode = 403;
        $this->responseLabel = "Forbidden Request";
        throw new \Exception("You don't have permission to access the requested resource.");
    }
    
    /* Method for response when there's no resource found at the ID
     * @return: void
     * @throws: \Exception
     */
    protected function notFound() {
        $this->responseCode = 404;
        $this->responseLabel = "Not Found";
        throw new \Exception("Resource not found. Please check the URL.");
    }
    
    /* Method for response when the user tries something that isn't allowed
     * @return: void
     * @throws: \Exception
     */
    protected function notAllowed() {
        $this->responseCode = 405;
        $this->responseLabel = "Method Not Allowed";
        throw new \Exception("Your request is not allowed by this application.");
    }
    
    /* Method for response when the user sends a content type that isn't allowed
     * @return: void
     * @throws: \Exception
     */
    protected function notAccepted() {
        $this->responseCode = 406;
        $this->responseLabel = "Not Acceptable";
        throw new \Exception("Your request isn't in an acceptable format.");
    }
}
?>
