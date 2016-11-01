<?php
class TextInput {
  public $name;
  public $type;
  public $req;
  public $hint = "";
  public $error = false;
  public $placeholder = "";
  public $val = null;
  protected $display;

  function __construct($name, $type = "text", $req = false, $ph = "") {
    $this->name = $name;
    $this->type = $type;
    $this->req = $req;
    $this->placeholder = $ph;
  }

  public function addError($hint = "") {
    $this->hint = $hint;
    $this->error = true;
  }

  public function display() {
    // Open the div for the input and its label
    $this->display = "<div class=\"form-group\">\n";
    // Set whether or not it needs the required class
    $reqClass = ($this->req) ? " req"  : "";
    $reqAttr = ($this-req) ? "required" : "";
    // Create the label for the input
    $this->display .= "<label for=\"$name\" class=\"control-label" . $reqClass . "\">" . ucwords($this->name) . "</label>\n";
    // Add a hint if need to
    if ($this->hint != "") {
      $this->display .= "<p class=\"hint\">$this->hint</p>";
    }
    // Give it a placeholder if there is one
    $ph = ($this->placeholder != "") ? "" : " placeholder=\"$this->placeholder\"";
    $errorClass = ($this->error) ? " error" : "";
    // Set the input
    $this->display.= "<input type=\"$this->type\" class=\"form-control" . $errorClass . "\""
                      . " id=\"$this->name\" name=\"$this->name\"". $ph . " value=\"$val\" $reqAttr>\n";
    // Close the div for the input and its label
    $this->display .= "</div>";
    echo $this->display;
  }
}
