<?php
class TextArea extends TextInput {
  function __construct($name, $req = false) {
    $this->name = $name;
    $this->req = $req;
    $this->hint = $hint;
  }

  public function display() {
    // Open the div for the input and its label
    $this->display = "<div class=\"form-group\">\n";
    // Set whether or not it needs the required class
    $reqClass = ($this->req) ? " req"  : "";
    $reqAttr = ($this->req) ? "required" : "";
    // Create the label for the input
    $this->display .= "<label for=\"$name\" class=\"control-label"
                      . $reqClass . "\">" . ucwords($this->name) . "</label>\n";
    // Add a hint if need to
    if ($this->hint != "") {
      $this->display .= "<p class=\"hint\">$this->hint</p>";
    }
    $errorClass = ($this->error) ? " error" : "";
    // Set the textarea
    $this->display.= "<textarea class=\"form-control" . $errorClass
                      . "\" rows=\"8\" wrap=\"soft\" id=\"mess\" name=\"$this->name\" $reqAttr></textarea>\n";
    // Close the div for the input and its label
    $this->display .= "</div>";
    echo $this->display;
  }
}
