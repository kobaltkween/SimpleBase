<?php
namespace Kobalt\SimpleBase;
/* An abstract model class 
 * Your application should subclass this and make it suit your database tables
 */
abstract class Model {
    /* The table that the model is based on
     * Has the same name as the model
     * @var DbTable
     */
    public $table;
    
    /* The data that it gets from the user, and makes into properties
     * @var array
     */
    protected $input;
     
     /* The data that it gets from the database, and sends back to the controller
      * @var obj or array (single result is an object, multiple is an array)
      */
    protected $output;
    
    /* The columns of the database table
     * @var array
     */
    protected $columns;
     
     /* An array of the names of the "children" this model has
      * For instance, a Post model might have Tag, Image, and Ad as a child
      * @var array
      */
    protected $children = array();
    
    /* The condition to put on request from the controller, should use parameters like :param
     * @var string
     */
     protected $cond = "";
     
     /* The array of values to pass with the condition, 
      * @var array
      */
    protected $condVals = array();
    
    /* The constructor, which sets up the database manager, gets the columns
     */
    public function __construct() {
        // Set the table properties first
        $this->setBase();
        $this->dbm = new DbManager();
        $this->dbm->connect();
        $this->columns = $this->dbm->getCols($this->table);
        $this->dbm->close();
    }
    
    /* A stub for setting tables and children, to be overridden in subclasses
     * @return: void
     */
    protected function setBase() {
        // Set the relevant table properties here
    }
    
