<?php
namespace Kobalt\SimpleBase;
/* The database management class.  This connects to your database, executes queries,
 * and manages the results.  
 * Models use this class together with the DbTable class to talk to the database and fill their data.
 * It uses the QueryBuilder class to dynamically build queries, so that conditions are optional
 * If there are types of queries you make repeatedly that aren't includeed here, you can add them to subclasses
 * The class includes methods for
 *      * Getting one or more rows of data from a table
 *      * Getting one or more rows of data from a main table and the data 
 *        from all tables that have foreign keys in the main table
 *      * Getting all the rows of data from a "main" table connected to another table by a single join table
 *      * Inserting or updating a single row of data into a single table
 *      * Inserting multiple rows of data into a single table
 *      * Getting all of the column names in a table
 */
class DbManager {
    /* The user name to access the database
     * @var string
     */
    private $username;
    
    /* The password for accessing the database
     * @var string
     */
    private $password;
    
    /* The host address for connecting to the database
     * @var string
     */
    private $host;
    
    /* The name of the database for this application
     * @var string
     */
    private $db;
    
    /* The connection to the database
     * @var \PDO
     */
    public $con;
    
    /* Statement for binding properties
     * @var \PDOStatement
     */
    public $stmt = null;  // PDO statement
    
    /* Text for feedback when testing
     * @var string
     */
    public $message;
    
    /* The query after it's been built, for viewing when testing
     * @var string
     */
    public $query = "";
    
    /* Whether or not the connection is open
     * @var boolean
     */
    public $connected;
    
    /* Whether or not the database has been initialized
     * @var boolean
     */
    public static $dbInitialized;
    
    /* Whether or not the initial data has been loaded
     * @var boolean
     */
    public static $dbLoaded;
    
    /* The constructor which initializes the database connection settings
     */
    function __construct() {
        /*Set the connection properties to globals,
         * which should be set by a config file
         * located outside of the public_html folder
         */
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->host = DB_HOST;
        $this->db = DB_NAME;
        $this->connected = false;
        $this->message = "";
        $this->charset = "utf8";
    }

