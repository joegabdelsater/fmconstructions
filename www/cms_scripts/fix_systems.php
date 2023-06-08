<?php
/**
 * Created by PhpStorm.
 * User: lambasoft
 * Date: 7/28/17
 * Time: 12:35 PM
 */


class DB{

    private $conn;
    public function __construct($host,$database,$username,$password){
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;

        if(!$this->connect()){
            throw new Exception('Unable to connect to database');
        }
    }

    private function connect(){
        try{
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password);
            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return true;
        }
        catch(PDOException $ex){
            return false;
        }
    }


    public function findSQL($query, $mode = PDO::FETCH_NUM){
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        // set the resulting array to associative
        $stmt->setFetchMode($mode);

        return $stmt->fetchAll();
    }

}


class CMS{

    public $white_tables = array(
        "cmsgen_contenthistory","backend_structure","cmsgen_default_values","logs","cmsgen_statistics","users","table_options","system","site_options","admin_advanced_settings","cmsgen_cpanel_links"
    );


    public $my_tables = array();
    public $my_columns = array();

    private $conn;

    public function __construct($connection){
        $this->conn = $connection;

        $this->setTables();
        $this->setColumns();


    }

    private function setTables(){
        $this->my_tables = $this->conn->findSQL("show tables");

        $this->my_tables = array_map(function($item){
            return reset($item);
        },$this->my_tables);

        $this->my_tables = array_values(array_diff($this->my_tables,$this->white_tables));
    }


    private function setColumns(){
        foreach ($this->my_tables as $table) {
            $this->my_columns[$table] = array_map(function($item){
                return $item[0];
            },$this->conn->findSQL("SHOW COLUMNS FROM {$table}"));
        }
    }


    public function getSystemTables(){
        $_systemTables = $this->conn->findSQL("SELECT * FROM `system`", PDO::FETCH_ASSOC);

        $systemColumns = array();
        foreach ($_systemTables as $systemTable) {
            if(!in_array($systemTable['table_name'],$this->white_tables)){
                $systemColumns[$systemTable['table_name']][] = $systemTable;
            }
        }

        return $systemColumns;
    }


    public function getSystemColumns(){
        $systemColumns = array();
        foreach ($this->getSystemTables() as $rows) {
            foreach ($rows as $row) {
                if(isset($row['table_name']) && isset($row['field_name'])){
                    $_table = $row['table_name'];
                    $_column = $row['field_name'];
                    if(!in_array($_table,$this->white_tables)){
                        $systemColumns[$_table][] = $_column;
                    }
                }
            }
        }
        return $systemColumns;
    }


}



class FixSystem{

    public function __construct($host, $database, $user, $password){
        $this->DB = new DB($host,$database,$user,$password);
        $this->CMS = new CMS($this->DB);
    }

    private function full_array_diff($array1,$array2){
        return array_merge(array_diff($array1, $array2), array_diff($array2, $array1));
    }

    public function missingTables(){
        $systemTables = $this->CMS->getSystemTables();
        $tables = $this->CMS->my_tables;

        $tableDifference = array_diff($tables,array_keys($systemTables));
        return $tableDifference;
    }

    public function extraTables(){
        $systemTables = $this->CMS->getSystemTables();
        $tables = $this->CMS->my_tables;


        $tableDifference = array_diff(array_keys($systemTables),$tables);
        return $tableDifference;
    }

    public function missingColumns(){
        $tableDifference = $this->missingTables();
        $columns = $this->CMS->my_columns;

        $systemColumns = $this->CMS->getSystemColumns();



        $missingColumns = array();
        foreach ($systemColumns as $_table => $systemColumn) {
            if(isset($columns[$_table])){
                if(count($systemColumn) != count($columns[$_table])){
                    $missingColumns[$_table] = $this->full_array_diff($systemColumn,$columns[$_table]);
                }
            }
        }

        foreach ($tableDifference as $_table) {
            if(!isset($missingColumns[$_table])){
                $missingColumns[$_table . " *"] = $columns[$_table];
            }
        }


        return $missingColumns;
    }

    public function extraColumns(){
        $columns = $this->CMS->my_columns;

        $systemColumns = $this->CMS->getSystemColumns();



        $extraColumns = array();
        foreach ($systemColumns as $_table => $systemColumn) {
            if(!isset($columns[$_table]) && !in_array($_table,$this->CMS->white_tables)){
                $extraColumns[$_table . "*"] = $systemColumn;
            }
        }


        return $extraColumns;
    }


}

$database = isset($_GET['database'])? $_GET['database'] : 'grinddig_greenbin';
$user = isset($_GET['user'])? $_GET['user'] : 'grinddig_user';
$password = isset($_GET['password'])? $_GET['password'] : '$u1s2e3#';
$FixSystem = new FixSystem("127.0.0.1",$database,$user,$password);

echo "<center>";
$missingTables = $FixSystem->missingTables();
echo "You have <b>" . count($missingTables) . "</b> missing tables <br>";

$missingColumns = $FixSystem->missingColumns();
$missingColumnsCount = 0;
foreach ($missingColumns as $missingColumn) {
    $missingColumnsCount += count($missingColumn);
}
if($missingColumnsCount > 0){
    echo  "You have <b>" . $missingColumnsCount . "</b> missing columns in the system table";
}else{
    echo  "You have <b>" . abs($missingColumnsCount) . "</b> extra columns  in the system table";
}

echo "</center>";
?>

<hr>


<div style="float: left; margin-left: 50px;">
    <h4><u>Missing Tables</u></h4>
    <ul>
        <?php
        foreach ($missingTables as $missingTable) {
            echo "<li>";
            echo $missingTable;
            echo "</li>";
        }
        ?>
    </ul>

    <h4><u>Missing Columns</u></h4>

    <ul>
        <?php
        foreach ($missingColumns as $table => $columns) {
            echo "<li>";
            echo $table;
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>" . $column . "</li>";
            }
            echo "</ul>";
            echo "</li>";
        }
        ?>
    </ul>
    <i>* Table is missing</i>
</div>

<div style="float: right; margin-right: 50px;">

    <h4><u>Extra Tables</u></h4>
    <?php
    $extraTables = $FixSystem->extraTables();
    foreach ($extraTables as $extraTable) {
        echo "<li>";
        echo $extraTable;
        echo "</li>";
    }
    ?>


    <h4><u>Extra Columns</u></h4>
    <?php
    $extraColumns = $FixSystem->extraColumns();
    ?>
    <ul>
        <?php
        foreach ($extraColumns as $table => $columns) {
            echo "<li>";
            echo $table;
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>" . $column . "</li>";
            }
            echo "</ul>";
            echo "</li>";
        }
        ?>
    </ul>
    <i>* Table is missing</i>

</div>
