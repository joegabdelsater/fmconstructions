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

class TableTraversal extends db{


    var $display;
    var $id;
    var $tableName;
    var $childTable;
    var $childTableField;
    var $fieldType; //Type of the display field (ex: photo_upload, mp3_upload, textfield etc...

    public static $truncation = 100;

    var $childrenTableField = array();
    var $children = array ();

    public static $forceSelectMenu = false;

    public static $forcedChildren = array ();

    public static $limitExceededTables = array() ;
    public static $limit = 5; //Limit for the second order of the hierarchy
    public static $result = "";
    public static $visited = array();
    public static $visitedRows = array ();
    public static $foreignKeysToSelfTables = array ();
    public static $childTablesCache = array ();


    public static $preselectedId = 0;

    public static $originalFieldName = '';

    public static $previousIndent = 0;

    public static $parentsTableArray = array ();

    public static $orderByPosition = false; // A default order by position ASC

    public static $foreignTableTo; // The selectable options are from this table

    static $options = array();

    public $currentLevel = 0;// Please reset this number if you use this menu twice on the same page. to reset : MenuItem::reset();

    function __construct($id,$display,$tableName){
        db::__construct();

        $this->id = (int)$id;
        $this->display = (string)$display;
        $this->tableName = (string)$tableName;
        $this->getChildTable();
        /*
        $tableName = $this->adodb->qstr($tableName, get_magic_quotes_gpc());
        $sql = "SELECT table_name,field_name FROM `system` WHERE foreign_table=".$tableName." AND table_name != ".$tableName." LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
        $row = $res->FetchRow();
        $this->childTable = $row['table_name'];
        $this->childTableField = $row['field_name'];
        }*/
    }


    /**
     * Returns true if this field has forced parameters and false otherwise
     *
     */
    function hasForcedParameters(){

        return !empty(self::$forcedChildren) && is_array(self::$forcedChildren);
    }

    function getChildTable(){

        $tableName = $this->adodb->qstr($this->tableName, get_magic_quotes_gpc());

/*        if(isset(self::$childTablesCache[$this->tableName])){
            $this->childTable = self::$childTablesCache[$this->tableName]['childTable'];
            $this->childTableField =  self::$childTablesCache[$this->tableName]['childTableField'];
            $this->children =  self::$childTablesCache[$this->tableName]['children'];
            $this->childrenTableField =  self::$childTablesCache[$this->tableName]['childrenTableField'];
            return;
        }*/



        if($this->hasForcedParameters()){


            foreach(self::$forcedChildren as $key=>$value){


                if($value == $this->tableName){
                    if($this->hasForeignKeyToSelf(true)){

                        $this->childTable = $this->tableName;
                    }else{
                        if(isset(self::$forcedChildren[$key+1])){
                            $this->childTable = self::$forcedChildren[$key+1];
                        }else{
                            $this->childTable = null;
                        }
                    }
                    break;
                }
            }


            if(!$this->hasForeignKeyToSelf(true)){

                $sql = "SELECT field_name FROM `system` WHERE foreign_table=".$tableName." AND table_name != ".$tableName." AND table_name='{$this->childTable}' ";
            }else{

                $sql = "SELECT field_name FROM `system` WHERE foreign_table=".$tableName." AND table_name='{$this->childTable}' ";
            }

            $res = $this->adodb->Execute($sql);
            $row = $res->FetchRow();
            $this->childTableField = $row['field_name'];

            //       self::$childTablesCache[$this->tableName]['childTable'] = $this->childTable;
            //      self::$childTablesCache[$this->tableName]['childTableField'] = $this->childTableField;

            return;
        }

        $sql = "SELECT table_name,field_name FROM `system` WHERE foreign_table=".$tableName." AND table_name != ".$tableName." ";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){

            while($row = $res->FetchRow()){
                $this->children [] = $row['table_name'];
                $this->childrenTableField [$row['table_name']] = $row['field_name'];
            }
            $this->childTable = $this->children[0];
            $this->childTableField = $this->childrenTableField[$this->childTable];




        } else if($res->RecordCount() >= 2){

            $relatedToTable = explode('_',$this->tableName);
            $relatedToTable = $relatedToTable[0];
            $relatedToTable = substr($relatedToTable, 0, -1);

            $sql = "SELECT table_name,field_name FROM `system` WHERE foreign_table=".$tableName." AND table_name != ".$tableName." AND table_name LIKE '%{$relatedToTable}%'";


            $res = $this->adodb->Execute($sql);
            while($row = $res->FetchRow()){
                $this->children [] = $row['table_name'];
                $this->childrenTableField [$row['table_name']] = $row['field_name'];
            }


            if(isset($this->children[0])){
                $this->childTable = $this->children[0];
                $this->childTableField = $this->childrenTableField[$this->childTable];
            }



        }

