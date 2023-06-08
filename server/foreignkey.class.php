<?php
    require_once('api-adodb/config.php');
    require_once("database.class.php");

    class Foreignkey {

        public $id;
        public $name;

        public $tableName;
        public $childTable;
        public $foreignField;
        public $db;

        public static $lastLevel;
        public static $grandParent;

        public $result;

        function __construct($tableName,$fieldName){
            $this->db = new MySQLDatabase();
            $this->tableName = $tableName;
            $this->fieldName = $fieldName;


            self::$lastLevel = $this->getLastLevel();
            self::$grandParent = $this->getGrandParent();
            // $this->result = $this->createMenu();
        }

        function returnLastLevel(){

            return self::$lastLevel;
        }
        function returnGrandParent(){
            return self::$grandParent;
        }

        function getLastLevel($tableName = null){
            if(!$tableName){
                $tableName = $this->tableName;
            }
            $sql = "SELECT table_name FROM system WHERE foreign_table = '{$tableName}' ";

            $res = $this->db->query($sql);

            if($this->db->num_rows($res)){
                $row = $this->db->fetch_array($res);
                if(!empty($row['table_name'])){
                    return $row['table_name'];
                }
            }
            return "";
        }

        function getGrandParent($table=null,$field=null){
            if(!$table){
                $table = $this->tableName;
            }
            if(!$field){
                $field = $this->fieldName;
            }
            $sql = "SELECT tableName FROM system WHERE table_name = '{$table}' AND field_name = '{$field}' ";
            $res = $this->db->query($sql);

            if($this->db->num_rows($res)){
                $row = $this->db->fetch_array($res);
                if(!empty($row['foreign_field']) && !empty($row['foreign_table'])){
                    return $this->getGrandParent($row['foreign_table'],$row['foreign_field']);
                }else{
                    $this->tableName = $table;
                    return true;
                }
            }else{
                return false;
            }
        }

        function tableHasForeignKey(){

            $sql = "SELECT foreign_key,foreign_table FROM system WHERE table_name = '{$this->tableName}' AND field_name = '{$this->fieldName}' LIMIT 1";
            $res = $this->db->query($sql);
            if($this->db->num_rows($res) == 1){
                return true;
            }else{
                return false;
            }
        }


        //Returns the name of each page
        public function pageName(){
            return str_replace("'","",$this->tableName);
        }

        //Returns the menu link
        public function link(){
            return $this->pageName().".php?id=".$this->id;
        }

        //Returns an array of menu items
        public static function createMenu($tableName){
            require_once(LIB_PATH.DS.'database.class.php');
            $db = new MySQLDatabase();

            $sql = "SELECT id,name FROM `$tableName`";
            // echo $sql;
            $res = $db->query($sql);

            $object_array = array();
            while($row = $db->fetch_array($res)){
                $object = new MenuItem($row['id'],$row['name'],$tableName);
                $object_array [] = $object;
            }


            return $object_array;

        }

        //Function to check if the menu item has children or not
        public function hasChildren(){
            if(isset($this->foreignField,$this->childTable)){

                $sql = "SELECT id FROM `".$this->childTable."` WHERE ".$this->foreignField."='".$this->id."' LIMIT 1;";

                $res = $this->adodb->Execute($sql);

                if($res->RecordCount() == 1){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }

        //Returns an array containing all the children
        public function getChildren(){
            $sql ="SELECT id,name,".$this->foreignField." FROM `".$this->childTable."` WHERE ".$this->foreignField."=".$this->id;
            $res = $this->adodb->Execute($sql);
            $object_array = array();
            $foreignFieldName = $this->foreignField;
            $childTableName = $this->childTable;
            while($row = $res->FetchRow()){
                $object = new MenuItem($row['id'],$row['name'],$childTableName);
                $object_array [] = $object;
            }
            return $object_array;
        }

    }

    //Function that draws the menu takes as  parameters the array of menu items and the levels. level=0 prints all menu items
    function drawMenu (Array $listOfItems,$level=0) {
        echo "<ul class='parentMenu level$level'>";
        $i=0;
        foreach ($listOfItems as $item) {
            echo "<li class='chilld level$level'><a href='".$item->link()."' class='link level$level'>" . $item->name."</a>";
            if ($item->hasChildren()) {
                $i++;
                if($level > 0 && $i >= $level){
                    continue;
                }
                drawMenu($item->getChildren(),$level-1); // here is the recursion
            }
            echo "</li>";

        }
        echo "</ul>";
    }


    //
    //    $menu = MenuItem::createMenu("brands");
    //    drawMenu($menu);



?>