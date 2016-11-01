<?php
namespace Kobalt\SimpleBase;
/* Class to represent tables, to help DBManager class build queries based on table properties
 */
class DbTable {
    /* The name of the table
     * @var string
     */
    public $name;
    
    /* The name of the id column
     * @var string
     */
    public $id;
    
    /* The name of the column to sort by
     * Defaults to "modified"
     * @var string
     */
    public $sort = "modified";
     
     /* The sort direction, can be ASC or DESC
      * Defaults to DESC
      * @var string
      */
    public $sortDir = "DESC";
     
    
    /* An associative array of columns that hold foreign keys pointing to tables
     * @var array
     */
    public $fkCols = array();
    
    /* An array of column names associated with aliases for those columns
     * This prevents a joined query from overwriting columns with the same name from different tables
     * @var array
     */
    public $aliases = array();
    
    /* An array of columns meant for display, 
     * @var array
     */
    public $displayCols = array();
    
    
    /* Construct a table object
     * @param $table: string, the name of the table in the database
     * @param $id: string, the name of the id column
     */
    public function __construct($table, $id) {
        $this->name = $table;
        $this->id = $id;
    }
    
    /* Add a foreign key column and table association to the list of foreign keys
     * @param $col: string, the name of the foriegn key column
     * @param $table: DbTable, the table the foreign key refers to
     * @return: void
     */
    public function addAssoc($col, $table) {
        $this->fkCols[$col] = $table;
    }
    
    /* Change the sort direction for queries
     * @param $dir: string
     * @return: void
     */
    public function setSortDir($dir) {
        $dir = strtoupper($dir)
        if ($dir == "ASC" || $dir == "DESC") {
            $this->sortDir = $dir;
        }
    }
    
    /* Add a column alias, to prevent overlap in a join query
     * @param $col: string, the original name of the column
     * @param $alias: string, the alias name of the column
     * @return: void
     */
    public function addAlias($col, $alias) {
        $this->aliases[$col] = $alias;
    }
}
?>