        self::$childTablesCache[$this->tableName]['childTable'] = $this->childTable;
        self::$childTablesCache[$this->tableName]['childTableField'] = $this->childTableField;
        self::$childTablesCache[$this->tableName]['children'] = $this->children;
        self::$childTablesCache[$this->tableName]['childrenTableField'] = $this->childrenTableField;
    }

    static function resetResult(){
        self::$result = "";
    }


    public function hasForeignKeyToSelf($bool = false){

        if(isset(self::$foreignKeysToSelfTables[$this->tableName]) && self::$foreignKeysToSelfTables[$this->tableName] === false ){
            return false;
        }
        $sql = "SELECT field_name FROM `system` WHERE foreign_table='{$this->tableName}' AND table_name='{$this->tableName}' LIMIT 1;";
        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 0){
            self::$foreignKeysToSelfTables[$this->tableName] = false;
            return false;
        }

        $row = $res->FetchRow();

        if($bool === true){
            $sql = "SELECT {$row['field_name']} FROM {$this->tableName} WHERE id = {$this->id} LIMIT 1;";
            $res = $this->adodb->Execute($sql);
            if($res->RecordCount() == 0){
                return false;
            }else{
                $currRow = $res->FetchRow();
                if(!empty($currRow[$row['field_name']])){

                    return $row['field_name'];
                }else{
                    return false;
                }
            }

        }

        return $row['field_name'];
    }

    /**
     * Function to check if the current row (id) has children in the child table
     *
     */
    public function hasChildren(){

        if($field = $this->hasForeignKeyToSelf()){
            $sql = "SELECT id FROM `".$this->tableName."` WHERE `".$field."` ='".$this->id."' LIMIT 1;";

            $res = $this->adodb->Execute($sql);
            if($res->RecordCount() == 1){
                return true;
            }
        }

        if(!isset($this->childTable) ){
            return false;
        }




        //ParentsTableArray restricts the children fetch to be from the table inside that array
        //Check if the child table is a part of this array
        //If not, the content of the child table should not be fetched.
        if(!empty(self::$parentsTableArray)){
            if(!in_array($this->childTable, self::$parentsTableArray)){
                return false;
            }
        }




        //   $sql = "SELECT id FROM `".$this->childTable."` WHERE ".$this->childTableField."='".$this->id."' LIMIT 1;";

        $sql = "SELECT `id` FROM `{$this->childTable}` WHERE `{$this->childTableField}` = '{$this->id}'  OR find_in_set( '{$this->id}' ,cast(REPLACE( `{$this->childTableField}` ,'|',',') as char)) > 0  LIMIT 1";


        $res = $this->adodb->Execute($sql);

        if($res->RecordCount() == 1){
            return true;
        }else{


            $children = $this->children;
            while($child = array_shift($children)){
                $sql = "SELECT id FROM `".$child."` WHERE ".$this->childrenTableField[$child]."='".$this->id."' LIMIT 1;";
                $res = $this->adodb->Execute($sql);
                if($res->RecordCount() == 1){
                    return true;
                }
            }

            return false;
        }

    }

    /**
     * Returns the text that will be displayed and visible to the user
     *
     */
    function display(){
        return displayInSitemap($this->tableName,$this->display);
    }

    /**
     * Checks if a table has been visited or not
     *
     * @param mixed $tableName
     */
    public static function beenVisited($tableName){
        if(in_array($tableName,self::$visited)){
            return true;
        }
        return false;
    }
    //Returns the name of each page
    public function pageName(){

        if(isset(self::$options[$this->tableName]['page_link']) && is_array(self::$options[$this->tableName]) ){
            return self::$options[$this->tableName]['page_link'];
        }
        return str_replace("'","",$this->tableName).'.php';
    }

    /**
     * Returns the menu item link
     *
     */
    public function link(){
        return $this->pageName()."?id=".$this->id;
    }

    /**
     * Returns the menu item link
     *
     */
    public function adminLink(){
        return pageLink("generate.php?table={$this->tableName}&id={$this->id}");
    }

    /**
     * Returns the database column name of the field that will be displayed to the user
     *
     * @param mixed $tableName
     */
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

    /**
     * Gets the order by clause for each table
     *
     */
    public static function getOrderBy($tableName){
        if(isset(self::$options[$tableName]['order']) && is_array(self::$options[$tableName]) ){
            return self::$options[$tableName]['order'];
        }elseif(self::$orderByPosition){
            return 'pos ASC';
        }else{
            return false;
        }
    }

    public function visited($tableName){
        if(!in_array($tableName,self::$visited)){
            self::$visited [] = $tableName;
        }
    }

    /**
     * Gets the children for each table
     *
     */
    public function getChildren(){


        self::resetLimitExcess($this->childTable);
        $this->visited($this->childTable);
        $this->visited($this->tableName);




        $displayField = self::getFieldToDisplay($this->childTable);


        $hasChildrenFromSameTable = false;
        if($field = $this->hasForeignKeyToSelf()){
            $sql = "SELECT id FROM `".$this->tableName."` WHERE `".$field."` ='".$this->id."' LIMIT 1;";

            $res = $this->adodb->Execute($sql);
            if($res->RecordCount() == 1){


                $displayField = self::getFieldToDisplay($this->tableName);
                $sql = "SELECT `id`,`{$displayField}` FROM `{$this->tableName}` WHERE `{$field}` = '{$this->id}' ";
                if(self::getOrderBy($this->tableName)){
                    $sql .= " ORDER BY ".self::getOrderBy($this->tableName)." ";
                }

                $hasChildrenFromSameTable = true;
            }
        }



        if(!$hasChildrenFromSameTable){
            $sql = "SELECT `id`,`{$displayField}` FROM `{$this->childTable}` WHERE `{$this->childTableField}` = '{$this->id}'  OR find_in_set( '{$this->id}' ,cast(REPLACE( `{$this->childTableField}` ,'|',',') as char)) > 0  ";



            if(self::getOrderBy($this->childTable)){
                $sql .= " ORDER BY ".self::getOrderBy($this->childTable)." ";
            }
        }





        $res = $this->adodb->Execute($sql);

        if($res->RecordCount() > self::$limit && self::$limit != 0 ){
            $sql .= " LIMIT ".self::$limit." ";
            $res = $this->adodb->Execute($sql);

            $this->limitExceededFor($this->childTable);
        }
        // echo $sql;
        $objectArray = array ();
        while( $row = $res->FetchRow() ){


            if($hasChildrenFromSameTable){
                $table = new Table($this->tableName);
                $object = new self($row['id'],$row[$displayField],$this->tableName);

            }else{
                $table = new Table($this->childTable);
                $object  = new self($row['id'],$row[$displayField],$this->childTable);
            }

            $fieldType = $table->getFieldType($displayField);
            $object->fieldType = $fieldType;
            $objectArray [] = $object;
        }




        if($hasChildrenFromSameTable){
            $displayField = self::getFieldToDisplay($this->childTable);
            if(!empty($displayField) && !empty($this->childTableField) && !empty($this->childTable) && !empty($this->id) ){
                $sql = "SELECT `id`,`{$displayField}` FROM `{$this->childTable}` WHERE `{$this->childTableField}` = '{$this->id}' ";

                if(self::getOrderBy($this->childTable)){
                    $sql .= " ORDER BY ".self::getOrderBy($this->childTable)." ";
                }



                $res = $this->adodb->Execute($sql);

                if($res->RecordCount() > self::$limit && self::$limit != 0 ){
                    $sql .= " LIMIT ".self::$limit." ";
                    $res = $this->adodb->Execute($sql);

                    $this->limitExceededFor($this->childTable);
                }

                while( $row = $res->FetchRow() ){

                    $table = new Table($this->childTable);

                    $object  = new self($row['id'],$row[$displayField],$this->childTable);


                    $fieldType = $table->getFieldType($displayField);
                    $object->fieldType = $fieldType;
                    $objectArray [] = $object;
                }
            }
        }

        return $objectArray;
    }



    public function visitedRows($rowId,$tableName){
        self::$visitedRows[$tableName][] = $rowId;
    }
    public function hasVisitedRows($rowId,$tableName){
        if(isset(self::$visitedRows[$tableName]) && is_array(self::$visitedRows[$tableName])){
            return in_array($rowId,self::$visitedRows[$tableName]);
        }else{
            return false;
        }
    }

    /**
     * Appends to the array the list of table names whose limit was exceeded when fetching their records.
     *
     * @param string $tableName
     */
    public function limitExceededFor($tableName){
        self::$limitExceededTables [$tableName] = array ('foreignKey' => $this->childTableField, 'id' => $this->id);


    }

    public static function resetLimitExcess($tableName=NULL){
        if(isset($tableName)){
            unset(self::$limitExceededTables[$tableName]);
        }else{
            self::$limitExceededTables = array ();
        }
    }

    function incrementLevel(){
        $this->currentLevel++;
    }

    //Returns an array of menu items
    public static function createMenu($tableName, $options = null){



        if(isset($options)){
            self::$options = $options;
        }
        if(isset($options['options']['limit'])){
            self::$limit = $options['options']['limit'];
        }
        if(isset($options['parameters']) && $options['parameters'] !== false ){
            self::$forcedChildren = $options['parameters'];
        }

        $db = new MySQLDatabase();


        if (self::getFieldToDisplay($tableName) == 'first_name') {
			$sql = "SELECT `id`,`".self::getFieldToDisplay($tableName)."`, `last_name` FROM `$tableName`";
		} else {
			$sql = "SELECT `id`,`".self::getFieldToDisplay($tableName)."` FROM `$tableName`";
		}

        $table = new Table($tableName);

        if( ($field = $table->hasForeignKeyRelatedTo($tableName,true)) !== false ){
            $sql .= " WHERE {$field} = '' ";
        }

        if(isset($options['options']['foreignKeyId'],$options['options']['foreignKey']) ){
            $sql .= " WHERE `{$options['options']['foreignKey']}` = '{$options['options']['foreignKeyId']}'  OR find_in_set( '{$options['options']['foreignKeyId']}' ,cast(REPLACE( `{$options['options']['foreignKey']}` ,'|',',') as char)) > 0  ";
        }

        if(isset($options['options']['order_by'])){
            $sql .= " ORDER BY `{$options['options']['order_by']}` ";
        }else if(self::getOrderBy($tableName)){
            $sql .= " ORDER BY ".self::getOrderBy($tableName)." ";
        }

        if(isset($options['options']['userlimit'])){
            $options['options']['userlimit'] =  intval($options['options']['userlimit']);
            $sql .= " LIMIT {$options['options']['userlimit']} ";
        }






        $fieldType = $table->getFieldType(self::getFieldToDisplay($tableName));
        // echo $sql;
        $res = $db->query($sql);

        $object_array = array();
        while($row = $db->fetch_array($res)){
            if (self::getFieldToDisplay($tableName) == 'first_name') {
                $displayValue = $row['first_name'].' '.$row['last_name'];
            }else {
                $displayValue = $row[self::getFieldToDisplay($tableName)];
            }


            $object = new self($row['id'],$displayValue,$tableName);
            $object->fieldType = $fieldType;

            $object_array [] = $object;
        }



        return $object_array;

    }

    static function tableExceededLimit($tableName){
        return isset(self::$limitExceededTables[$tableName]);
        return in_array($tableName,self::$limitExceededTables);
    }

    /**
     *Function that draws the menu, this function should be overridden in classes that extends this class in order to produce different results
     */
    function draw (Array $listOfItems) {
        echo "
        <ul class='parentMenu '>
        ";
        $i=0;
        foreach ($listOfItems as $item) {
            echo "<li class='child '>";
            echo"<a href='".$item->link()."' class='link'>";
            echo $item->display();
            echo "</a>";

            if ($item->hasChildren()) {

                drawMenu($item->getChildren()); // here is the recursion
            }
            echo "</li>";

        }
        echo "
        </ul>
        ";
    }



}

