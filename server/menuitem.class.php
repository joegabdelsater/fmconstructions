<?php
    /**
    *CSRF class
    *
    * March 13 - 2013
    * V1.0 - Geroge
    * - Added orderByPosition flag to order the results by position.
    */
    require_once('api-adodb/config.php');

    require_once("database.class.php");



    class MenuItem extends db {

        public $id;
        public $name;

        public $tableName;
        public $childTable;
        public $foreignField;

        public static $result;

        public static $orderByPosition = false;

        public static $options = array();

        public $currentLevel = 0;// Please reset this number if you use this menu twice on the same page. to reset : MenuItem::reset();

        function reset(){
            $this->currentLevel = 0;
        }

        static function resetResult(){
            self::$result = "";
        }
        function __construct($id,$name,$tableName,$link_id=NULL){

            db::__construct();
            //New table object
            $this->id = $id;
            $this->name = $name;

            $this->tableName = $this->adodb->qstr($tableName, get_magic_quotes_gpc());

            $temp = explode("_",$tableName);
            $firstPartTableName = reset($temp);

            $sql = "SELECT table_name,field_name FROM `system` WHERE foreign_table=".$this->tableName." AND table_name LIKE '%".$firstPartTableName."%' LIMIT 1;";
            $res = $this->adodb->Execute($sql);

            if($res->RecordCount() == 0){
                $sql = "SELECT table_name,field_name FROM `system` WHERE foreign_table=".$this->tableName." LIMIT 1;";
                $res = $this->adodb->Execute($sql);
            }
            if($res->RecordCount() == 1){
                $row = $res->FetchRow();

                $this->childTable = $row['table_name'];
                $this->foreignField = $row['field_name'];
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


        static function getFieldToDisplay($tableName){


            if(isset(self::$options[$tableName]['display_field']) && is_array(self::$options[$tableName]) && !empty(self::$options[$tableName]['display_field'])){
                return self::$options[$tableName]['display_field'];
            }


            if(isset(self::$options[$tableName]) && !is_array(self::$options[$tableName])){

                return self::$options[$tableName];
            }

            $table = new Table($tableName);
            if(($displayField = $table->getDisplayField()) !== false){
                return $displayField;
            }

            return 'name';
        }


        //Returns an array of menu items
        public static function createMenu($tableName, $options = null){
            require_once(LIB_PATH.DS.'database.class.php');


            if(isset($options)){
                self::$options = $options;
            }

            $db = new MySQLDatabase();


            $sql = "SELECT id,`".self::getFieldToDisplay($tableName)."` FROM `$tableName`";

            if(self::$orderByPosition){
                $sql .= " ORDER BY pos ASC ";
            }
            // echo $sql;
            $res = $db->query($sql);

            $object_array = array();
            while($row = $db->fetch_array($res)){
                $object = new MenuItem($row['id'],$row[self::getFieldToDisplay($tableName)],$tableName);
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
            $parentTable = str_replace('`','', $this->tableName);
            $parentTable = str_replace("'",'', $parentTable);
            $parentTable = explode("_",$parentTable);
            $parentTable = $parentTable[0];

            $sql = "SELECT foreign_field FROM `system` WHERE foreign_table = '{$this->childTable}'";
            $res = $this->adodb->Execute($sql);

            //echo $sql;

            if($res->RecordCount() == 1){
                $sql = "SELECT foreign_field FROM `system` WHERE foreign_table = '{$this->childTable}' AND table_name LIKE '{$parentTable}%' LIMIT 1";

                $res = $this->adodb->Execute($sql);
            }




            $row = $res->FetchRow();
            $fieldThatContainsNameToDisplay = $row['foreign_field'];


            if(empty($fieldThatContainsNameToDisplay)){
                $fieldThatContainsNameToDisplay = self::getFieldToDisplay($this->childTable);
            }

            $sql ="SELECT `id`,`{$fieldThatContainsNameToDisplay}`,`".$this->foreignField."` FROM `".$this->childTable."` WHERE ".$this->foreignField."=".$this->id;


            if(self::$orderByPosition){
                $sql .= " ORDER BY pos ASC ";
            }



            $res = $this->adodb->Execute($sql);
            $object_array = array();
            $foreignFieldName = $this->foreignField;
            $childTableName = $this->childTable;
            while($row = $res->FetchRow()){
                $object = new MenuItem($row['id'],$row[$fieldThatContainsNameToDisplay],$childTableName);
                $object_array [] = $object;
            }
            return $object_array;
        }

        function incrementLevel(){
            $this->currentLevel++;
        }

    }

    //Function that draws the menu takes as  parameters the array of menu items and the levels. level=0 prints all menu items
    /**
    * FUnction that draws the recursive menu up to a certain level
    *$printParentLinks is a boolean to have links on the parent items or not
    * @param array $listOfItems
    * @param integer $level
    * @param bool $printParentLinks
    */
    function drawMenu (Array $listOfItems,$level=0,$printParentLinks = true) {
        echo "
        <ul class='parentMenu level$level'>
        ";
        $i=0;
        foreach ($listOfItems as $item) {
            echo "<li class='child level$level'>";
            if(($level == 0 && !$printParentLinks) || $printParentLinks){
                echo"<a href='".$item->link()."' class='link level$level'>";
            }
            echo $item->name;
            if(($level == 0 && !$printParentLinks) || $printParentLinks){
                echo "</a>";
            }
            if ($item->hasChildren()) {

                $item->incrementLevel();

                if($item->currentLevel > $level){

                    continue;
                }
                drawMenu($item->getChildren(),$level-1,$printParentLinks); // here is the recursion
            }
            echo "</li>";

        }
        echo "
        </ul>
        ";
    }

    function generateNestedArray(Array $listOfItems,&$array=null){

        if(!$array)
            $array = array();

        foreach ($listOfItems as $item) {
            $array [$item->id] = $item->name;
            if ($item->hasChildren()) {
                $array[] = generateNestedArray($item->getChildren(), $array);
            }
        }

        return $array;

    }

    /**
    * Function that returns a select menu recursively based on several database related tables
    *
    * $value is the ID value to be selected
    *
    * @param mixed $listOfItems
    * @param mixed $level
    * @param mixed $value
    */
    function drawSelectMenu (Array $listOfItems,$level=0,$value=NULL) {
        static $allLevels;
        if(!$allLevels)
            $allLevels = $level;

        $i=0;
        foreach ($listOfItems as $item) {


            $label = '';
            //$label .= str_repeat('---',$allLevels - $level);
            $style = "text-indent:" . ( ($allLevels - $level) *10) .'px';
            $label .= $item->name;

            if($level != 0){


                MenuItem::$result .= "<optgroup label='{$label}'  style='".$style."'>";
            }else {
                MenuItem::$result .= '<option value="'.$item->id.'" '.($value == $item->id ? 'selected="selected"' : '' ).' style="'.$style.'">'.$label.'</option>';
            }

            if ($item->hasChildren()) {

                $item->incrementLevel();

                if($item->currentLevel > $level){

                    continue;
                }

                drawSelectMenu($item->getChildren(),$level-1,$value); // here is the recursion
            }
            if($level != 0){
                MenuItem::$result .= "</optgroup>";
            }

        }


    }

    //
    //    $menu = MenuItem::createMenu("brands");
    //    drawMenu($menu);



?>