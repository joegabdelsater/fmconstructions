<?php
/**
* April 27 - 2013
* Added deleteFile function to delete files and unset their column from the db
* April 02 - 2013
* check for dependencies when deleting from table - George
* March 16 - 2013
* Added multi file uploader for tables containing photos,gallery or images in their name
* Exceptions / Error handling when uploading files
*March 11 - 2013
* version 1.0 George
* Fixes:
* - standardized functions
* - added safeFind() function
*/
require_once('api-adodb/config.php');
require_once(LIB_PATH.DS.'session.class.php');
require_once(LIB_PATH.DS.'log.class.php');

class Table extends db{
    protected $tableName;

    public $currentId = null;
    protected $allAvailableFieldTypes = array(); //Stores the field types this version of CMS Generator includes (taken from backend_structure)
    protected $tableFieldsTypes = array(); //An array that includes information about the selected table's fields
    public static $existingTables = array(); //Aray containing the existing tables
    public static $tableDisplayFields = array(); //Aray containing the tables display fields
    public static $tableLinkFormats = array(); //Aray containing the url formats
    public static $fieldsNamesTypes = array(); //cache fields names Types

    protected static $magicQuotes;

    public static $fieldTypesByTable = array ();

    function __construct($tableName=""){


        db::__construct();
        if(!isset($magicQuotes))
            self::$magicQuotes = get_magic_quotes_gpc(); //Save the magic quote status in a static variable


        if(!empty($tableName)){

            $tableName = unhashTable($tableName);

            ####Check if the TABLE is a SYSTEM table###
            global $systemTables;
            global $adminTables;
            if(in_array($tableName,$systemTables)){
                global $session; //Global session object
                $session->message("You have no access to the following table.");
                redirect_to("dashboard.php");
            }
            if(in_array($tableName,$adminTables)){
                global $user;
                $user->requiredLevel(9);
            }
            ###END CHECK###
            $this->tableName = "`".($tableName)."`";
            //echo $this->tableName; die;
            //$this->tableName = $this->adodb->qstr($tableName,self::$magicQuotes);

            if($this->tableExists()){
                //Store the fields names and types
                $this->getFieldsNamesTypes();
            }else{
                //Error if the table selected does not exist
                die("Table ".h($tableName)." does not exist!");
            }

        }

    }

    //Getter function , gets the table name
    public function returnTableName(){
        return $this->tableName;
    }

    //Function that checks if the current selected table exists or not
    public function tableExists($tableName=""){


        if(isset( self::$existingTables[$tableName] )){
            return  self::$existingTables[$tableName];
        }
        if(empty($tableName)){
            $sql = "SELECT 1 FROM ".$this->tableName;
        }else{
            $sql = "SELECT 1 FROM `".$tableName."`";
        }

        $result = $this->adodb->Execute($sql);
        if($result === false){
            self::$existingTables[$tableName] = false;
            return false;
        }else{
            self::$existingTables[$tableName] = true;
            return true;

        }
    }




    function createAdminBreadCrumb($id,$fieldName){

        $parentsTableArray = array ();
        $foreignTable = $this->getForeignTable($fieldName);
        $parentStart = $foreignTable;


        $parentsTableArray[] = $parentStart;
        while( ($parent = $this->getRootTable($parentStart)) && ($parent != $parentsTableArray[0]) ){
            $parentsTableArray [] = $parent;
            $parentStart = $parent;
        }

        while($tableName = array_shift($parentsTableArray)){
            $table = new Table($tableName);
            $current = $table->findItemById($id);

            $displayField = $table->getDisplayField();
            $displayValue = strip_tags($current[$displayField]);
            
            if ($displayField == 'first_name') {
            	$displayValue = strip_tags($current[$displayField]).' '.strip_tags($current['last_name']);
			}
            $result[] = '<a href="'.pageLink("generate.php?table={$tableName}&id={$current['id']}").'">'.$displayValue.'</a>';
            $parentsTableArrayTemp = $parentsTableArray;
            $temp = array_shift($parentsTableArrayTemp);

            $sql = "SELECT field_name FROM `system` WHERE foreign_table = '$temp' AND table_name = '$tableName' ";

            $res = $this->adodb->Execute($sql);

            if($res && $res->RecordCount() == 0){
                return $result;
            }

            $row = $res->FetchRow();
            $id = $current[$row['field_name']];
        }

        return $result;

    }

    /**
    * Returns the HTML of a bread crumb. Given an ID of a row of the selected table and an options['tables'] array in the following manner:
    * The 'link' key in the tables array is used to define a custom link. %column_name_in_the_database%
    * Default link is tableName.php?id=$id
    *
    * In case $requestedLang & $defaultLang are passed, it will replace instanced of _ar by _en , for example if default display field is: title_en
    * you can switch it to title_ar
    *
    * array (
    *    'tables' => array (
    *        'products' => array
    *        (
    *            'foreignKey' => 'link_subgroup_id',
    *            'link' => 'products/%title%',
    *        ) ,
    *        'subgroups' => array (
    *            'foreignKey' => 'link_group_id',
    *        ),
    *        'groups' => array (
    *            'foreignKey' => 'link_category_id'
    *         )
    *     )
    *)
    * @param mixed $id
    * @param mixed $options
    * @param string $requestedLang
    * @param string $defaultLang
    */
    public function drawBreadcrumb($id,$options,$defaultLang='',$requestedLang=''){
        $tableName = $this->getRawTableName();

        $displayField = $this->getDisplayField();

        if (!empty($requestedLang) && !empty($defaultLang)) {
            $displayField = str_replace($defaultLang,$requestedLang,$displayField);
        }

        $row = $this->findItemById($id);
        $link = $this->returnFormatedLink($id);
        /*
        if(isset($options['tables'][$tableName]['link']) && !empty($options['tables'][$tableName]['link'])){
        $link = $options['tables'][$tableName]['link'];
        list($junk, $fieldName, $junk) = explode('%',$link);
        $link = str_replace('%'.$fieldName.'%', $row[$fieldName], $link);
        }else{
        if(isset($options['tables'][$tableName]['link']) && $options['tables'][$tableName]['link'] === false){
        $link = false;
        }else{
        $link = $tableName.'.php?id='.$id;
        }
        }

        */
        $class = '';
        if(isset($options['tables'][$tableName]['class'])){
            $class = $options['tables'][$tableName]['class'];
        }

        $table = new Table($tableName);
        $breadCrumb [] = array ('display' => $row[$displayField] , 'link' => $link ,'class' => $class);
        $i = 0;
        while ( isset($options['tables'][$tableName]['foreignKey']) &&  $foreignTable = $table->getForeignTable($options['tables'][$tableName]['foreignKey']) ){
            $i++;
            if($i == 10){
                break;
            }
            $foreignKeyId = $row[$options['tables'][$tableName]['foreignKey']];

            if(empty($foreignKeyId)){
                if(isset($options['tables'][$tableName]['foreignKey2'])){
                    $options['tables'][$tableName]['foreignKey'] = $options['tables'][$tableName]['foreignKey2'];

                    $foreignKeyId = $row[$options['tables'][$tableName]['foreignKey']];
                    $foreignTable = $table->getForeignTable($options['tables'][$tableName]['foreignKey']);
                    unset($options['tables'][$tableName]['foreignKey2']);

                }

                if(empty($foreignKeyId)){
                    break;
                }
            }

            $table = new Table($foreignTable);
            $row = $table->findItemById($foreignKeyId);
            $displayField = $table->getDisplayField();
            if (!empty($requestedLang) && !empty($defaultLang)) {
                $displayField = str_replace($defaultLang,$requestedLang,$displayField);
            }
            $tableName = $foreignTable;
            $link = $table->returnFormatedLink($foreignKeyId);

            /*
            if(isset($options['tables'][$tableName]['link']) && !empty($options['tables'][$tableName]['link'])){
            $link = $options['tables'][$tableName]['link'];
            list($junk, $fieldName, $junk) = explode('%',$link);
            $link = str_replace('%'.$fieldName.'%', $row[$fieldName], $link);
            }else{
            if(isset($options['tables'][$tableName]['link']) && $options['tables'][$tableName]['link'] === false){
            $link = false;
            }else{
            $link = $tableName.'.php?id='.$row['id'];
            }

            }
            */

            $class = '';
            if(isset($options['tables'][$tableName]['class'])){
                $class = $options['tables'][$tableName]['class'];
            }

            $breadCrumb [] = array ('display' => $row[$displayField] , 'link' => $link, 'class' => $class);

        }

        /*
        $i = 0;
        do {
        $i++;
        if(!isset($foreignTable)){
        $table = new Table($tableName);
        $row = $this->findItemById($id);
        }else{
        $table = new Table($foreignTable);
        $row = $table->findItemById($foreignKeyId);

        }

        if(isset($row[$options['tables'][$tableName]['foreignKey']])){
        $foreignKeyId = $row[$options['tables'][$tableName]['foreignKey']];
        }else{
        $foreignKeyId = 0;
        }

        if(empty($foreignKeyId)){
        echo $tableName.'<br />';
        if(isset($options['tables'][$tableName]['foreignKey2'])){
        $options['tables'][$tableName]['foreignKey'] = $options['tables'][$tableName]['foreignKey2'];
        $foreignKeyId = $row[$options['tables'][$tableName]['foreignKey']];
        unset($options['tables'][$tableName]['foreignKey2']);
        if(empty($foreignKeyId)){
        break;
        }
        }
        }

        $displayField = $table->getDisplayField();

        if(isset($foreignTable)){
        $tableName = $foreignTable;
        }


        if($i == 10){
        break;
        }
        if(isset($options['tables'][$tableName]['link']) && !empty($options['tables'][$tableName]['link'])){
        $link = $options['tables'][$tableName]['link'];
        list($junk, $fieldName, $junk) = explode('%',$link);
        $link = str_replace('%'.$fieldName.'%', $row[$fieldName], $link);
        }else{
        $link = $tableName.'.php?id='.$id;
        }
        $breadCrumb [] = array ('display' => $row[$displayField] , 'link' => $link);

        }while( isset($options['tables'][$tableName]['foreignKey']) &&  $foreignTable = $table->getForeignTable($options['tables'][$tableName]['foreignKey']) );
        */
        $breadCrumb = array_reverse($breadCrumb);
        $html = '';
        $html .= '<ul class="breadcrumb">';
        foreach($breadCrumb as $key=> $item){

            $class = '';

            if(isset($item['class']) && !empty($item['class'])){
                $class = $item['class'];
            }
            if($key == sizeof($breadCrumb) - 1){
                $active = ' class="active" ';
                $divider = '';
            }else{
                $active = '';
                $divider = '<li class="divider">&raquo;</li>';
            }

            $html .= '<li '.$active.'>';

            if($item['link'] !== false){
                $html .= '<a href="'.$item['link'].'" class="'.$class.'" >';
            }
            $html .= $item['display'];

            if($item['link'] !== false){
                $html .= '</a>';
            }

            $html .= '</li>';
            $html .= $divider;
        }
        $html .= '</ul>';

        return $html;
    }