/**
 * FUnction that draws the recursive menu up to a certain level
 *$printParentLinks is a boolean to have links on the parent items or not
 * @param array $listOfItems
 * @param integer $level
 * @param bool $printParentLinks
 */
function drawMenu (Array $listOfItems) {
    echo "
    <ul class='parentMenu '>
    ";
    $i=0;
    foreach ($listOfItems as $item) {
        echo "<li class='child '>";
        echo"<a href='".$item->link()."' class='link'>";
        echo $item->display();
        echo "</a>";
        if ($item->hasChildren()) {

            drawMenu($item->getChildren()); // here is the recursion
        }
        echo "</li>";

    }

    echo "
    </ul>
    ";
}


function drawMenuTest (Array $listOfItems) {
    echo "
    <ul class='parentMenu '>
    ";
    $i=0;
    foreach ($listOfItems as $item) {
        echo "<li class='child '>";
        echo"<a href='".$item->link()."' class='link'>";
        echo $item->display();
        echo "</a>";
        if ($item->hasChildren()) {

            drawMenu($item->getChildren()); // here is the recursion
        }
        echo "</li>";

    }

    echo "
    </ul>
    ";
}

function getParentTableByRowId($currentTable,$currentFieldName,$currentRowId){
    $sql = "SELECT foreign_table FROM `system` WHERE table_name = '{$currentTable}' AND field_name ='{$currentFieldName}' LIMIT 1 ";
    $res = $this->adodb->Execute($sql);
    $row = $res->FetchRow();

    //Foreign key to self
    if($row['foreign_table'] == $currentTable){
        $sql = "SELECT $currentFieldName FROM $currentTable WHERE id = '{$currentRowId}' LIMIT 1";
        $res = $this->adodb->Execute($sql);
        $row = $res->FetchRow();

        if(!empty($row[$currentFieldName])){
            return $currentTable;
        }
    }

    return $row['foreign_table'];
}