    /* A filter input function that's just here to demonstrate how to filter input
     * Specific model subclasses will need to override this to handle particular fields
     * @return: void
     * @throws: FilterExcept (from the filters) or \Exception
     */
    protected function filterInput() {
        foreach ($this->input as $k => $v) {
            if (in_array($k, ["id", "limAmt", "limOffset"]) {
                $v = $this->filterInt($v, $k, false);
                if ($v <= 0 && $k != "limOffset") {
                    $this->invalid($k);
                } 
            } else if ($k == "sort") {
                $v = $this->filterSort($v);
                $this->table->sort = $v;
            } else if ($k == "sortDir") {
                $v = $this->filterSortDir($v);
                $this->table->sortDir = $v;
            } else {
                // Treat everything else as unrequired text
                $v = filterText($v, $k);
            }
            $this->input[$k] = $v;
        }
    }
    
    /* An integer filter method for ids and other integers
     * @param $dirty: int
     * @param $name: string, passing the name for the exception
     * @return: int
     * @throws: FilterExcept (from the filter) or \Exception
     */
    protected function filterInt($dirty, $name, $req = true) {
        if ($dirty !== "" && $req) {
            $intFilter = new IntFilter();
            $clean = $intFilter->filter($dirty);
            return $clean;
        } else if ($dirty == "" && $req) {
            $this->invalid($name);
        } else {
            return "";
        }
    }
    
    /* A text filter method for most regular content.  It just removes illegal content
     * @param $dirty: string
     * @return: string
     */
    protected function filterText($dirty, $name, $req = false) {
        $tFilter = new TextFilter();
        $clean = $tFilter->filter($dirty);
        if ($req && empty($clean)) {
            $this->invalid($name);
        } else {
            return $clean;
        }
    }
    
    /* A specific filter for sorting
     * @param $dirty: string, Sort column
     * @return: string
     * @throws: FilterExcept
     */
    protected function filterSort($dirty) {
        $dirty = strtolower($dirty);
        $sortFilter = new DataFilter();
        $sortFilter->setWhitelist($this->columns);
        $clean = $sortFilter->filter($dirty);
        return $clean;
    }
    
    /* A specific filter for sorting direction
     * @param $dirty: string, Sort column
     * @return: string
     * @throws: FilterExcept
     */
    protected function filterSortDir($dirty) {
        $dirty = strtoupper($dirty);
        $sortFilter = new DataFilter();
        $sortFilter->setWhitelist(["ASC", "DESC");
        $clean = $sortFilter->filter($dirty);
    }
        
    
    /* A function to throw Exceptions when necessary
     * @param $name: string, the name of the invalid value
     * @return: none;
     * @throws: \Exception
     */
     protected function invalid($name) {
        throw new \Exception("Invalid data: $name");
    }
    
    /* Get all of the joined information, add it to output 
     * and set object properties, just in case need to do more 
     * @return: void
     */
    protected function getAssocObjs() {
        if (!empty($this->table->joinTables)) {
            foreach($this->table->joinTables as $table) {
                $model = $table->joinedTable->name;
                $obj = new $model();
                $propName = $obj->table->name . "s";
                // By  default, sort by "name" property
                $this->output[$propName] = $obj->getAllJoined($this->table);
                $this->$propName = $this->output[$propName];
            }
        }
    }
    
    /* Get a single row of data from the table
     * @param $input: array, the data from the controller
     * @return: object, $this->output
     * @throws: FilterExcept from the filtering, DbExcept (if the query fails), or \Exception
     */
    public function getOne($input) {
        // Set the input
        $this->input = $input;
        // Make sure the input is valid - in this case, the id
        $this->filterInput();
        // Make sure the table has been set, or raise an exception
        if ($this->table !== null) {
            if (!$this->dbm->connected) {
                $this->dbm->connect();
            }
            // Get the id
            $id = $this->input["id"];
            if (empty($this->table->fkCols)) {
                $this->output = $this->dbm->getSimple($this->table, $id)
            } else {
                $this->output = $this->dbm->getAssoc($this->table, $id);
            }
            // Get data from the child classes
            if (!empty($this->children)) {
                $this->getChildData($this->output, $id);
            }
            return $this->output;
        } else {
            throw new \Exception("Cannot retrieve data.  Model's table has not been defined.")
        }
    }
    
    /* Get all of the rows in a single table, no joins, no conditions, no limits
     * @return: array, $this->output
     * @throws: FilterExcept from the filtering, DbExcept (if the query fails), or \Exception
     */
    public function getAllSimple() {
        // Make sure the table has been set, or raise an exception
        if ($this->table !== null) {
            if (!$this->dbm->connected) {
                $this->dbm->connect();
            }
            $this->output = $this->dbm->getSimple($this->table);
         } else {
            throw new \Exception("Cannot retrieve data.  Model's table has not been defined.")
        }
    }
    
    /* Get a full list of the rows from the table and its related data, with conditions set by the input
     * @param $input: array, the data from the controller
     * @return: array, $this->output
     * @throws: FilterExcept from the filtering, DbExcept (if the query fails), or \Exception
     */
    public function getAll($input) {
        // Set the input
        $this->input = $input;
        // Make sure the input is valid - in this case, just search conditions
        if (!empty($this->input)) {
            $this->filterInput();
            // Deal with limits, the keys to look for are set in the Router class
            $limAmt =  $this->setValue("limAmt", 0);
            $limOffset = $this->setValue("limOffset", 0);
            // Deal with sorting, the keys to look for are set in the Router class
            $this->table->sort = $this->setValue("sort", $this->table->sort);
            $this->table->sortDir = $this->setValue("sortDir", $this->table->sortDir);
        }
        // Make sure the table has been set, or raise an exception
        if ($this->table !== null) {
            if (!$this->dbm->connected) {
                $this->dbm->connect();
            }
            $this->output = $this->dbm->getAssoc($this->table, 0, $this->cond, $this->condVals, $limAmt, $limOffset);
            // Get data from the child classes
            if (!empty($this->children)) {
                $idCol = $this->table->id;
                foreach($this->output as $obj) {
                    $this->getChildData($obj, $obj->$idCol);
                }
            }
            return $this->output;
        } else {
            throw new \Exception("Cannot retrieve data.  Model's table has not been defined.")
        }
    }

    
    /* A function to get all of the data from the "child" models associated with this one
     * @param $dataHolder: object, the object to add the information to
     * @param $id: int, the in
     * @return: void
     * @throws: DbExcept, if query goes wrong
     */
    protected function getChildData($dataHolder, $id) {
        foreach($this->children as $child => $joinTable) {
            $childObj = new $child();
            $prop = strtolower($child) . "s";
            $dataHolder->$prop = $childObj->getJoined($joinTable, $this->table, $id);
        }
    }
    
    /* A method to let other models get all child data back from this model
     * For instance, in a Tag subclass, a Post subclass would call this method to get all tags back for a post
     * @param $joinTable: DbTable, the table joining this model's table and the table of the requester
     * @param $otherTable: DbTable, the table of the requesting model
     * @param $id: int, a key to the row in the table of the requesting model acting as a constraint
     * @return: void
     * @throws: DbExcept, if query goes wrong
     */
    public function getJoined($joinTable, $otherTable, $id) {
        if (!$this->dbm->connected) {
            $this->dbm->connect();
        }
        $res = $this->dbm->getJoined($this->table, $joinTable, $otherTable, $id);
        $this->dbm->close();
    }    
    
    /* A simple method to check if a value exists in the input array, and to return it if it does
     * @param $key: string, the name of the key 
     * @param $default: mixed, the value to return if the key doesn't exist
     * @return: mixed, whatever value is in the input or the default
     */
    protected function setValue($key, $default) {
        return (array_key_exists($key, $this->input)) ? $this->input[$key] : $default;
    }
    
    /* A method to create a new record in the table
     * @param $input: array, data from the controller
     * @return: int, the primary key of the row just added
     * @throws: FilterExcept from the filtering, DbExcept (if the query fails), or \Exception
     */
    public function insert($input) {
        // Set the input
        $this->input = $input;
        // Filter the input
        $this->filterInput();
        // Make sure that there's no id
        if (!$this->dbm->connected) {
            $this->dbm->connect();
        }
        if (array_key_exists("id", $this->input)) {
            $id = $this->input["id"];
            $this->output = $this->dbm->insertRow("update", $this->table, $this->input, $id);
            if (!empty($this->children)) {
                foreach($this->children as $child => $table) {
                    // Just delete existing joins first for simplicity
                    $thisFK = array_search($this->table, $table->fkCols)
                    $cond  = "$thisFK = :id";
                    $res = $this->dbm->deleteRows($table, $id, $cond);
                    if ($res) {
                        // Add new joins
                        // Array key in input should be the same as the model name
                        $this->input[$child][$thisFK] = $this->input[$id];
                        $newJoins = $this->dbm->insertRows($table, $this->input[$child]);
                    } else {
                        throw new \Exception("Could not delete previous joins");
                    }                    
                }
        } else {
            $this->output = $this->dbm->insertRow("insert", $this->table, $this->input);
        }
    }
}

?>