    /**
    * Checks if the current table has foreign keys related to the table passed through the first parameter. If yes, it returns a string of the field name , else : false
    *
    * @param mixed $relatedToTable
    */
    function hasForeignKeyRelatedTo($relatedToTable,$exactly=false){
        $tableName = $this->getRawTableName();
        $relatedToTable = explode('_',$relatedToTable);
        $relatedToTable = $relatedToTable[0];
        if(!$exactly){
            $sql = "SELECT field_name FROM `system` WHERE table_name = '{$tableName}' AND foreign_field != '' AND foreign_table LIKE '%{$relatedToTable}%' LIMIT 1";
        }else{
            $sql = "SELECT field_name FROM `system` WHERE table_name = '{$tableName}' AND foreign_field != '' AND foreign_table LIKE '{$relatedToTable}' LIMIT 1";
        }
        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 0){
            return false;
        } else {
            $row = $res->FetchRow();

            return $row['field_name'];
        }
    }

    /**
    *Function to check if a table has a another table that depends on it, if yes, it returns an array of the dependent tables
    */
    function hasDependencies(){
        $tableName = $this->getRawTableName();
        $sql = "SELECT table_name FROM `system` WHERE foreign_table = '{$tableName}' ";
        // echo $sql;
        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 0){
            return false;
        } else {
            while($row = $res->FetchRow()){
                $array [] = $row;
            }

            return $array;
        }
    }

    public function siteMapTables(){
        global $systemTables;
        global $adminTables;
        global $user;
        $allTablesArray = array_flip($this->adodb->MetaTables('TABLES'));

        foreach($systemTables as $restrictedTableName){
            if(isset($allTablesArray[$restrictedTableName])){
                unset($allTablesArray[$restrictedTableName]);
            }
        }
        foreach($adminTables as $restrictedTableName){
            if(isset($allTablesArray[$restrictedTableName])){
                unset($allTablesArray[$restrictedTableName]);
            }
        }
        return $allTablesArray;
    }
    //List all tables
    //Make sure to unset the SYSTEM tables
    public function listAllTables(){
        global $systemTables;
        global $adminTables;
        global $user;
        $allTablesArray = array_flip($this->adodb->MetaTables('TABLES'));

        foreach($systemTables as $restrictedTableName){
            if(isset($allTablesArray[$restrictedTableName])){
                unset($allTablesArray[$restrictedTableName]);
            }
        }
        foreach($adminTables as $restrictedTableName){
            if(isset($allTablesArray[$restrictedTableName])){
                unset($allTablesArray[$restrictedTableName]);
            }
        }
        foreach($allTablesArray as $tableName=>$index){

            //Check if the user hass acecss to the following table
            if(!currentPageIs("showDatabase.php") && !currentPageIs("showTables.php")){
                if(!$user->isAllowed($tableName)){
                    unset($allTablesArray[$tableName]);
                }
            }
        }
        return $allTablesArray;

    }
    /**
    * Function that lists all tables that are editable by the user
    *
    */
    public function listAllEditableTables(){
        $allTables = $this->listAllTables();

        //Unset admin tables
        //These tables have their structure already inserted inside the 'system' table
        unset($allTables['admin_advanced_settings']);
        unset($allTables['site_options']);
        unset($allTables['cmsgen_default_values']);


        foreach($allTables as $table => $dumm){










            if(strpos($table,'cmsgen_') !== false){
                unset($allTables[$table]);
            }
        }

        return array_flip($allTables);
    }
    //Counts the total number of rows present in the table
    public function countAllRows(){
        $sql = "SELECT COUNT(*) FROM ".$this->tableName;
        $resultSet = $this->adodb->Execute($sql);
        $row = $resultSet->FetchRow();
        return $row['COUNT(*)'];
    }

    /**
    * Optimized list functions that uses joins
    *
    * @param mixed $options
    * @return mixed
    */
    public function optimizedList($options = array () ){
        $table = new TableRelationship($this->getRawTableName());

        return $table->findContainingAllBelongsTo($options);
    }

    //Lists all the entries in the given table
    public function listAllRows($offset=0,$limit=0){

        $offset = (int)$offset;
        $limit = (int)$limit;
        //Checks if a table is sortable
        $posExists = $this->isTableSortable();

        if($posExists){
            $orderBy = " ORDER BY pos ASC ";
        } else {
            $orderBy = " ORDER BY id DESC ";
        }

        $sql = "SELECT * FROM ".$this->tableName;
        //Advanced search start
        
        if(isset($_GET['action']) && $_GET['action'] == 'search' && isset($_POST['data']['Search']) ){
            unset($_POST['data']['Search']['submit']);
            $sqlArray = array ();

            foreach($_POST['data']['Search'] as $fieldName => $value){
                $fieldType = $this->getFieldType($fieldName);
                if(empty($value) && $fieldType != 'checkbox'){
                    continue;
                }
                if(!$this->fieldExists($fieldName)){
                    continue;
                }

                if(!is_numeric($value)){
                    $value = '%'.$value.'%';
                    $value = $this->escape($value);
                    $sqlArray [] = " {$fieldName} LIKE {$value} ";
                }else{
                    $value = $this->escape($value);
                    $sqlArray [] = " {$fieldName} = {$value} ";
                }
            }
            if(!empty($sqlArray)){
                $sql .= ' WHERE ';
                $sql .= join(' AND ',$sqlArray);
            }
        }


        //Advanced search end
        $sql .= $orderBy;
        if(!empty($limit)){
            $sql .= " LIMIT $limit ";
        }
        if(!empty($offset)){
            $sql .= " OFFSET $offset ";
        }

        $cache = new Cache($this->getRawTableName().'__'.md5($sql));
        if(false !== ($cachedObject = $cache->fetch())){
            return $cachedObject;
        }
        $resultSet = $this->adodb->Execute($sql);
        $resultArray = array();
        if($resultSet->RecordCount() == 0){
            return array ();
        }
        while($row = $resultSet->FetchRow()){
            $resultArray[] = $row;
        }

        //Forces the pos field to be as the last column
        foreach($resultArray as $k=>$row){
            foreach($row as $j => $value){
                if($j == 'pos'){
                    unset($row[$j]);
                    $row[$j] = $value;
                    $resultArray[$k] = $row;
                }
            }
        }

        $cache->store($resultArray);
        return $resultArray;

    }

    //Find an item by id, return an array or false
    public function findItemById($id){
        $id = (int)$id;

        $sql = "SELECT * FROM ".$this->tableName;
        $sql .= " WHERE id='{$id}' LIMIT 1;";

        $cache = new Cache($this->getRawTableName().'__'.md5($sql));
        if(false !== ($cachedObject = $cache->fetch())){
            return $cachedObject;
        }
        $resultSet = $this->adodb->Execute($sql);


        if($resultSet->RecordCount() == 1){
            $array = $resultSet->FetchRow();
        }
        $result = !empty($array) ? $array : false;

        $cache->store($result);
        return $result;
    }

    /**
    * Returns COUNT[(*) of the current table.
    *
    * @param string $where
    */

    public function countWhere($where=""){
        $sql = "SELECT COUNT(*) FROM ".$this->tableName;
        if(!empty($where)){
            $sql .= $where;
        }

        $sql .= " LIMIT 1";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 0){
            return 0;
        }else{
            $row = $res->FetchRow();
            return $row['COUNT(*)'];
        }
    }

    /**
    * Returns an array with paginated records, returns false if there are no results
    *
    * @param Pagination $pagination
    * @param string $orderBy
    * @return mixed
    */

    public function findPaginate($pagination,$orderBy=""){
        $sql = "SELECT * FROM ".$this->tableName;

        $offset = $pagination->offset();
        $perPage = $pagination->per_page;

        if(!empty($orderBy)){
            $sql .= ' '.$orderBy;
        }
        $sql .= " LIMIT {$offset},{$perPage}";
        $resultSet = $this->adodb->Execute($sql);

        if($resultSet->RecordCount() == 0){
            return false;
        }
        $array = array();
        while($row = $resultSet->FetchRow()){
            $array [] = $row;
        }
        return !empty($array) ? $array : false;
    }

    //Function that finds by sql
    public function executeSql($sql){
        $this->findSql($sql);
    }
    public function findSql($sql){

        $resultSet = $this->adodb->Execute($sql);

        if($resultSet && $resultSet->RecordCount() == 0){
            return false;
        }
        $array = array();
        while($resultSet && $row = $resultSet->FetchRow()){
            $array [] = $row;
        }
        return !empty($array) ? $array : false;
    }
    //Function that finds all the rows
    //Pagination is an object of type Pagination
    public function findAll($limit=0,$order=NULL,$direction="",$pagination=""){
        $limit = (int)$limit;
        $order = $order;

        $direction = $direction == 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM ".$this->tableName;
        if(!empty($order)){
            $sql .= " ORDER BY $order $direction ";
        }
        if(!empty($limit)){
            $sql .= " LIMIT $limit ";
        }
        $resultSet = $this->adodb->Execute($sql);

        if($resultSet->RecordCount() == 0){
            return false;
        }
        $array = array();
        while($row = $resultSet->FetchRow()){
            $array[] = $row;
        }
        return !empty($array) ? $array : false;
    }

    /**
    * Find anything from the selected table with limit and ordering. Array of the form 'conditions'=> array('fieldName =' => 'value')
    * 'conditions' => array('age >'=>19, 'name LIKE' => '%george%', 'gender'=>'male')
    * 'fields' => array('id','name','gender','age')
    * 'order' => 'NAME ASC, AGE DESC'
    * 'LIMIT' => '10, 5'
    * @param array $array
    * @param Pagination $pagination
    * @return mixed
    */
    public function safeFind($array,$pagination=null){


        $columnsSelectedString = '*';

        if (isset($array['fields']) && is_array($array['fields']) && !empty($array['fields'])) {
            $columnsSelected = $array['fields'];
            $columnsSelectedString = implode(',', $columnsSelected);
        }
        $select = '';
        if(isset($array['conditions']) && is_array($array['conditions']) && !empty($array['conditions'])){
            $sqlArray = array();

            foreach($array['conditions'] as $column=>$value){
                @list($columnName,$sign) = explode(' ',$column);
                if(empty($sign)){
                    $sign = '=';
                }
                $value = $this->adodb->qstr($value,self::$magicQuotes);
                $sqlArray [] = $columnName.' '.$sign.$value;
            }
            $select = " WHERE " . join(" AND ",$sqlArray);
        }

        $sql = "SELECT {$columnsSelectedString} FROM ".$this->tableName." {$select} ";



        if(isset($array['order']) && !empty($array['order'])){
            $sql .= ' ORDER BY '.$array['order'];
        }

        if(isset($pagination) && !empty($pagination)){
            $offset = $pagination->offset();
            $perPage = $pagination->per_page;
            $sql .= " LIMIT {$offset},{$perPage}";
        }

        if(isset($array['limit']) && !empty($array['limit']) && empty($pagination)){
            $limit = intval($array['limit']);
            $sql .= " LIMIT {$limit} ";
        }


        $resultSet = $this->adodb->Execute($sql);

        if($resultSet->RecordCount() == 0){
            return false;
        }
        $array = array();
        while($row = $resultSet->FetchRow()){
            $array [] = $row;
        }
        return !empty($array) ? $array : false;
    }
    /**
    * Find anything from the selected table with limit and ordering. Array of the form column_name=>field_value
    *
    * @param array $array
    * @param int $limit
    * @param string $order
    * @return array
    */
    public function findInTable($array,$limit=NULL,$order=""){
        $sqlArray = array();

        foreach($array as $column=>$value){
            $value = $this->adodb->qstr($value,self::$magicQuotes);
            $sqlArray [] = $column."=".$value;
        }
        $select = join(" AND ",$sqlArray);

        $sql = "SELECT * FROM ".$this->tableName." WHERE $select ";
        if(!empty($order)){
            $sql .= $order;
        }
        if(!empty($limit)){
            $sql .= " LIMIT $limit ;";
        }

        $res = $this->adodb->Execute($sql);
        $count = $res->RecordCount();
        if($count == 0){
            return false;
        }else{
            while($row = $res->FetchRow()){
                $result_array [] = $row;
            }
            return $result_array;
        }
    }
    /**
    * Function that returns a result array using the WHERE clause you supply.
    *
    * @param string $whereClause
    * @param array $columnsSelected
    * @return mixed
    */
    public function findWhere($whereClause, $columnsSelected = array() ){
        $columnsSelectedString = '*';
        if (is_array($columnsSelected) && !empty($columnsSelected)) {
            $columnsSelectedString = implode(',', $columnsSelected);
        }

        $sql = "SELECT ".$columnsSelectedString." FROM ".$this->tableName;

        if(strstr($whereClause,'WHERE')){
            str_replace('WHERE','',$whereClause);
        }
        if(strstr($whereClause,'where')){
            str_replace('where','',$whereClause);
        }

        $sql .= ' WHERE '.$whereClause;

        $resultSet = $this->adodb->Execute($sql);
        $array = array();
        while($row = $resultSet->FetchRow()){
            $array [] = $row;
        }
        return !empty($array) ? $array : false;
    }


    /**
    * Function that returns a result array with the search results of your supply.
    *
    * @param string $toFind
    * @param array $columnsSearched
    * @param array $columnsSelected
    * @param booelan $caseSensitive
    * @return mixed
    */
    public function searchFor($toFind,  $columnsSearched , $columnsSelected = array() , $caseSensitive = true){

        $array = array();
        $columnsSelectedString = '*';
        if (is_array($columnsSelected) && !empty($columnsSelected)) {
            $columnsSelectedString = implode(',', $columnsSelected);
        }

        $sql = "SELECT ".$columnsSelectedString." FROM ".$this->tableName;

        if(strstr($whereClause,'where')){
            str_replace('where','',$whereClause);
        }

        if (is_array($columnsSearched) && !empty($columnsSearched)) {
            $sql .= ' WHERE '; //.$whereClause;
            $counter = 0;
            foreach ($columnsSearched as $k=>$value) {
                if(isset($k) && $k != $counter){
                    $sql .=  (($caseSensitive) ? $k : " LOWER({$k})")  . " LIKE " . "'%" .
                    (($caseSensitive) ? $value : strtolower($value)). "%'";
                }else{
                    $sql .=   (($caseSensitive) ? $value : " LOWER({$value})" ) . " LIKE " . "'%" .
                    (($caseSensitive) ? $toFind : strtolower($toFind)) . "%'";
                }
                if(!($counter++ == count($columnsSearched) - 1))
                    $sql .= " OR ";
            }
            $sql .= "";
        }else{
            //$array ['Error'] = "You must provide columns to search in ! Parameter `columnsSearched` is empty or invalid." ;
        }

        $resultSet = $this->adodb->Execute($sql);
        while($row = $resultSet->FetchRow()){
            $array [] = $row;
        }
        return !empty($array) ? $array : false;
    }


    //function that returns the live edit attributes that should be placed inside the div tag
    public function getLiveEditAttr($id){
        global $session;
        if($session->is_logged_in()){
            $id = (int)$id;
            $tableName = str_replace("`","",$this->tableName);
            $tableName = str_replace("'","",$tableName);
            return ' itemId="'.$id.'" table="'.$tableName.'" ';
        }else{
            return '';
        }
    }

    //Function that compares the field names with the predefined common names to automatically determine the type of fields
    //Used in the installation of the cms, use getFieldType($fieldName) to return the type of this field
    public function returnFieldType($field_name){

        if(($fieldType = $this->getFieldType($field_name)) !== false){
            return $fieldType;
        }
        $sql_field_name = "%".$field_name."%"; //Wildcard used
        $sql_field_name = $this->adodb->qstr($sql_field_name,self::$magicQuotes);//Escape value
        $sql = "SELECT field_type FROM backend_structure WHERE common_name LIKE {$sql_field_name} ";

        $resultSet = $this->adodb->Execute($sql);

        $row = $resultSet->FetchRow();
        $field_type = $row['field_type'];
        //If the field type is not found in our database, try to guess it
        if(empty($field_type)){

            if(strpos($field_name,'_id') !== false){
                return 'foreign';
            }

            if(strpos($field_name,'picture') !== false || strpos($field_name,'pic') !== false || strpos($field_name,'background') !== false){
                return 'photo_upload';
            }

            $array =  $this->tableFieldsTypes;
            $typeLength =  $array[$field_name];
            list($type,$length) = explode(" ",$typeLength);

            switch($type){
                case 'int':
                switch($length){
                    case 1:
                        return 'checkbox';
                        break;
                    case 11:
                        return 'id';
                        break;
                    default:
                        return 'integer';
                        break;
                }
                break;
                case 'blob';
                    return 'textarea';
                    break;
                case 'date';
                    return 'date';
                    break;
                case 'timestamp';
                    return 'timestamp';
                    break;
                case 'string':
                    return 'textfield';
                    break;
            }
        }

        return (!empty($field_type) ? $field_type : 'Unknown');

    }
    //Ultility Function that sanitizes the table name variable
    protected function sanitizeTableNameVariable($table=""){
        if(empty($table))
            $tableName = $this->tableName;
        else
            $tableName = $table;
        $tableName = str_replace('`','',$tableName);
        $tableName = $this->adodb->qstr($tableName,self::$magicQuotes);

        return $tableName;

    }
    /**
    * Outputs a csv which is basically a table dump as CSV
    *
    */
    public function exportToCsv(){

        $fileName = $this->getRawTableName().'_'.date("Y-m-d").'.csv';

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        //header('Content-Disposition: attachment; filename=Customers_Export.csv');
        header("Content-Disposition: attachment;filename={$fileName}");
        header("Content-Transfer-Encoding: binary");
        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $allRows  = $this->listAllRows();
        $i = 0; $j = 0;
        foreach($allRows as $index=>$row){

            foreach ($row as $fieldName => $value){
                //Do not display position
                if($fieldName == 'pos'){
                    continue;
                }
                if(!$this->isFieldActive($fieldName)  || !$this->isVisible($fieldName)){
                    continue;
                }

                $fieldType = $this->returnFieldType($fieldName);

                if($fieldType == 'foreign'){
                    $row[$fieldName] = $this->getForeignKeyValue($row[$fieldName],$fieldName);
                }elseif($fieldType == 'checkbox'){
                    $row[$fieldName] = $row[$fieldName]==1 ? 'Yes' : 'No';
                }elseif($fieldType == 'date'){
                    $row[$fieldName] = formatDate($row[$fieldName]);
                }elseif($fieldType == 'photo_upload'){

                    continue;

                }elseif($fieldType == 'thumbnail'){
                    continue;
                }
                $fieldValue = substr(strip_tags($row[$fieldName]),0,100);

                if ($i == 0) {
                    $array[$i][$j] = $fieldName;
                }

                $array[$i+1][$j] = $fieldValue;

                $j++;
            }
            $i++;
        }


        ob_start();
        $handle = fopen("php://output", 'w');

        foreach ($array as $row) {
            fputcsv($handle, $row)."\n";
        }
        fclose($handle);
        echo ob_get_clean();

    }
    public function getRawTableName($table=""){
        if(empty($table))
            $tableName = $this->tableName;
        else
            $tableName = $table;

        $tableName = str_replace('`','',$tableName);
        $tableName = str_replace("'",'',$tableName);

        return $tableName;
    }
    //Ultitilty function to sanitize field name
    protected function sanitizeFieldName($fieldName){
        $fieldName = $this->adodb->qstr($fieldName,self::$magicQuotes);
        $withoutQuotesFieldName = str_replace('\'','',$fieldName);

        return $withoutQuotesFieldName;
    }
    //function that escapes sql injecting values
    protected function escape($value){
        return $this->adodb->qstr($value,self::$magicQuotes);
    }
    //function that returns bValidator parameters to the input html objects
    protected function getbValidatorParams($fieldType,$isRequired=0){
        $fieldType = $this->escape($fieldType);
        $sql = "SELECT bValidator FROM `backend_structure` WHERE field_type=$fieldType LIMIT 1;";
        $res = $this->adodb->Execute($sql);
        $row = $res->FetchRow();
        $result = $row['bValidator'];
        if($isRequired){
            if(isset($this->fieldType) && $this->fieldType == 'photo_upload' && !empty($this->id)){
                return $result;
            }
            $result .= !empty($result) ? ',required' : 'required';
        }
        return $result;
    }

    /**
    * Builds the query from the conditions
    *
    * @param mixed $conditions
    */
    protected function queryBuilder ( $conditions = array ()  ){
        $sql = "";
        $whereClause = "";
        $sql = "SELECT {$fieldsSQL} FROM `{$this->getRawTableName()}` AS  `{$this->getRawTableName()}` ";

        foreach($conditions['joins'] as $joinArray){
            $alias = isset($joinArray['alias']) ? $joinArray['alias'] : $joinArray['table'];
            $sql .= " {$joinArray['type']} JOIN `{$joinArray['table']}` AS `{$alias}` ON ( {$joinArray['conditions']} ) ";
        }

        if(isset($conditions['conditions']) && is_array($conditions['conditions']) && !empty($conditions['conditions'])){
            $sqlArray = array();

            foreach($conditions['conditions'] as $column=>$value){
                @list($columnName,$sign) = explode(' ',$column);
                if(empty($sign)){
                    $sign = '=';
                }
                $value = $this->adodb->qstr($value,self::$magicQuotes);
                $explode = explode('.',$columnName);
                if(sizeof($explode) == 2){
                    $aliasName = $explode[0];
                    $columnName = $explode[1];
                }else{
                    $aliasName = $this->getRawTableName();
                    $columnName = $explode[0];
                }

                $sqlArray [] = '`'.$aliasName.'`.`'.$columnName.'` '.$sign.$value;
            }
            $whereClause = " WHERE " . join(" AND ",$sqlArray);
        }

        $sql .= $whereClause;

        if(isset($options['limit'])){
            $options['limit'] = (int) $options['limit'];
            $sql .= " LITMIT {$options['limit']}";
        }

    }

    /**
    * Function that checks if the selected table is a table for photo gallery or not
    *
    */
    public function isPhotoGallery(){
        if ($this->tableName == '`product_images`') {
            return false;
        }
        if ( (strpos($this->tableName,'photos') !== false) || (strpos($this->tableName,'gallery') !== false) ||(strpos($this->tableName,'galleries') !== false) || (strpos($this->tableName,'images') !== false)){
            return true;
        }else{
            return false;
        }
    }

    /**
    * Checks if the current table supports subtables at the generate.php form
    * 
    */
    public function hasEditSubTables() {
        return true;
    }

    /**
    * Returns an array of tables related to the current table
    * 
    * @param mixed $tableName
    */
    public function getRelatedTables($tableName = NULL ) {
        if($tableName == NULL ){
            $tableName = $this->getRawTableName();
        }   

        $tableName = $this->escape($tableName);


        $sql = "SELECT field_name,table_name FROM system WHERE foreign_table = {$tableName} ";
        $res = (array) $this->findSql($sql);
        $result = array ();


        foreach($res as $index => $entry){
            if(empty($entry['table_name'])){
                continue;
            }
            $result[$index]['tableName'] = $entry['table_name'];   
            $result[$index]['foreignKey'] = $entry['field_name'];   
            $result[$index]['displayName'] = printTableName($entry['table_name']);   
        }

        return $result;

    }

    /**
    * Returns an array with the tables that will be listed as tabs. 
    * 
    */
    public function getEditSubTables(){
        return $this->getRelatedTables();
    }


    public function isFieldActive($fieldName){
        $fieldName = $this->adodb->qstr($fieldName,self::$magicQuotes);
        $tableName = $this->sanitizeTableNameVariable();
        $sql = "SELECT active FROM system WHERE table_name = $tableName AND field_name = $fieldName LIMIT 1;";


        $cache = new Cache($this->getRawTableName().'__'.md5($sql));
        if(false !== ($cachedObject = $cache->fetch())){
            return $cachedObject;
        }


        $resultSet = $this->adodb->Execute($sql);
        $row = $resultSet->FetchRow();

        $result = $row['active'] == 1 ? true : false;

        $cache->store($result);
        return $result;
    }


    //Function that returns the corresponding form element for the following field , text,country, select, etc...
    public function getFormElement($fieldName,$id=NULL,$versionId = NULL ,$formOptions = NULL){
        $element = new FormElement($this->getRawTableName(), $fieldName, $id , $versionId,$formOptions );
        return $element->displayFormElement();

    }

    /**
    * Function that returns the root table of the current foreign field. If multiple foreign fields are present in the same table, use the following format: products, product_categories, product_subcategories ...
    *
    */

    function getRootTable($foreignTable){
        $sql = "SELECT foreign_table, foreign_field FROM `system` WHERE table_name='{$foreignTable}' AND foreign_table != '' AND foreign_table != '{$foreignTable}' ";



        $res = $this->adodb->Execute($sql);
        $resultsFound = $res->RecordCount();
        if(!$resultsFound){
            return NULL;
        }
        if($resultsFound == 1){
            $row = $res->FetchRow();
            return $row['foreign_table'];
        } else {
            $temp = explode("_",$foreignTable);
            $tableName = reset($temp);
            //Remove the last character . Usually the plural part of the string
            $tableName = substr($tableName, 0, -1);
            $sql = "SELECT foreign_table, foreign_field FROM `system` WHERE table_name='{$foreignTable}' AND foreign_table LIKE '%{$tableName}%' ";
            $res = $this->adodb->Execute($sql);

            if($res->RecordCount() == 1){
                $row = $res->FetchRow();
                return $row['foreign_table'];
            } else {
                return NULL;

            }
        }
    }


    public function listAllCpanelLinks(){

        $sql = "SELECT * FROM `cmsgen_cpanel_links` WHERE active=1";
        $res = $this->adodb->Execute($sql);

        $resultArray = array();
        if($res->RecordCount() >= 1){
            while($row = $res->FetchRow()){
                $resultArray [] = $row;
            }
        }
        return !empty($resultArray) ? $resultArray : false;

    }
    //Function that returns a default value that is read from the database
    public function findDefaultValue($fieldType){
        $fieldType = $this->adodb->qstr($fieldType);
        $sql = "SELECT value FROM `cmsgen_default_values` WHERE field_type={$fieldType} LIMIT 1;";
        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            $value = $row['value'];
        }
        return !empty($value) ? $value : "";
    }

    //Grind's Socile Module

    public function installSocial(){
        $sql = "CREATE TABLE IF NOT EXISTS `social` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `social_network` varchar(100) NOT NULL,
        `url` varchar(255) NOT NULL,
        `icon` varchar(255) NOT NULL COMMENT 'icon path',
        `profile` varchar(255) NOT NULL,
        `active` int(1) unsigned NOT NULL,
        PRIMARY KEY (`id`),
        KEY `social_network` (`social_network`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        ('social', 'id', 'id', '', '', 1, '', 1),
        ('social', 'social_network', 'textfield', '', '', 1, '', 1),
        ('social', 'url', 'url', '', '', 1, '', 1),
        ('social', 'icon', 'photo_upload', '', '', 0, '', 1),
        ('social', 'profile', 'textfield', '', '', 0, '', 1),
        ('social', 'active', 'checkbox', '', '', 0, '', 1);";
        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
    }

    public function installShortCodes(){
        $sql = "CREATE TABLE IF NOT EXISTS `shortcodes` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `code` varchar(255) NOT NULL,
        `text` text NOT NULL,
        `description` text NOT NULL,
        `enabled` tinyint(1) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
        ";

        $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`,`tootltip`) VALUES
        ('shortcodes', 'id', 'id', '', '', 1, '', 1,''),
        ('shortcodes', 'code', 'textfield', '', '', 1, '', 1,''),
        ('shortcodes', 'text', 'textarea', '', '', 1, '', 1,''),
        ('shortcodes', 'description', 'textarea', '', '', 1, '', 1,'This is only used for CMS users. No changes will be made on website.'),
        ('shortcodes', 'enabled', 'checkbox', '', '', 1, '', 1,'')
        ";

        $sql3 = "CREATE TABLE IF NOT EXISTS `shortcodes_images` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `image` varchar(255) NOT NULL,
        `alt` varchar(255) NOT NULL,
        `caption` varchar(255) NOT NULL,
        `link_shortcode_id` varchar(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
        ";

        $sql4 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        ('shortcodes_images', 'id', 'id', '', '', 1, '', 1),
        ('shortcodes_images', 'image', 'photo_upload', '', '', 0, '', 1),
        ('shortcodes_images', 'alt', 'textfield', '', '', 0, '', 1),
        ('shortcodes_images', 'caption', 'textfield', '', '', 0, '', 1),
        ('shortcodes_images', 'link_shortcode_id', 'foreign', 'shortcodes', 'code', 1, '', 1)
        ";
        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
        $this->adodb->Execute($sql3);
        $this->adodb->Execute($sql4);
    }

    public function installNewsModule(){
        $sql = "CREATE TABLE `news` (
        `id` int(1) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `picture` varchar(255) NOT NULL,
        `text` text NOT NULL,
        `date` date NOT NULL,
        `active` int(1) NOT NULL,
        `highlighted` int(1) NOT NULL,
        `pos` int(11) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;";

        $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        ('news', 'id', 'id', '', '', 1, '', 1),
        ('news', 'name', 'textfield', '', '', 1, '', 1),
        ('news', 'picture', 'photo_upload', '', '', 1, '', 1),
        ('news', 'text', 'textarea', '', '', 1, '', 1),
        ('news', 'date', 'date', '', '', 0, '', 1),
        ('news', 'active', 'checkbox', '', '', 0, '', 1),
        ('news', 'highlighted', 'checkbox', '', '', 0, '', 1),
        ('news', 'pos', 'position', '', '', 0, '', 1);";
        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
    }


    public function installCVModule(){
        $sql = "CREATE TABLE `cvs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(250) NOT NULL,
        `profession` varchar(250) NOT NULL,
        `mobile` varchar(250) NOT NULL,
        `email` varchar(250) NOT NULL,
        `cv` varchar(250) NOT NULL,
        `message` longtext NOT NULL,
        `checked` int(1) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";

        $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        ('cvs', 'id', 'id', '', '', 1, '', 1),
        ('cvs', 'name', 'textfield', '', '', 0, '', 1),
        ('cvs', 'profession', 'textfield', '', '', 0, '', 1),
        ('cvs', 'mobile', 'textfield', '', '', 0, '', 1),
        ('cvs', 'email', 'email', '', '', 0, '', 1),
        ('cvs', 'cv', 'pdf_upload', '', '', 0, '', 1),
        ('cvs', 'message', 'textarea_nostyles', '', '', 0, '', 1),
        ('cvs', 'checked', 'checkbox', '', '', 0, '', 1);";
        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
    }


    public function installMailingList(){
        $sql = "
        CREATE TABLE `cmsgen_mailing_list` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `group_id` INT NOT NULL ,
        `email` VARCHAR( 255 ) NOT NULL,
        `verification` VARCHAR( 255 ) NOT NULL,
        `verified` tinyint( 1 ) NOT NULL
        ) ENGINE = MYISAM ;
        ";


        $sql2 = "
        INSERT INTO `system` (
        `id` ,
        `table_name` ,
        `field_name` ,
        `field_type` ,
        `foreign_table` ,
        `foreign_field` ,
        `mandatory` ,
        `parameters` ,
        `active`
        )
        VALUES (
        NULL , 'cmsgen_mailing_list', 'email', 'email', '', '', '1', '', '1'
        ), (
        NULL , 'cmsgen_mailing_list', 'group_id', 'foreign', 'cmsgen_mailing_list_groups', 'name', '1', '', '1'
        ), (
        NULL , 'cmsgen_mailing_list', 'id', 'id', '', '', '', '', '1'
        ), (


        NULL , 'cmsgen_mailing_list', 'verification', 'textfield', '', '', '', '', '1'
        ), (
        NULL , 'cmsgen_mailing_list', 'verified', 'checkbox', '', '', '', '', '1'
        ),

        (NULL, 'admin_advanced_settings', 'mailinglist_header', 'textarea', '', '', 0, '', 1),
        (NULL, 'admin_advanced_settings', 'mailinglist_footer', 'textarea', '', '', 0, '', 1);";

        $sql3 = "CREATE TABLE `cmsgen_mailing_list_groups` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `name` VARCHAR( 255 ) NOT NULL
        ) ENGINE = MYISAM ;";

        $sql4 = "INSERT INTO `system` (
        `id` ,
        `table_name` ,
        `field_name` ,
        `field_type` ,
        `foreign_table` ,
        `foreign_field` ,
        `mandatory` ,
        `parameters` ,
        `active`
        )
        VALUES (
        NULL , 'cmsgen_mailing_list_groups', 'id', 'id', '', '', '', '', '1'
        ), (
        NULL , 'cmsgen_mailing_list_groups', 'name', 'textfield', '', '', '1', '', '1'
        );";

        $sql5 = "ALTER TABLE `admin_advanced_settings` ADD `mailinglist_header` TEXT NOT NULL ,
        ADD `mailinglist_footer` TEXT NOT NULL ";

        $sql6 = "CREATE TABLE IF NOT EXISTS `cmsgen_mailing_list_queue` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `date` date NOT NULL,
        `message_id` int(11) NOT NULL,
        `sent` tinyint(1) NOT NULL,
        `subscriber_id` int(11) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        $sql7 = "CREATE TABLE IF NOT EXISTS `cmsgen_mailing_list_messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `subject` varchar(255) NOT NULL,
        `message` text NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
        $this->adodb->Execute($sql3);
        $this->adodb->Execute($sql4);
        $this->adodb->Execute($sql5);
        $this->adodb->Execute($sql6);
        $this->adodb->Execute($sql7);
    }


    public function installMainModule(){
        $sql = "CREATE TABLE `main` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `home_text` longtext NOT NULL,
        `copyright_notice` varchar(999) NOT NULL,
        `contact_page_text` longtext NOT NULL,
        `careers_page_text` longtext NOT NULL,
        `google_map_iframe` longtext NOT NULL,
        `corporate_pdf_catalogue` varchar(999) NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;";

        $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        ('main', 'id', 'id', '', '', 1, '', 1),
        ('main', 'home_text', 'textarea', '', '', 0, '', 1),
        ('main', 'copyright_notice', 'textfield', '', '', 0, '', 1),
        ('main', 'contact_page_text', 'textarea', '', '', 0, '', 1),
        ('main', 'careers_page_text', 'textarea', '', '', 0, '', 1),
        ('main', 'google_map_iframe', 'textarea_nostyles', '', '', 0, '', 1),
        ('main', 'corporate_pdf_catalogue', 'pdf_upload', '', '', 0, '', 1);";

        $sql3 = "INSERT INTO `main` VALUES(1, '<p>Enthusiastically orchestrate inexpensive interfaces and go forward schemas. Dramatically reintermediate resource-leveling schemas whereas standardized content. Efficiently impact user friendly resources for mission-critical human capital. Holisticly synthesize visionary imperatives via performance based manufactured products. Quickly restore granular channels with web-enabled channels. <br /><br />Synergistically simplify exceptional applications with installed base deliverables. Intrinsicly envisioneer focused networks vis-a-vis standardized resources. Objectively customize process-centric solutions before one-to-one ROI. Quickly initiate enterprise-wide technologies after professional leadership. Completely reconceptualize customer directed networks without distinctive relationships.</p>', '© All Rights Reserved LSD 2012', '<p>Enthusiastically orchestrate inexpensive interfaces and go forward schemas. Dramatically reintermediate resource-leveling schemas whereas standardized content. Efficiently impact user friendly resources for mission-critical human capital. Holisticly synthesize visionary imperatives via performance based manufactured products. Quickly restore granular channels with web-enabled channels. <br /><br /><strong>Tel:</strong> +000 0 000 000<br /><strong>Fax:</strong> +000 0 000 000<br /><br /><strong>email:</strong> info@domain-name.com</p>', '<p>Enthusiastically orchestrate inexpensive interfaces and go forward schemas. Dramatically reintermediate resource-leveling schemas whereas standardized content. Efficiently impact user friendly resources for mission-critical human capital. Holisticly synthesize visionary imperatives via performance based manufactured products. Quickly restore granular channels with web-enabled channels. <br /><br /><strong>Tel:</strong> +000 0 000 000<br /><strong>Fax:</strong> +000 0 000 000<br /><br /><strong>email:</strong> info@domain-name.com</p>', '', '');";


        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
        $this->adodb->Execute($sql3);
    }



    public function installMediaModule(){
        $sql = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        ('cmsgen_galleries', 'id', 'id', '', '', 0, '', 1),
        ('cmsgen_galleries', 'name', 'textfield', '', '', 0, '', 1),
        ('cmsgen_galleries', 'description', 'textarea', '', '', 0, '', 1),
        ('cmsgen_galleries', 'main_photo', 'photo_upload', '', '', 0, '', 1),
        ('cmsgen_galleries', 'active', 'checkbox', '', '', 0, '', 1),
        ('cmsgen_galleries', 'highlighted', 'checkbox', '', '', 0, '', 1),
        ('cmsgen_galleries', 'pos', 'position', '', '', 0, '', 1),


        ('cmsgen_gallerypictures', 'id', 'id', '', '', 0, '', 1),
        ('cmsgen_gallerypictures', 'gallery_id', 'foreign', 'cmsgen_galleries', 'name', 0, '', 1),

        ('cmsgen_gallerypictures', 'name', 'textfield', '', '', 0, '', 1),
        ('cmsgen_gallerypictures', 'description', 'textarea', '', '', 0, '', 1),
        ('cmsgen_gallerypictures', 'date', 'date', '', '', 0, '', 1),
        ('cmsgen_gallerypictures', 'active', 'checkbox', '', '', 0, '', 1),
        ('cmsgen_gallerypictures', 'highlighted', 'checkbox', '', '', 0, '', 1),
        ('cmsgen_gallerypictures', 'pos', 'position', '', '', 0, '', 1),

        ('cmsgen_gallerypictures', 'image_path', 'photo_upload', '', '', 0, '', 1)";

        $sql2 = "
        CREATE TABLE IF NOT EXISTS `cmsgen_galleries` (
        `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
        `name` VARCHAR( 255 ) NOT NULL ,
        `main_photo` VARCHAR( 255 ) NOT NULL ,
        `description` TEXT NOT NULL ,
        `active` TINYINT( 1 ) NOT NULL ,
        `highlighted` TINYINT( 1 ) NOT NULL ,
        `pos` INT( 11 ) NOT NULL ,
        PRIMARY KEY ( `id` )
        ) ENGINE = MYISAM ;
        ";

        $sql3 = "
        CREATE TABLE IF NOT EXISTS `cmsgen_gallerypictures` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `gallery_id` INT NOT NULL ,
        `image_path` VARCHAR( 255 ) NOT NULL ,

        `name` VARCHAR( 255 ) NOT NULL ,
        `date` DATETIME NOT NULL ,
        `description` TEXT NOT NULL ,
        `active` TINYINT( 1 ) NOT NULL ,
        `highlighted` TINYINT( 1 ) NOT NULL ,
        `pos` INT( 11 ) NOT NULL ,

        PRIMARY KEY ( `id` )
        ) ENGINE = MYISAM ;";

        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
        $this->adodb->Execute($sql3);
    }


    public function installInstructionsModule(){
        $sql = "CREATE TABLE `instructions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `text` longtext NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;";

        $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        ('instructions', 'id', 'id', '', '', 1, '', 0),
        ('instructions', 'name', 'textfield', '', '', 0, '', 1),
        ('instructions', 'text', 'textarea_nostyles', '', '', 0, '', 0);";

        $this->adodb->Execute($sql);
        $this->adodb->Execute($sql2);
    }


    //Returns a random value for the bulk insert
    public function getBulkValue($fieldName){
        $fieldType = $this->getFieldType($fieldName);
        $value = "";
        switch($fieldType){
            //Default value for each field type
            /*
            case 'url':
            $value = 'http://www.example.com';
            break;
            case 'textfield':
            $value = 'Lorem Ipsum';
            break;
            case 'textarea':
            $value = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sollicitudin vulputate enim, at egestas tellus tempus vitae. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam interdum dignissim tortor, ut consequat sem dignissim eu. Vestibulum consequat, justo sit amet commodo tristique, velit justo molestie lectus, ac scelerisque arcu ante et nulla. Fusce lacinia sapien sed sem pharetra volutpat. Mauris condimentum ultricies tempus. Sed mollis urna vitae urna lacinia imperdiet. Nulla facilisi. Quisque sagittis tincidunt orci. Aliquam erat volutpat. ';
            break;
            case 'photo_upload':
            $value = '/images/bulk.jpg';
            break;
            case 'email':
            $value="admin@example.com";
            break;
            */
            case 'checkbox':
                $value = rand(0,1);
                break;
            default:
                $value = $this->findDefaultValue($fieldType);
                break;

        }
        return !empty($value) ? $value : "";
    }
    //Function that does bulk inserts
    public function bulkInsert($numberOfRows,$tableName){

        if($this->tableExists($tableName)){

            $table = new Table($tableName);
            $tableFields = $table->getTableFields();
            for($i=1;$i<=$numberOfRows;$i++){
                $rowToSave = array();
                //Insert all the values into rowToSave
                foreach($tableFields as $fieldName){
                    $value = $table->getBulkValue($fieldName); //Stores the value of this field
                    $rowToSave[$fieldName] = $value;
                }
                //Save the array in each iteration
                $table->create($rowToSave);
            }
        }
    }


    //Function that checks if a table is sortable
	public function isTableSortable(){
		$sql = "SHOW COLUMNS FROM ".$this->tableName." LIKE 'pos'";
		$result = mysqli_query($sql);
		$resultSet = $this->adodb->Execute($sql);
		$row = $resultSet->FetchRow();
		
		//$posExists = (mysqli_num_rows($result))?TRUE:FALSE;
		$posExists = (!empty($row))?TRUE:FALSE;
		//echo 'TABLE POS '. ($posExists == true ? 'exists' : 'does not exist') ;
		return $posExists;
	}
    //Function that updates the row ordering / position
    public function updateRowPositions($order,$page){

        $pos = ($page*PER_PAGE) + 1;
        $table = $this->sanitizeTableNameVariable();
        for ($i = 0; $i < count($order); $i++) {
            if (is_numeric($order[$i])) {
                $sql = "
                UPDATE " . ($this->tableName) . " SET pos = " . $pos . " WHERE id = " . $order[$i];
                //echo $sql;
                $this->adodb->Execute($sql);
                $pos++;
            }
        }

    }
    /**
    * Returns an array containing information about the selected table.
    *
    */
    public function getTableInfo(){
        $tableName = $this->getRawTableName();
        $sql = "SELECT field_name,field_type FROM system WHERE table_name = '$tableName';";
        $resultSet = $this->adodb->Execute($sql);
        $result= array ();
        $i = 0;
        while(
            $row = $resultSet->FetchRow()
        ){
            $result[$i]['field_name'] = $row['field_name'];
            $result[$i]['field_type'] = $row['field_type'];
            $i++;
        }

        return $result;
    }
    //Returns the total number of rows in the given table
    protected function totalRows(){
        $sql =  "SELECT  COUNT(id) FROM ".$this->tableName;
        $resultSet = $this->adodb->Execute($sql);
        $row = $resultSet->FetchRow();
        return $row['COUNT(id)'];
    }
    //Function that returns the html column of the given field_type
    public function getFieldHTML($field_type="textfield"){
        $field_type = $this->adodb->qstr($field_type,self::$magicQuotes);
        $sql = "SELECT html FROM backend_structure WHERE field_type = $field_type LIMIT 1;";
        $resultSet = $this->adodb->Execute($sql);
        $row = $resultSet->FetchRow();

        return !empty($row['html']) ? $row['html'] : '';

    }

    /**
    * Returns an array containing information about the child tables
    * tableName, fieldName, foreignField
    *
    */
    public function getChildTableInformation(){
        $tableName = $this->getRawTableName();
        $sql = "SELECT table_name,field_name,foreign_field FROM `system` WHERE foreign_table='".$tableName."' ";

        $res = $this->adodb->Execute($sql);
        $i = 0;
        $result = array ();
        if($res->RecordCount() >= 1){
            while($row = $res->FetchRow()){
                $result [$i]['tableName'] = $row['table_name'];
                $result [$i]['fieldName'] = $row['field_name']; //Field where the drop down menu appears
                $result [$i]['foreignField'] = $row['foreign_field']; //Field where the drop down menu appears
                $i++;
            }
        }

        return $result;
    }

    public function isRequired($fieldName){
        $fieldName = $this->escape($fieldName);
        $tableName = $this->sanitizeTableNameVariable($this->tableName);

        $sql = "SELECT mandatory FROM system WHERE field_name = {$fieldName} AND table_name=".$tableName." LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            return $row['mandatory'];
        }else{
            return false;
        }
    }

    public function isActive($fieldName){
        $fieldName = $this->escape($fieldName);
        $tableName = $this->sanitizeTableNameVariable($this->tableName);

        $sql = "SELECT active FROM system WHERE field_name = {$fieldName} AND table_name=".$tableName." LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            return $row['active'];
        }else{
            return false;
        }
    }
    public function isVisible($fieldName){
        $fieldName = $this->escape($fieldName);
        $tableName = $this->sanitizeTableNameVariable($this->tableName);

        $sql = "SELECT is_visible FROM system WHERE field_name = {$fieldName} AND table_name=".$tableName." LIMIT 1;";


        $cache = new Cache($this->getRawTableName().'__'.md5($sql));
        if(false !== ($cachedObject = $cache->fetch())){
            return $cachedObject;
        }

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();

            $cache->store($row['is_visible']);
            return $row['is_visible'];
        }else{
            $cache->store(false);
            return false;
        }
    }

    public function getTooltip($fieldName){
        $fieldName = $this->escape($fieldName);
        $tableName = $this->sanitizeTableNameVariable($this->tableName);

        $sql = "SELECT tooltip FROM system WHERE field_name = {$fieldName} AND table_name=".$tableName." LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            return $row['tooltip'];
        }else{
            return false;
        }
    }


    //boolean function that checks if a field is mandatory or not
    public function isMandatoryField($fieldName,$fieldType){
        if(($mandatory = $this->isRequired($fieldName)) !== false){

            return $mandatory == 1 ? true : false;
        }
        $mandatoryFields = array('id','textarea','photo_upload','foreign');


        if(in_array($fieldType,$mandatoryFields))
            return true;
        else
            return false;
    }

    public function isActiveField($fieldName,$fieldType){
        if(($active = $this->isActive($fieldName)) !== false){

            return $active == 1 ? true : false;
        }
        //Default is active
        return true;
    }

    public function isVisibleField($fieldName,$fieldType){
        if(($active = $this->isVisible($fieldName)) !== false){

            return $active == 1 ? true : false;
        }
        //Default is active
        return true;
    }


    //Function that returns an array containing all the available field types
    public function returnAllAvailableFieldTypes(){
        if(empty($this->allAvailableFieldTypes)){
            $sql = "SELECT field_type FROM backend_structure";
            $resultSet = $this->adodb->Execute($sql);

            while($row = $resultSet->FetchRow()){
                $this->allAvailableFieldTypes [] = $row['field_type'];
            }
        }
        return $this->allAvailableFieldTypes;
    }

    //Check if CRUD is disabled (ADD/EDIT/DELETE)
    public function isCrudEnabled(){
        global $oneRowTables;
        $tableName = $this->sanitizeTableNameVariable();

        if(in_array($this->getRawTableName(),$oneRowTables)){
            return false;
        }
        $sql = "SELECT disable_crud FROM `table_options` WHERE table_name=$tableName LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            if($row['disable_crud'] == 1){
                return false;
            }
        }
        return true;
    }
    //Function that saves the table options
    public function saveTableOptions($tableName,$field,$value){

        $tableName = $this->sanitizeTableNameVariable($tableName);
        $value = $this->escape($value);
        $field = $this->escape($field);
        $originalFieldName = str_replace("'","",$field);
        $sql = "SELECT id FROM `table_options` WHERE table_name=$tableName LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $sql = "UPDATE `table_options` SET $originalFieldName=$value WHERE table_name=$tableName LIMIT 1;";
        } else {
            $sql = "INSERT into `table_options` (table_name,$originalFieldName) VALUES ($tableName,$value)";
        }
        //echo $sql;
        $this->adodb->Execute($sql);
    }

    /**
    * Returns the table option for the selected table
    *
    * @param string $optionName
    */
    public function getTableOptions($optionName){

        $tableName = $this->sanitizeTableNameVariable();
        $sql = "SELECT `{$optionName}` FROM `table_options` WHERE table_name=$tableName LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();

            return array_shift($row);
        }

        return false;
    }

    /**
    * Returns the urlencoded link formatted as shown in table_options table
    *
    * @param mixed $id
    */
    public function returnFormatedLink($id){
        $entry = $this->findItemById($id);


        if(isset(self::$tableLinkFormats[$this->getRawTableName()])){
            $urlFormat = self::$tableLinkFormats[$this->getRawTableName()];
        }else{
            $urlFormat = $this->getTableOptions('link_format');
            self::$tableLinkFormats[$this->getRawTableName()] = $urlFormat;
        }



        if(empty($urlFormat)){
            return $this->getRawTableName().'.php?id='.$id;
        }

        $pattern = '#\$[A-Za-z0-9_]+#';
        preg_match_all($pattern, $urlFormat, $matches);

        $matches = array_shift($matches);
        $url = $urlFormat;
        foreach($matches as $match){
            $fieldName = str_replace('$','',$match);
            $url = str_replace($match,$entry[$fieldName],$url);
        }
        return urlencode($url);
    }

    //Function that returns an array containing a fields parameters
    public function getFieldParameters($fieldName){

        $tableName = $this->sanitizeTableNameVariable();
        $fieldName = $this->adodb->qstr($fieldName);

        $sql = "SELECT parameters,field_type FROM `system` WHERE table_name=$tableName AND field_name=$fieldName LIMIT 1;";


        $res = $this->adodb->Execute($sql);
        $row = $res->FetchRow($res);
        $parameters = $row['parameters'];

        /**
        *If a foreign key
        */
        if($row['field_type'] == 'foreign'){
            if(empty($parameters)){
                return false;
            }else{
                $parameters = str_replace("|","___",$parameters);
                $parametersArray = parse_ini_string_1(str_replace(',', "\n", $parameters));
                if(isset($parametersArray['tables'])){
                    $tablesArray = explode('___', $parametersArray['tables']);
                    $parametersArray['tables'] = $tablesArray;
                }
                return $parametersArray;
            }
        }

        if(!empty($parameters)){
            $parametersArray = parse_ini_string_1(str_replace(',', "\n", $parameters));
            return $parametersArray;
        }

        return false;

    }


    //Function that updates the parameter for a field
    public function saveFieldParameters($value,$parameterName,$tableName,$fieldName){

        $table = new Table($tableName);

        $parametersArray = $table->getFieldParameters($fieldName);

        if($parametersArray !== false){
            $parametersArray[$parameterName] = $value;
        }else{
            $parametersArray = array();
            $parametersArray[$parameterName] = $value;
        }
        $parameters = array();
        foreach($parametersArray as $parameter=>$value){
            $parameters [] = $parameter.'='.$value;
        }

        $parameters = implode(",",$parameters);
        //$parameters = $parameterName."=".$value.",";


        $parameters = $this->adodb->qstr($parameters);
        $tableName = $this->adodb->qstr($tableName);
        $fieldName = $this->adodb->qstr($fieldName);

        //Update the parameters
        $sql = "UPDATE `system`
        SET `parameters` = $parameters
        WHERE `field_name` = $fieldName AND `table_name` = $tableName;";

        $this->adodb->Execute($sql);

    }
    //function that saved a row inside the table system
    public function saveSystemField($tableName,$fieldName,$fieldType,$mandatory,$foreignTable="",$foreignField="",$active="",$tooltip="",$visible=""){
        $tableName = $this->escape($tableName);
        $fieldName = $this->escape($fieldName);
        $fieldType = $this->escape($fieldType);
        $mandatory = $this->escape($mandatory);
        $tooltip = $this->escape($tooltip);
        $active = $this->escape($active);
        $visible = $this->escape($visible);
        $foreignTable = $this->escape($foreignTable);
        $foreignField = $this->escape($foreignField);
        //Check if a CMS update is being performed or not
        $checkExistSql = "SELECT id FROM system WHERE table_name=$tableName AND field_name=$fieldName";

        $res = $this->adodb->Execute($checkExistSql);
        if($res->RecordCount() == 0){
            //If record not found,create
            $sql = "INSERT into system (table_name,field_name,field_type,foreign_table,foreign_field,mandatory,active,tooltip,is_visible) VALUES ($tableName,$fieldName,$fieldType,$foreignTable,$foreignField,$mandatory,$active,$tooltip,$visible)";
        }else{
            //If record found, update
            $sql = "UPDATE system set field_type=$fieldType,foreign_table=$foreignTable,foreign_field=$foreignField,mandatory=$mandatory,active=$active,is_visible=$visible,tooltip=$tooltip WHERE (table_name=$tableName AND field_name=$fieldName) LIMIT 1;";
        }


        $this->adodb->Execute($sql);


    }
    /**
    * Returns the foreign field of a specific fieldName
    *
    * @param string $fieldName
    */

    public function getForeignField($fieldName){
        $fieldName = $this->escape($fieldName);
        $tableName = $this->sanitizeTableNameVariable($this->tableName);

        $sql = "SELECT foreign_field FROM system WHERE field_name = {$fieldName} AND table_name=".$tableName." LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            return $row['foreign_field'];
        }else{
            return false;
        }
    }


    /**
    * Function that returns the display field for the selected table
    *
    */
    public function getDisplayField(){
        $tableName = $this->getRawTableName();

        if(isset(self::$tableDisplayFields[$tableName])){
            return self::$tableDisplayFields[$tableName];
        }

        ##Search for the display field in the table_options
        $sql = "SELECT `display_fields` FROM `table_options` WHERE `table_name` = '{$tableName}' LIMIT 1";
        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            self::$tableDisplayFields[$tableName] = $row['display_fields'];
            return $row['display_fields'];
        }
        ##Search for the display field from the system table
        $sql = "SELECT foreign_field FROM `system` WHERE foreign_table = '{$tableName}' LIMIT 1;";
        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            self::$tableDisplayFields[$tableName] = $row['foreign_field'];
            return $row['foreign_field'];
        }
        ##No display field has been defined

        self::$tableDisplayFields[$tableName] = false;
        return false;

    }


    /**
    * Returns true if a field exists and false otehrwise
    *
    * @param string $fieldName
    */
    public function fieldExists($fieldName){
        $fieldName = $this->escape($fieldName);
        $res = $this->adodb->Execute("SHOW COLUMNS FROM {$this->tableName} LIKE $fieldName ");
        return ($res->RecordCount()) ? TRUE : FALSE;
    }

    /**
    * Update a single column in the database
    *
    * @param mixed $id
    * @param mixed $fieldName
    * @param mixed $value
    */
    public function updateField($id,$fieldName,$value){

        $id = intval($id);
        $value = $this->adodb->qstr($value,self::$magicQuotes); //Sanitize
        //End checkbox update
        $sql  = "UPDATE ".$this->tableName." SET `{$fieldName}` = $value ";
        $sql .= " WHERE id='{$id}' LIMIT 1;";

        $res = $this->adodb->Execute($sql);

        if($this->adodb->Affected_Rows() == 1){
            Cache::deleteByTableName($this->getRawTableName());
            return true;
        }else{
            return false;
        }
    }

    /**
    * Returns the foreign table of a specific fieldName
    *
    * @param string $fieldName
    */

    public function getForeignTable($fieldName){
        $fieldName = $this->escape($fieldName);
        $tableName = $this->sanitizeTableNameVariable($this->tableName);

        $sql = "SELECT foreign_table FROM system WHERE field_name = {$fieldName} AND table_name=".$tableName." LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            return $row['foreign_table'];
        }else{
            return false;
        }
    }

    //Return an array with the table's fields names, types and lengths fieldName=>fieldTypeAndLength
    public function returnFieldsNamesTypes(){
        return $this->tableFieldsTypes;
    }
    //Function that stores in an array the field name, type and length
    protected function getFieldsNamesTypes(){

        if(isset(self::$fieldsNamesTypes [$this->getRawTableName()])){
            $this->tableFieldsTypes =   self::$fieldsNamesTypes [$this->getRawTableName()];
            return;
        }

        $sql = "SELECT * FROM ".$this->tableName." LIMIT 1;";

        $resultSet = $this->adodb->Execute($sql);
        $totalFields = $resultSet->FieldCount();

        for($i = 0; $i<$totalFields; $i++){
            $field = $resultSet->FetchField($i);
            $this->tableFieldsTypes[$field->name] = $field->type . " ". $field->max_length;
        }

        self::$fieldsNamesTypes [$this->getRawTableName()] = $this->tableFieldsTypes;
    }
    //function that returns the foreign key value for a certain field
    public function getForeignKeyValue($id,$fieldName){

        $sanitizedTable = $this->sanitizeTableNameVariable();
        $fieldName = $this->escape($fieldName);
        $sql = "SELECT foreign_table,foreign_field FROM `system` WHERE table_name=$sanitizedTable AND field_name=$fieldName LIMIT 1; ";
        $resultSet = $this->adodb->Execute($sql);

        $row = $resultSet->FetchRow();
        $foreign_table = $row['foreign_table'];
        $foreign_field = $row['foreign_field'];
        $id = $this->escape($id);
        $sql = "SELECT $foreign_field FROM `$foreign_table` WHERE id=$id";

        $resultSet = $this->adodb->Execute($sql);
        $row = $resultSet->FetchRow();

        return $row[$foreign_field];
    }
    //Function that stores in an array the field names of the the given table
    public function getTableFields(){
        $sql = "SELECT * FROM ".$this->tableName." LIMIT 1;";

        $resultSet = $this->adodb->Execute($sql);
        $totalFields = $resultSet->FieldCount();
        $fieldsArray = array();
        for($i = 0; $i<$totalFields; $i++){
            $field = $resultSet->FetchField($i);
            $fieldsArray [] = $field->name;
        }


        //Forces the pos field to be as the last column
        foreach($fieldsArray as $k=>$value){
            if($value == 'pos'){
                unset($fieldsArray[$k]);
                $fieldsArray[$k] = $value;
            }

        }

        return $fieldsArray;
    }
    /**
    * Function that santizes the data before it is entered into the database.
    *
    * @param mixed $key
    * @param mixed $value
    * @return mixed
    */
    protected function setValues($key,$value){
        $fieldType = $this->returnFieldType($key);

        if($fieldType == 'checkbox'){
            if($value == 'on')
                return 1;
            else
                return 0;
        }

        if($fieldType == 'textarea'){
            //Purify the HTML before inserting it into the database.

            //                return htmlPurify($value);
            return $value;
        }

        return $value;
    }

    public function createBackend(){
        $dbms_schema = LIB_PATH.DS."cmsgen.sql";
        $sql_query = @fread(@fopen($dbms_schema, 'r'), @filesize($dbms_schema)) or die('problem ');
        $sql_query = remove_remarks($sql_query);
        $sql_query = split_sql_file($sql_query, ';');

        foreach($sql_query as $query){
            $this->adodb->Execute($query);
        }

    }

    /**
    * Process multiple file uploads.
    *
    * @param array $postValues
    */

    protected function processMultipleUploadFiles(&$postValues){


        $files = $_FILES;
        //Flag
        $postValues['alreadyProcessedMultipleUpload'] = true;

        foreach($files as $image){

            $fileName = array_keys($files);
            $fileName = array_shift($fileName);
            unset($_FILES[$fileName]);


            for($i = 0; $i < sizeof($image['name']); $i++){
                $name = $image['name'][$i];
                $type = $image['type'][$i];
                $size = $image['size'][$i];
                $tmp_name = $image['tmp_name'][$i];
                $error = $image['error'][$i];

                $uploadFile = array();
                $updoadFile['name'] = $name;
                $updoadFile['type'] = $type;
                $updoadFile['size'] = $size;
                $updoadFile['tmp_name'] = $tmp_name;
                $updoadFile['error'] = $error;

                $_FILES[$fileName] = $updoadFile;
                $this->create($postValues);

            }
        }

        return true;
    }

    //check the files to be uploaded,upload them and save them in the postValues
    protected function processUploadFiles(&$postValues){

        if(isset($_FILES)){
            foreach($_FILES as $field_name=>$file){
                if(!empty($file['name'])){

                    //Get the target path of the file
                    $target_path = PUBLIC_PATH.DS. $this->getDestinationFileName($file);
                    if(file_exists($target_path)){
                        //If the file exists, rename it with a unique name
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $file['name'] = str_replace('.'.$ext,'',$file['name'].'-'.uniqid()).'.'.$ext;
                    }

                    if($this->validMimeType($file,$field_name)){
                        $postValues[$field_name] = $this->getDestinationFileName($file); //path to the file, to be saved in the database
                        if(!($this->uploadFile($file))){
                            throw new Exception("Upload failed for ".$file['name']);
                            /*
                            return 'file_upload_failed'; // File not uploaded!
                            */

                        }else{
                            ##Check for additional restrictions once the file is uploaded##
                            $parameters = $this->getFieldParameters($field_name);
                            if(!empty($parameters)){
                                //Absolute file path on the server
                                $filePath = PUBLIC_PATH.DS.$postValues[$field_name];
                                foreach($parameters as $restriction=>$value){
                                    if(method_exists($this,$restriction)){
                                        $this->$restriction($filePath,$value);
                                        /*
                                        if(!$this->$restriction($filePath,$value)){
                                        return "invalid_".$restriction;
                                        }
                                        */
                                    }
                                }
                            }
                        }
                    }else{
                        throw new Exception("The file {$file['name']} has an invalid file type.");
                        /*
                        return 'invalid_mime_type'; //Invalid file type!
                        */
                    }
                }
            }
        }

        return true;
    }
    //Function that checks the max image width
    protected function max_image_width($filePath,$maxWidth){
        list($width,$height) = getimagesize($filePath);

        if ($width > $maxWidth)
        {
            unlink($filePath);
            throw new Exception("Maximum image width is ".$maxWidth."px");
            /*
            global $session;
            $session->message("Maximum image width is ".$maxWidth."px");
            unlink($filePath);
            return false; //Fail
            */
        }else{
            return true; //Success
        }
    }
    //Function that checks the max image height
    protected function max_image_height($filePath,$maxHeight){
        list($width,$height) = getimagesize($filePath);

        if ($height > $maxHeight)
        {
            unlink($filePath);
            throw new Exception("Maximum image height is ".$maxHeight."px");
            /*
            global $session;
            $session->message("Maximum image height is ".$maxHeight."px");
            unlink($filePath);
            return false; //Fail
            */
        }else{
            return true; //Success
        }
    }
    //Function that checks the image proprtions
    protected function image_proportions($filePath,$proportion){
        list($width,$height) = getimagesize($filePath);
        $imageProportions = $width/$height;
        if ($imageProportions < ($proportion-0.1) || $imageProportions > ($proportion + 0.1))
        {
            unlink($filePath);
            throw new Exception("Image proportions should be ".$proportion);
            /*
            global $session;
            $session->message("Image proportions should be ".$proportion);
            unlink($filePath);
            return false; //Fail
            */
        }else{
            return true; //Success
        }
    }

    /**
    * Delete the value from a single column
    *
    * @param integer $id
    * @param string $column
    */
    public function deleteColumn($id,$column){
        $id = intval($id);
        $row = $this->findItemById($id);

        $column = preg_replace('/[^\w-]/', '', $column);

        $sql  = "UPDATE ".$this->tableName." SET ";
        $sql .= " `{$column}` = '' ";
        $sql .= " WHERE id='{$id}' LIMIT 1;";

        $fieldType = $this->returnFieldType($column);

        if($fieldType == 'photo_upload' || $field_type == 'pdf_upload' || $field_type == 'mp3_upload' ){
            $filePath = PUBLIC_PATH.DS.$row[$column];
            if(file_exists($filePath)){
                unlink(PUBLIC_PATH.DS.$row[$column]);
            }
        }
        if($this->adodb->Execute($sql)){
            Cache::deleteByTableName($this->getRawTableName());
            return true;
        } else {
            return false;
        }

    }
    public function deleteRows($ids){
        foreach($ids as $id){
            $id = (int)$id;
            $this->delete($id);
            //$sql = "DELETE FROM ".$this->tableName." WHERE id='$id' LIMIT 1;";
            //                $this->adodb->Execute($sql);
        }
    }


    /**
    * Returns the number of versions for this specific entry
    *
    * @param mixed $id
    */

    function countContentHistoryById($id){

        if(!$this->tableExists('cmsgen_contenthistory')){
            return 0;
        }

        $id = intval($id);
        $sql = "SELECT COUNT(*) FROM `cmsgen_contenthistory` WHERE `entry_id` = '{$id}' AND `table_name` = '{$this->getRawTableName()}'  ";
        $res = $this->adodb->Execute($sql);

        $row = $res->FetchRow();

        return --$row['COUNT(*)'];
    }


    /**
    * Returns the versions for this specific entry
    *
    * @param mixed $id
    */

    function getContentHistoryByRowId($id){
        $id = intval($id);
        $sql = "SELECT * FROM `cmsgen_contenthistory` WHERE `entry_id` = '{$id}' AND `table_name` = '{$this->getRawTableName()}' ORDER BY created DESC ";
        $res = $this->adodb->Execute($sql);

        $resultArray  = array ();

        if($res->RecordCount() > 0){
            while( $row = $res->FetchRow() ){
                $resultArray [] = $row;
            }
        }
        return  $resultArray;
    }

    /**
    * Returns the version by id
    *
    * @param mixed $id
    */

    function getContentHistoryById($id){
        $id = intval($id);
        $sql = "SELECT * FROM `cmsgen_contenthistory` WHERE `id` = '{$id}' AND `table_name` = '{$this->getRawTableName()}' ORDER BY created DESC ";
        $res = $this->adodb->Execute($sql);

        $resultArray  = array ();

        if($res->RecordCount() > 0){
            while( $row = $res->FetchRow() ){
                $resultArray [] = $row;
            }
        }
        return  !empty($resultArray) ? array_shift($resultArray) : false ;
    }



    //Function that stores in an array the field name, type and length
    protected function getColumnTypes(){
        $sql = "SELECT * FROM `system` WHERE table_name = '{$this->getRawTableName()}' AND field_name != 'id' ";
        $resultSet = $this->adodb->Execute($sql);

        $array = array();
        while($row = $resultSet->FetchRow()){
            $array[$row['field_name']] = array ('field_name' => $row['field_name'], 'field_type' => $row['field_type'], 'mandatory' => $row['mandatory'] , 'active' => $row['active']);
        }

        $this->columnInfo = $array;

    }


    /**
    * Funciton that takes the postValues that are being inserted into the database and saves them as a json encoded string for later revision
    *
    * @param mixed $postValues
    */
    public function contentHistorySave($postValues){
        $tableName = $this->getRawTableName();
        $entryId = (int)$this->currentId;
        global $user;
        $userId = (int) $user->id;

        $data = $this->escape(json_encode($postValues));
        $sql  = "INSERT INTO `cmsgen_contenthistory` (`table_name`,`entry_id`,`data`,`edited_by` ) VALUES ('$tableName','$entryId',$data,$userId) ";

        $this->adodb->Execute($sql);
    }

    /**
    * Handles steps to be taken after validation
    *
    * @param mixed $postValues
    */
    public function afterValidate(&$postValues){
        $v = new AfterValidation($this->getRawTableName(),$postValues);
        $postValues = AfterValidation::$postValues;
    }


    /**
    * Handles steps to be taken after validation
    *
    * @param mixed $postValues
    */
    public function afterSave($postValues){
        $v = new AfterSave($this->getRawTableName(),$postValues , $this->id);
    }

    //Create or Update the fields
    public function save($postValues){

        $v = new Validation($this->getRawTableName(),$postValues);
        //$postValues = Validation::$postValues;
        $this->afterValidate($postValues);

        Cache::deleteByTableName($this->getRawTableName());

        $result = !empty($postValues['id']) ? $this->update($postValues) : $this->create($postValues);

        if(!empty($postValues['id'])){
            $this->id = intval($postValues['id']);
        }else{
            $this->id  = intval($result);
        }

        $this->afterSave($postValues);
        return $result;
    }
    //Function that creates a table entry no matter what the table is
    public function create(&$postValues,$skipUploadProcess = false){
        // user level needed used in the mass insert hack
        global $user;

        $fieldsArray = $this->getTableFields();
        unset($fieldsArray[0]); //Unset the ID
        $fieldsToSave = array(); //Array containing the fields names and values


        ##Upload Files ##

        //If m ultiple file uploading:
        //  if($this->isPhotoGallery() && !isset($postValues['alreadyProcessedMultipleUpload'])){
        //                echo 'not processed';
        //                $uploadResult = $this->processMultipleUploadFiles($postValues);
        //Last isnerted ID
        //                $insertedID = $this->adodb->Insert_ID();
        //                return $insertedID;
        // if single file uploading
        //            } else {

        if (!$skipUploadProcess) {
            $uploadResult = $this->processUploadFiles($postValues);
        }
        //  }



        ## END FILE UPLOAD

        foreach($postValues as $key=>$value){
            if(in_array($key,$fieldsArray)){
                $value = $this->setValues($key,$value);
                $key = $this->sanitizeFieldName($key);

                // Custom Slug handling by ps
                // if slug is empty, we fill it out programatically, otherwise we leave it
                if ( $key == 'slug' && empty($value) && ( isset($postValues['title']) || isset($postValues['name']) ) ){
                    $value = ( isset($postValues['title']) ) ? $postValues['title'] : $postValues['name'];
                    $value = strtolower( Slug($value) );
                }
                // End Custom

                $fieldsToSave['`'.$key.'`'] = $this->adodb->qstr($value,self::$magicQuotes); //Sanitize
            }
        }

        $sql  = "INSERT INTO ".$this->tableName." (";
        $sql .= join(", ", array_keys($fieldsToSave));
        $sql .= ") VALUES (";
        $sql .= join(", ", array_values($fieldsToSave));
        $sql .= ")";

        // by Patrick
        // Mass add rows hack for super admins only
        // Loop through the current table's fields and output the corresponding form
        $hack_multiplier_field = ( isset($_POST['hack_multiplier_field']) && !empty($_POST['hack_multiplier_field']) ) ? intval($_POST['hack_multiplier_field']) : 1;
        $loopCount = ( $user->user_level == 9 && $hack_multiplier_field < 11 ) ? $hack_multiplier_field : 1;

        for ( $loop=0; $loop < $loopCount; $loop++):
            if(!($this->adodb->Execute($sql))){
                throw new Exception($this->adodb->ErrorMsg());
                // return false;
            }
            $insertedID = $this->adodb->Insert_ID();
            $this->currentId = $insertedID;

            $action = 'Created entry. table: '.$this->getRawTableName().' id('.$insertedID.')';
            if($insertedID !== false){
                global $user;
                if(isset($user))
                    $log = new Log($user->username,true,$action);

                $this->contentHistorySave($postValues);
            }else{
                global $user;
                $log = new Log($user->username,false,$action);
            }
            endfor;


        return $insertedID;
    }


    //Function to return the target path of the file to be uploaded, path to be saved in the database
    //Parameter: an array of type $_FILES
    protected function getDestinationFileName($file){
        $tableName = str_replace("`","",$this->tableName);



        $fileName = basename($file['name']);
        $extension = ShowFileExtension($fileName);
        $fileName = str_replace('.'.$extension,'',$fileName);
        $fileName = seoUrl($fileName.date('Y-m-d h:i:s')).'.'.$extension;
        $fileType = $file['type'];

        if($fileType == 'application/pdf'){
            $destination  = PDF_DIR.DS.$tableName.DS.$fileName;
            $folderDestination = IMAGES_PATH.DS.PDF_DIR.DS.$tableName;
        }else{
            $destination  = IMAGE_DESTINATION.DS.$tableName.DS.$fileName;
            $folderDestination = IMAGES_PATH.DS.IMAGE_DESTINATION.DS.$tableName;
        }


        if(!is_dir($folderDestination)){
            mkdir($folderDestination,0755,true);
        }
        return $destination;
    }
    //Function that returns the type of the field from the selected table
    public function getFieldType($field_name){

        $originalFieldName = $field_name;
        if(isset(self::$fieldTypesByTable[$this->getRawTableName()][$field_name])){
            return self::$fieldTypesByTable[$this->getRawTableName()][$field_name];
        }
        $field_name = $this->adodb->qstr($field_name,self::$magicQuotes);//Escape value
        $tableName = $this->sanitizeTableNameVariable($this->tableName);

        $sql = "SELECT field_type FROM system WHERE field_name = {$field_name} AND table_name=".$tableName." LIMIT 1;";

        $res = $this->adodb->Execute($sql);
        if($res->RecordCount() == 1){
            $row = $res->FetchRow();
            self::$fieldTypesByTable[$this->getRawTableName()][$originalFieldName] = $row['field_type'];
            return $row['field_type'];
        }else{
            return false;
        }
    }
    //boolean function that checks whether the file extension to be uploaded is valid or not
    protected function validMimeType($file,$field_name){
        $fieldType = $this->getFieldType($field_name);

        $fileType = $file['type'];

        if($fieldType == 'photo_upload'){
            //Array containing all valid image types
            $validImageTypes = array('image/jpeg','image/png','image/gif','image/jpg','image/x-icon');
            if(in_array($fileType,$validImageTypes)){
                return true;
            }else{
                return false;
            }
            // pdf
        }elseif($fieldType == 'pdf_upload'){

            if(in_array ($fileType, array ( 'application/pdf' ,'application/octet-stream'))){
                return true;
            }
            // mp3
        }elseif($fieldType == 'mp3_upload'){
            $validAudioTypes = array('audio/mpeg','audio/mp3', 'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg');
            if(in_array($fileType,$validAudioTypes)){
                return true;
            }
        }

        //other wise return false
        return false;
    }


    //function that uploads a file, simple upload without checking
    protected function uploadFile($file){

        $target_path = IMAGES_PATH.DS. $this->getDestinationFileName($file);

        if(@move_uploaded_file($file['tmp_name'], $target_path)) {
            return true;
        }else{

            return false;
        }
    }
    //Function that updates a table entry no matter what the table is
    public function update(&$postValues){

        //Delete the cache
        Cache::deleteByTableName($this->getRawTableName());

        $fieldsArray = $this->getTableFields();
        unset($fieldsArray[0]); //Unset the ID
        $attribute_pairs = array();
        if(!isset($postValues['id'])){
            return $this->create($postValues);
        }
        $id = (int)$postValues['id'];
        $this->currentId = $id;


        //Upload Files
        $uploadResult = $this->processUploadFiles($postValues);
        if($uploadResult !== true){
            return $uploadResult;
        }

        foreach($postValues as $key=>$value){
            if(in_array($key,$fieldsArray)){

                $value = $this->setValues($key,$value);
                $key = $this->sanitizeFieldName($key);

                // Custom Slug handling by ps
                // if slug is empty, we fill it out programatically, otherwise we leave it
                if ( $key == 'slug' && empty($value) && ( isset($postValues['title']) || isset($postValues['name']) ) ){
                    $value = ( isset($postValues['title']) ) ? $postValues['title'] : $postValues['name'];
                    $value = strtolower( Slug($value) );
                }
                // End Custom

                $value = $this->adodb->qstr($value,self::$magicQuotes); //Sanitize
                $attribute_pairs[] = "`{$key}`={$value}";

            }
        }
        //Update checkboxes
        /**
        $emptyValuesArrays =  array_diff_key(array_flip($fieldsArray),$postValues);
        if(!empty($emptyValuesArrays)){
        foreach($emptyValuesArrays as $fieldName=>$value){
        if($this->returnFieldType($fieldName) == 'checkbox'){
        $fieldName = $this->sanitizeFieldName($fieldName);
        $attribute_pairs[] = "{$fieldName}='0'";
        }
        }
        }
        **/

        //End checkbox update
        $sql  = "UPDATE ".$this->tableName." SET ";
        $sql .= join(", ", $attribute_pairs);
        $sql .= " WHERE id='{$id}' LIMIT 1;";

        $res = $this->adodb->Execute($sql);

        $action = 'Updated table: '.$this->getRawTableName().' id('.$id.')';
        if($this->adodb->Affected_Rows() == 1){
            global $user;
            $log = new Log($user->username,true,$action);

            $this->contentHistorySave($postValues);
            return true;
        }else{
            global $user;
            $log = new Log($user->username,false,$action);
            return false;
        }
    }

    //Function that deletes
    public function delete($id){

        Cache::deleteByTableName($this->getRawTableName());
        $id = (int)$id;
        $dependentForeignTablesArray = $this->hasDependencies();

        //If table has dependencies
        if($dependentForeignTablesArray){
            foreach($dependentForeignTablesArray as $row){
                //Check if each of the table has an entry added. If yes, do not allow the user to delete its parent
                $tableName = $row['table_name'];

                $table = new Table($tableName);
                $currentTableForeignKey = $table->hasForeignKeyRelatedTo($this->getRawTableName());
                $result = $table->findWhere("$currentTableForeignKey = '{$id}' ");
                if(!empty($result)){
                    throw new Exception("Please deleted related items in " . printTableName($tableName) . " first.");
                }
            }
        }

        //You can now proceed to deleting
        $sql = "DELETE FROM ".$this->tableName;
        $sql .= " WHERE id=". $this->adodb->qstr($id,self::$magicQuotes);
        $sql .= " LIMIT 1";
        $resSet = $this->adodb->Execute($sql);
        $deleted = ($this->adodb->Affected_Rows() == 1) ? true : false;
        $action = 'Deleted id='.$id.' from table: '.$this->getRawTableName();

        global $user;
        $log = new Log($user->username,$deleted,$action);

        return $deleted;
    }
}
?>