/**
 * Function that draws the recursive menu up to a certain level
 * $printParentLinks is a boolean to have links on the parent items or not
 * @param array $listOfItems
 * @param integer $level
 * @param bool $printParentLinks
 */
function drawSiteMapMenu (Array $listOfItems) {

    if(empty($listOfItems)){
        return;
    }

    echo "
    <ul class='parentMenu'>
    ";
    $i=0;

    foreach ($listOfItems as $item) {
        if($item->hasForeignKeyToSelf() && $item->hasVisitedRows($item->id,$item->tableName)){
            continue;
        }

        $item->visitedRows($item->id,$item->tableName);

        echo "<li class='child {$item->fieldType}'>";
        echo "<a href='".$item->adminLink()."' class='link'>";
        echo $item->display() .' <span class="item_id">#'.$item->id.'</span>';
        echo "</a>";

        if(isset($item->childTable)){


            foreach($item->children as $childTable){
                $childTableField = $item->childrenTableField[$childTable];
                echo '  <a class="addLink" href="'.pageLink("generate.php?table={$childTable}&{$childTableField}={$item->id}").'">[ Add '.printTableName($childTable).' ]</a>';
            }
        }

        if ($item->hasChildren()) {

            while($item->childTable = array_shift($item->children)){
                $item->childTableField = $item->childrenTableField[$item->childTable];
                drawSiteMapMenu($item->getChildren()); // here is the recursion
            }
        }
        echo "</li>";

    }

    if(TableTraversal::tableExceededLimit($item->tableName)){
        echo '<li  ><a class="loadMore" href="javascript:void(0)" data-table-name="'.$item->tableName.'" data-foreign-key="'.TableTraversal::$limitExceededTables[$item->tableName]['foreignKey'] .'"  data-foreign-key-id="'.TableTraversal::$limitExceededTables[$item->tableName]['id'] .'" >Load more '.printTableName($item->tableName).'...</a></li>';
    }

    echo "
    </ul>
    ";
}


