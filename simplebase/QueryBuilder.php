<?php 
namespace Kobalt\SimpleBase;
/* The query builder class.  Allows for modular query building, so that queries can be built programmatically.
 * The DBManager class uses this class to execute standard queries
 */
class QueryBuilder{
    /* The SQL query built
     * @var string
     */
    public $sql = "";
    
    /* The main table the query is acting on 
     * @var DbTable
     */
    private $table;
    
    /* The type of query performed
     * Right now, supports INSERT, UPDATE, DELETE, SELECT
     * @var string
     */
    private $cmd;
    
    /* The "FROM ..." part of a SELECT query that needs to be broken off for joins
     * @var string
     */
    private $from = "";
    
    /* The join statement for the query 
     * Need to put off adding in the join until aliasing is all done
     * @var string
     */
    private $join = "";
    
    /* The columns in a query
     * Is set to * if none are given
     * @var array
     */
    public $cols = [];
    
    /* The values for an insert or update
     * @var array
     */
    public $vals = [];
    
    /* The parameters for an insert or update
     * Entries match the $vals array
     * @var array
     */
    public $params = [];
    
    /* The type of the values in the $vals array
     * Entries match the $vals and $params arrays
     * @var array
     */
    public $types = [];
   
    /* Begins building the query
     * @param $cmd: string, what type of query, can be insert, update, delete or select
     * @param $table: DbTable, the main table the query acts on 
     * @param $cols: string, OPTIONAL, a comma and space separated list of columns to act on
     */
    function __construct($cmd, $table, $cols = "*") {
        $this->cmd = strtoupper($cmd);
        $this->table = $table;
        switch ($this->cmd) {

            case "INSERT":
                $this->sql .= "$this->cmd INTO ". $this->table->name;
                break;

            case "UPDATE":
                $this->sql .= $this->cmd . " " . $this->table->name;
                break;

            case "DELETE":
                $this->sql .= "$this->cmd FROM " . $this->table->name;
                break;

            case "SELECT":
                $this->sql .= "$this->cmd $cols FROM " . $this->table->name;
                break;
        }

    }
    
    /* A function to clean column names and turn them into parameters
     * It removes everything but letters
     * @param $col: string, a column name
     * @return: string, a parameter name
     */
    private function colConvert($col) {
        return ":" . preg_replace("/[^a-zA-Z]/", "", $col);
    }

    /* A function to set the parameter type for each parameter
     * Only need two types of parameter, since there's no float and boolean works as 0 or 1
     * @param $val: a value to use with prepared statements
     * @return: int, a \PDO constant setting the type of parameter a value should get
     */
    private function setParamType($val) {
        if (is_int($val)) {
            return \PDO::PARAM_INT;
        } else {
            return \PDO::PARAM_STR;
        }
    }

    /* Add a set of values to the query, only needed for INSERTs and UPDATEs
     * @param $colVar: array, an associative array such that the keys are columns and values are values
     * @return: void
     */
    public function addVals($colVar) {
        $this->cols = array_keys($colVar);
        $this->vals = array_values($colVar);
        foreach($this->cols as $col) {
            $this->params[] = $this->colConvert($col);
            $this->types[] = $this->setParamType($colVar[$col]);
        }
    }

    /* Add the end of an INSERT query
     * Assumes all columns and their values have been added
     * @return: void
     */
    public function addInsert() {
        if(count($this->cols) != 0) {
            // Only use the parameters that match with columns
            $params = array_slice($this->params, 0, count($this->cols));
            $this->sql .= " (" . implode(", ", $this->cols) . ")";
            $this->sql .= " VALUES (" . implode(", ", $this->params) . ")";
        }
    }

    /* Adds the end of an UPDATE query
     * Assumes all columns and their values have been added
     * @param $id: int, the key of the row to be updated
     * @return: void
     */
    public function addUpdate($id) {
        if(count($this->cols) != 0) {
            $i = 0;
            $this->sql .= " SET";
            while ($i < count($this->cols)) {
                $this->sql .= " " . $this->cols[$i]. " = " . $this->params[$i];
                if ($i > count($this->cols) - 1) {
                    $this->sql .= ",";
                }
                $i++;
            }
            $this->addCond($this->table->id . " = :id", [$id]);
        }
    }
    
    /* Add a join for SELECT queries
     * It only adds columns in the associated table that have aliases
     * @param $assocTable: DbTable, the table to be joined with this query's primary table
     * @param $col1: string, the column of the primary table used as a constraint
     * @param $col2: string, the column of the associated table to match the column constraint
     * @param $last: boolean, OPTIONAL, whether this is the last join in the query or not
     * @param $type: string, OPTIONAL, the type of join, defaults to "left"
     * @return: void
     */
    public function addJoin($assocTable, $col1, $col2, $last = true, $type = "left") {
        // Joins will always be on selects
        list($this->sql, $this->from) = splitString($this->sql, "FROM");
        // Take care of aliased columns
        if (!empty($assocTable->aliases)) {
            $this->sql .= ", ";
            foreach($assocTable->aliases as $c => $a) {
                $this->sql .= $assocTable->name . ".$c as $a,";
            }
            // Take the comma off the end
            $this->sql = trim($this->sql, ",");
        }
        $type = strtoupper($type);
        $this->join .= " $type JOIN " . $assocTable->name;
        $this->join .= " ON " . $this->table->name . "." . $col1 . " = " . $assocTable->name . "." . $col2;
        if ($last) {
            $this->sql .= " $this->from";
            $this->sql .= $this->join;
        }
    }

    /* Add SELECT conditions all at once as a string, and values as an array
     * Build out parameters for prepared statements
     * @param $cond: string, the condition(s) as a string, with :param style parameters
     * @param $vals: array, the values that should go where the parameters are
     * @return: void
     */
    public function addCond($cond, $vals) {
        $this->sql .= " WHERE $cond";
        // Parse the parameters out of the condition string
        preg_match_all("/(?<!\w):\w+/", $cond, $params);
        $param = $params[0];
        $i = 0;
        while($i < count($param)) {
            $this->params[] = $param[$i];
            $this->cols[] = null;  // Would break an INSERT or UPDATE query
            $this->types[] = $this->setParamType($vals[$i]);
            $this->vals[] = $vals[$i];
            $i++;
        }
    }

    /* Add ordering to a SELECT query
     * @param $ord: string, one or more comma separated ordering conditions
     * @return: void
     */
    public function addOrder($ord) {
        $this->sql .= " ORDER BY $ord";
    }
    
    /* Add limits to a SELECT query
     * @param $amt: int, the maximum number of records to return
     * @param $offset: int, the number of records to start counting from
     * @return: void
     */
    public function addLimit($amt, $offset = "0") {
        $this->sql .= " LIMIT $offset, $amt";
    }
    
    /* Bind all the parameters in a query to a DbManager PDO statement
     * @param $dbm: DbManager
     * @return: void
     */
    public function bindAll($dbm) {
        $dbm->stmt = $dbm->con->prepare($this->sql);
        $i = 0;
        while ($i < count($this->params)) {
            $dbm->stmt->bindParam($this->params[$i], $this->vals[$i], $this->types[$i]);
            $i++;
        }
    }
}