    /* Establishing a connection to the database
     * @return: void
     */
    public function connect() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $opt = [
                            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                            \PDO::ATTR_EMULATE_PREPARES   => false,
                    ];
        try {
            $this->con = new \PDO($dsn, $this->username, $this->password, $opt);
        } catch (\PDOException $e) {
            throw new DbExcept("Database connection failed. " . $e->getMessage() . "<br>");
        }
    }
    
    /* See if the database has been initialized, based on the creation of a table
     * @param $tableName: string, the name of a table that should be created
     * @return: void
     */
    public function checkDBInit($tableName) {
        $this->query = "SHOW TABLES LIKE $tableName";
        $res = $this->con->query($this->query);
        self::$dbInitialized = (empty($res)) ? false : true;
    }
    
    /* See if the database has been filled with initial data
     * @param $tableName: string, the name of the table that should have data in it
     * @return: void;
     */
    public function checkDBLoad($tableName) {
        $this->query = "SELECT * FROM $tableName";
        $res = $this->con->query($this-query);
        self::$dbLoaded = (empty($res)) ? false: true;
    }
    
    /* Get the columns in a table 
     * @param $table, DbTable
     * @return: array
     */
    public function getCols($table) {
        $this->query = "DESCRIBE " . $table->name;
        $this->stmt = $this->con->prepare($this->query);
        $this->stmt->execute();
        return $this->stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /* Execute a selection query that's already been built
     * Takes a QueryBuilder object
     * Returns the result of the query
     */
    protected function getReq($qb) {
        // Set the query, so can view it for testing
        $this->query = $qb->sql;
        $qb->bindAll($this);
        $this->stmt->execute();
        while ($row = $this->stmt->fetch(PDO::FETCH_OBJ)) {
            $res[] = $row;
        }
        return $res;
    }
    
    /* Get one or more rows of data
     * @param $table: DbTable, the table the data is coming from
     * @param $id: int, OPTIONAL, a positive integer that is a key for the row
     * @return: obj or array of objects
     */
    public function getSimple($table, $id = 0) {
        $qb = new QueryBuilder("select", $table);
        if ($id == 0) {
            $qb->addCond("$table->id = :id", [$id]);
            $res = $this->getReq($qb)[0];
        } else {
            $res = $this->getReq($qb);
        }
        return $res;
    }

    /* Get one or more rows of from a main table and its associated content
     * @param $table: DbTable with it's fkCols property set
     * @param $id: int, OPTIONAL, a positive integer to get back a specific row
     * @param $cond: string, OPTIONAL, a string setting a condition with :valName placeholders, 
     *               For example, "date >= :today", requires $vals argument
     * @param $vals: array, OPTIONAL, an array of values to set the parameters in the condition to
     * @return: obj or array, a single object if getting a row, an array of objects if getting multiple rows
     */
    public function getAssoc($table, $id = 0, $cond = "", $vals = [], $amt = 0, $offset = 0) {
        $qb = new QueryBuilder("select", $table, $table->name . ".*");
        if (!empty($table->fkCols)){
            foreach($table->fkCols as $col => $t) {
                if (next($table->fkCols) === false) {
                    $qb->addJoin($t, $col, $t->id);
                } else {
                    $qb->addJoin($t, $col, $t->id, false);
                }
            }
        }
        if ($id > 0) {
            $qb->addCond($table->name . "." . $table->id . " = :id", $id);
            $res = $this->getReq($qb)[0];
        } else {
            if ($cond != "") {
                $qb->addCond($cond, $vals);
            }
            if ($table->sort != null {
                $qb->addOrder("$table->sort $table->sortDir");
            }
            if ($amt > 0) {
                $qb->addLimit($amt, $offset);
            }
            $res = $this->getReq($qb);
        }
        return $res;
    }
    
    /* Get all the items from a table associated by one many to many join
     * This could be used to get all the tags associated with an post, or all
     * the posts associated with a tag.
     * If the main table doesn't have any display columns, the query will use
     * "*" instead of particular columns.
     * @param $mainTable: DbTable, the table the join table links to
     * @param $joinTable: DbTable, the table with the joins, should have the foreign key links set
     * @param $otherTable: DbTable, the other table joined with the main table, the source of the constraint
     * @param $otherID: int, the key of the row constraining the query   
     * @return: array, an array of anonymous objects
     * @throws: DbExcept
     */
    public function getJoined($mainTable, $joinTable, $otherTable, $otherID) {
        $cols = [];
        $colString = "";
        if (empty($mainTable->displayCols)) {
            $colString = $mainTable->name . ".*";
        } else {
            foreach ($mainTable->displayCols as $col) {
                $cols[] = $mainTable->name . ".$col";
            }
        }
        if (!empty($joinTable->displayCols)) {
            foreach ($joinTable->displayCols as $col) {
                $cols[] = $joinTable->name . ".$col";
            }
            if ($colString != "") {
                $colString .= ", ";
            }
            $colString .= implode(", ", $cols);
        }
        $qb = new QueryBuilder("select", $mainTable, $colString);
        $mainFKCol = array_search($mainTable, $joinTable->fkCols);
        $otherFKCol = array_search($otherTable, $joinTable->fkCols);
        if ($otherFKCol === false) {
            throw DbExcept("Could not find relationship between $otherTable->name and $joinTable->name.");
        }
        
        $qb->addJoin($joinTable, $mainTable->id, $mainFKCol, true, "inner");
        $qb->addCond($joinTable->name . "." . $otherFKCol . " = :id", [$otherID]);
        $order = $mainTable->name . "." .$mainTable->sort . " " $mainTable->sortDir;
        $qb->addOrder($order);
        return $this->getReq($qb);
    }
        
    /* Simple one table insertion or update of data
     * @param $cmd: string, either "insert" or "update"
     * @param $table: DbTable
     * @param $dataArr: array, the array of data to put in the row
     * @param $id: int, 0 if this is an insert, should be a value greater than 0 if updating
     * @return: int, the ID of the updated or inserted row
     */
    public function insertRow($cmd, $table, $dataArr, $id = 0) {
        // Make a new query builder
        $qb = new QueryBuilder($cmd, $table);
        // Add the columns and string values
        $qb->addVals($dataArr);
        // Cast the $id just in case it's not the right type
        $id = (int)$id;
        if ($cmd == "insert") {
            $qb->addInsert();
        } else if ($cmd == "update" && $id > 0) {
            $qb->addUpdate($id);
        } else {
            throw new DbExcept("Invalid id $id.  Cannot update.");
        }
        // Set the query so you can check on it
        $this->query = $qb->sql;
        $qb->bindAll($this);
        if ($this->stmt->execute()) {
            return $this->con->lastInsertId();
        } else {
            throw new DbExcept("Could not execute $cmd.");
        }
    }

    /* Insert several rows of data
     * @param $table: DbTable, the table to add the data to
     * @param $rows: an array of arrays of associative data
     * @return: array, array of indicies
     * @throws: DbExcept, from insertData method
     */
     public function insertRows($table, $rows) {
        $ids = [];
        foreach ($rows as $row) {
            $ids[] =  $this->insertData("insert", $table, $row);
        }
        return $ids;
    }

    /* Delete one or more rows of data from a table
     * @param $table: DbTable, the table to delete records from
     * @param $vals: int, string, or array, the id or other values to constrain the deletion
     * NOTE: if the condition is an integer that isn't an id, it should be put in an array
     * @param $cond: string, a condition with parameter values like :param instead of values
     * @return: boolean, Returns either true or false, depending on whether the delete fails.
     * @throws: DbExcept
     */
    public function deleteRows($table, $vals, $cond = "") {
        // Make a new query builder
        $qb = new QueryBuilder("delete", $table);
        if (is_int($vals)) {
            if ($vals <= 0 && $cond == "") {
                throw new DbExcept("Invalid id.  Cannot delete record. <br>");
            } else {
                $vals = [$vals];
            }
        } else if (is_string($vals) || is_float($vals)) {
            $vals = [$vals];
        } else if (!is_array($vals)) {
            throw new DbExcept("Please provide an array of values, an integer, or a string.<br>");
        }
        if ($cond == "") {
            $cond = $table->id . " = :id";
        }
        $qb->addCond($cond, $vals);
        // Set the query so you can check on it
        $this->query = $qb->sql;
        $qb->bindAll($this);
        return $this->stmt->execute();
    }    
    
    /* Delete table if it exists
     * @param $tableName: string, one or more comma separated names of database tables
     * @return: void
     */
    public function delTable($tableName) {
        $this->con->exec("DROP TABLE IF EXISTS $tableName");
    }

    /* Disconnect from the db
     * Should do this only when finished dealing with the database
     * Connections are "expensive" so should only close when finished with operations
     * @return: void
     */
    public function close() {
        if ($this->stmt != null) {
            $this->stmt = null;
        }
        $this->con = null;
        $this->connected = false;
    }
}