function drawSelectMenu (Array $listOfItems,$value=NULL, $parentsTableArray = array () ) {

    if(!is_array($value)){
        $value = explode('|',$value);
    }
    TableTraversal::$forceSelectMenu = true;

    if(empty(TableTraversal::$parentsTableArray)){
        TableTraversal::$parentsTableArray = $parentsTableArray;
    }


    $startIndent = TableTraversal::$previousIndent;



    foreach ($listOfItems as $item) {


        TableTraversal::$previousIndent = $startIndent;
        if($item->hasForeignKeyToSelf()){
            if($item->hasVisitedRows($item->id,$item->tableName)){
                continue;
            }
        }
        $item->visitedRows($item->id,$item->tableName);

        if($item->hasForeignKeyToSelf()){
            if(!in_array($item->tableName,TableTraversal::$parentsTableArray)){
                continue;
            }
        }

        $label = '';
        $delimiter = '&#8210;&#8210;';
        //$label .= str_repeat('---',$allLevels - $level);



        $label .= truncateUtf8($item->display(),TableTraversal::$truncation).' - #'.$item->id.'';
        $arrayWithoutLastChild = TableTraversal::$parentsTableArray;
        array_pop($arrayWithoutLastChild);

        // preselect value for edited or saved

        $isSelected = (in_array($item->id,$value) ? 'selected="selected"' : '' );
        if (TableTraversal::$preselectedId > 0) {
            if ($item->id == TableTraversal::$preselectedId ) {
                $isSelected = 'selected="selected"';
            }
        }



        if($item->hasChildren() || ((in_array($item->tableName, $arrayWithoutLastChild)) && !$item->hasForeignKeyToSelf()) || $item->tableName != TableTraversal::$foreignTableTo){

            if($item->tableName != TableTraversal::$foreignTableTo){
                TableTraversal::$result .= "<optgroup label='".str_repeat($delimiter,TableTraversal::$previousIndent/5).$label."'>";
            }else{
                TableTraversal::$result .= '<option value="'.$item->id.'" '.$isSelected.'>'.str_repeat($delimiter,TableTraversal::$previousIndent/5).$label.'</option>';
            }


            if($item->hasChildren()){
                TableTraversal::$previousIndent += 10;
            }

        }else {


            TableTraversal::$result .= '<option value="'.$item->id.'" '.$isSelected.'>'.str_repeat($delimiter,TableTraversal::$previousIndent/5).$label.'</option>';
        }

        if ($item->hasChildren()) {

            drawSelectMenu($item->getChildren(),$value); // here is the recursion
        }
        if($item->hasChildren()){
            if($item->tableName != TableTraversal::$foreignTableTo){
                TableTraversal::$result .= "</optgroup>";
            }
        }

    }
    TableTraversal::$previousIndent = $startIndent;
    TableTraversal::$forceSelectMenu = false;
}


function drawSelectMenuOriginal (Array $listOfItems,$level=NULL,$value=NULL) {

    static $allLevels;
    if(!$allLevels)
        $allLevels = $level;

    $i=0;
    foreach ($listOfItems as $item) {


        $label = '';
        //$label .= str_repeat('---',$allLevels - $level);
        $style = "text-indent:" . ( ($allLevels - $level) *10) .'px';
        $label .= $item->display();

        if($level != 0){


            TableTraversal::$result .= "<optgroup label='{$label}'  style='".$style."'>";
        }else {
            TableTraversal::$result .= '<option value="'.$item->id.'" '.($value == $item->id ? 'selected="selected"' : '' ).' style="'.$style.'">'.$label.'</option>';
        }

        if ($item->hasChildren()) {

            $item->incrementLevel();

            if($item->currentLevel > $level){

                continue;
            }

            drawSelectMenu($item->getChildren(),$level-1,$value); // here is the recursion
        }
        if($level != 0){
            TableTraversal::$result .= "</optgroup>";
        }

    }





}



?>