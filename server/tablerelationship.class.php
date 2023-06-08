<?php
    class TableRelationship extends Table {

        var $forcedTraversal = NULL; 

        /**
        * Array of the table relationships you want to fetch
        * 
        */
        public $contains = NULL ;

        /**
        * Returns an array containg the name of the tables where the realtionship type is HAS MANY
        * 
        */
        public function hasManyTables(){
            $sql = "SELECT `table_name` FROM `system` WHERE `foreign_table` = '{$this->getRawTableName()}' ";
            $result = $this->findSql($sql);

            $return = array ();
            if ( is_array($result)) {
                foreach ( $result as $row){
                    $return[] = $row['table_name'];
                }
            }
            return $return;
        }

        /**
        * Returns an array containg the name of the tables where the realtionship type is HAS MANY
        * 
        */
        public function belongsToTables() {

            $sql = "SELECT `table_name` AS `table_name` , `field_name` AS `foreign_key` , `foreign_table` AS `join_table` FROM `system` WHERE `table_name` = '{$this->getRawTableName()}' AND `field_type` = 'foreign' ";

            $result = $this->findSql($sql);


            return $result === false ? array () : $result;
        }

        /**
        * Returns an array containg the name of the tables where the realtionship type is HAS MANY
        * 
        */
        public function belongsToJoinConditions() {

            $sql = "SELECT 
            `table_name` AS `table_name` , 
            `field_name` AS `foreign_key` , 
            `foreign_table` AS `join_table` 

            FROM `system` WHERE `table_name` = '{$this->getRawTableName()}' AND `field_type` = 'foreign' ";


            $result = $this->findSql($sql);
            $joins = array ();

            if(is_array($result)){

                foreach ( $result as $row ) {

                    $joins [] = array (
                        'from'  => $row['table_name'],
                        'table' => $row['join_table'],
                        'type' => 'inner', 
                        'conditions' => "{$row['join_table']}.id = {$row['table_name']}.{$row['foreign_key']}",
                    );  

                }

            }


            return $result === false ? array () : $joins;
        }

        /**
        * Returns an array containg the name of the tables where the realtionship type is Has and Belongs to many , along with the table governing this join relationship
        * 
        */
        public function habtmTables() {
            $sql = "SELECT `foreign_table` , `parameters` FROM `system` WHERE `table_name` = '{$this->getRawTableName()}' AND `field_type` = 'habtm_foreign'  ";
            $result = $this->findSql($sql);

            return $result;
        }

        /**
        * Creates a unique alias
        * 
        * @param mixed $tableName
        * @param mixed $aliasesUsed
        */
        public function createAlias($tableName , $aliasesUsed){
            if(!isset($aliasesUsed[$tableName])){
                return $tableName;
            }
            for ($i = 1 ; $i <= 10 ; $i++){
                $alias = $tableName.'_'.$i;

                if(!isset($aliasesUsed[$alias])){
                    return $alias;
                }
            }

            return $tableName.'_'.rand();
        }

        /**
        * Returns an array containing the name of the tables and how they should be traversed to achieve a tree hierarchy. ie : it fetches the BelongsTo tables
        * [0] -> last child , [1] -> before last child ... [n] => first parent
        * @param mixed $path
        */
        public function getTableTraversalPath( $path = array () , $direction = 'child_to_parent' ){

            $tables = $this->belongsToTables();
            if(!is_array($tables)){
                $tables = array ();   
            }

            $arrayToAppend = array();
            $arrayToAppend['table_name'] = $this->getRawTableName();

            $tableInfoToUse = NULL;

            foreach($tables as $tableInfo){
                if( isset($this->forcedTraversal) && in_array($tableInfo['join_table'],$this->forcedTraversal) && ($tableInfo['join_table'] != $tableInfo['table_name']) ){

                    $tableInfoToUse = $tableInfo;
                }else if(!isset($this->forcedTraversal)){
                    $tableInfoToUse = $tableInfo;
                }  
            }

            $tableInfo = $tableInfoToUse;

            if(empty($tableInfoToUse)){
                $path [] = $arrayToAppend; 
                if($direction == 'parent_to_child'){
                    $path = array_reverse($path);
                }

                return $path;   
            }




            $arrayToAppend['foreign_key'] =  $tableInfo['foreign_key'];
            $arrayToAppend['join_table'] =  $tableInfo['join_table'];

            foreach ($path as $info){
                if($info['table_name'] == $arrayToAppend['table_name']){
                    return $path;   
                }
            }

            $path [] = $arrayToAppend; 

            $t = new TableRelationship($tableInfo['join_table']);  
            $t->forcedTraversal = $this->forcedTraversal;


            return $t->getTableTraversalPath($path , $direction);

        }

        public function find ( $options = array () ) {

            $joinConditions = $this->belongsToJoinConditions();
            $aliasUsed = array ($this->getRawTableName());
            $joins = array (); 
            $belongsToTableNames = array (); 

            $fieldsToSelect = array ("`{$this->getRawTableName()}`.*");

            foreach ($joinConditions as $join){
                //If not Tree table
                if($join['table'] != $join['from']){
                    $alias = $this->createAlias($join['table'],$aliasUsed);
                    $aliasUsed[$alias] = true;
                    $conditions = str_replace($join['table'],$alias,$join['conditions']);

                    $table = new Table($join['table']);
                    $displayField = $table->getDisplayField();

                    if ( $this->contains === NULL || ( is_array ($this->contains) &&  in_array ($join['table'], $this->contains )  )){
                        $fieldsToSelect [] = "`{$alias}`.`id` AS `{$alias}__id`";
                        $fieldsToSelect [] = "`{$alias}`.`{$displayField}` AS `{$alias}__{$displayField}`"; 
                    }

                    $joins [] = array (
                        'table' => $join['table'],
                        'alias' => $alias,
                        'type' => $join['type'],
                        'conditions' => $conditions
                    );

                    $belongsToTableNames[] = $join['table'];

                }
            }

            $options['fields'] = $fieldsToSelect;
            $options['joins'] = $joins;

            $query = $this->buildQuery($options);

            $resultArray =  $this->findSql($query);

            if(is_array($resultArray)){
                foreach ($resultArray as $resultKey => $result){
                    foreach ( $result as $key => $value ) {
                        if(strpos($key,'__') !== false ) {
                            list ( $belongsToTableName , $fieldName ) = explode ('__',$key);
                            if(in_array ($belongsToTableName,$belongsToTableNames)){
                                $resultArray[$resultKey][$belongsToTableName][$fieldName]  = $value; 
                            }
                        }

                    }
                }
            }

            // if the user wants to includes the Has Many relationships
            $allHasManyTables = $this->hasManyTables();
            $hasManyTables = array_intersect( (array) $this->contains,  $allHasManyTables );
            $hasManyTablesByKeys =  array_intersect_key((array) $this->contains , array_flip($allHasManyTables));
            foreach ( $hasManyTablesByKeys as $tableName => $subContains ) {
                $hasManyTables [] = $tableName;
            }

            foreach ( $hasManyTables as $tableName){
                foreach ( $resultArray as $i => $value ) {

                    $table = new TableRelationship($tableName); 

                    if(isset($this->contains[$tableName])){
                        $table->contains = $this->contains[$tableName];
                    }else{
                        $table->contains = array ();
                    }

                    $result = $table->find(array ( 
                            'conditions' => array (
                                $this->tableName.'.`id`' => $value['id']
                            )
                        ));  

                    $resultArray[$i][$tableName] = $result;
                }

            }

            return $resultArray;
        }

        /**
        * Builds an SQL Query from array 
        * 
        * @param mixed $query
        */
        public function buildQuery ( $options ) {

            $sql = "SELECT ";
            if ( isset($options['fields']) ){
                $sql .= join ( "," , $options['fields']);
            }   

            $sql .= " FROM `{$this->getRawTableName()}` AS `{$this->getRawTableName()}` ";

            foreach ( $options['joins'] as $join){
                $joinType = $join['type'];
                $joinTable = $join['table'];
                $joinAlias = $join['alias'];
                $joinConditions = $join['conditions'];

                $sql .= " {$joinType} JOIN `{$joinTable}` AS `{$joinTable}` ON ({$joinConditions}) ";   
            }

            if(isset($options['conditions']) && is_array($options['conditions']) && !empty($options['conditions'])){
                $sqlArray = array();

                foreach($options['conditions'] as $column=>$value){
                    @list($columnName,$sign) = explode(' ',$column);
                    if(empty($sign)){
                        $sign = '=';
                    }
                    $value = $this->adodb->qstr($value,self::$magicQuotes);
                    $sqlArray [] = $columnName.' '.$sign.$value;
                }
                $sql .= " WHERE " . join(" AND ",$sqlArray);
            }



            //Checks if a table is sortable
            $posExists = $this->isTableSortable();

            if($posExists){
                $orderBy = " ORDER BY `{$this->getRawTableName()}`.`pos` ASC ";
            } else {
                $orderBy = " ORDER BY `{$this->getRawTableName()}`.`id` DESC ";
            }



            if(!empty($orderBy)){
                $sql .= ' '.$orderBy;
            }

            if(isset($options['limit'])){

                $options['limit'] = (int) $options['limit'];
                $sql .= " LIMIT {$options['limit']} ";
            } else if(isset($pagination)){
                $offset = $pagination->offset();
                $perPage = $pagination->per_page;
                $sql .= " LIMIT {$offset},{$perPage}";
            }

//            echo $sql . '<br>';
            return $sql;


        }


        /**
        * Returns an array containing all the belongs to associations. Ie : if you are fetching all products, it will return the category that every product belongs to and the master category that every category belongs to
        * 
        * $options['conditions']['id'] = 1 // Returns the record with id = 1 
        * $options['conditions']['{$associationTableName}__id'] = 1 // Returns only the records in the associated table with this id 
        * 
        * @param mixed $options
        */
        public function findContainingAllBelongsTo($options = array () ){


            return $this->find($options);

            $path = array();
            $belongsToTableNames = array (); 

            foreach($this->belongsToTables() as $tableInfo){

                $params = $this->getFieldParameters($tableInfo['foreign_key']);
                $t = new TableRelationship($tableInfo['join_table']);

                if(!empty($params) && isset($params['tables']) && !empty($params['tables'])  ){
                    $t->forcedTraversal = $params['tables'];
                }

                $path[$tableInfo['join_table']][] = $tableInfo;
                $path[$tableInfo['join_table']] = array_merge($path[$tableInfo['join_table']] ,  $t->getTableTraversalPath());


            }

            $sql = "";
            $joinsSql = "";
            $selectionFields = array ("`{$this->getRawTableName()}`.*");



            foreach($path as $tableName => $traversalPaths){
                foreach($traversalPaths as $traversalPath){
                    if(isset($traversalPath['join_table'])){

                        $belongsToTableNames [] = $traversalPath['join_table'];


                        $joinsSql .= " 
                        LEFT JOIN `{$traversalPath['join_table']}` AS `{$traversalPath['join_table']}`
                        ON ( `{$traversalPath['table_name']}`.`{$traversalPath['foreign_key']}`  = `{$traversalPath['join_table']}`.`id` ) "; 

                        /*

                        ##IF TREE TABLE
                        $treeTable = new Table($traversalPath['table_name']);
                        if( ($foreignFieldName = $treeTable->hasForeignKeyRelatedTo($traversalPath['table_name'],true)) !== false ){
                        $displayField = $treeTable->getDisplayField();
                        for($i = 1 ; $i <= 10 ; $i++){
                        $joinsSql .= " 
                        LEFT JOIN `{$traversalPath['table_name']}` AS `{$traversalPath['table_name']}_{$i}`";

                        if($i == 1){
                        $joinsSql .=  " ON ( `{$traversalPath['table_name']}`.`{$foreignFieldName}`  = `{$traversalPath['table_name']}_{$i}`.`id` ) ";
                        }else{
                        $joinsSql .=  " ON ( `{$traversalPath['table_name']}_".($i-1)."`.`{$foreignFieldName}`  = `{$traversalPath['table_name']}_{$i}`.`id` ) ";
                        }

                        $selectionFields [] = "`{$traversalPath['table_name']}_{$i}`.`id` AS `{$traversalPath['table_name']}_{$i}__id`";
                        $selectionFields [] = "`{$traversalPath['table_name']}_{$i}`.`{$displayField}` AS `{$traversalPath['table_name']}_{$i}__{$displayField}`";  
                        }

                        }
                        ## IF TREE TABLE
                        */
                        $table = new Table($traversalPath['join_table']);
                        $displayField = $table->getDisplayField();

                        $selectionFields [] = "`{$traversalPath['join_table']}`.`id` AS `{$traversalPath['join_table']}__id`";
                        $selectionFields [] = "`{$traversalPath['join_table']}`.`{$displayField}` AS `{$traversalPath['join_table']}__{$displayField}`";  

                    }
                }
            }


            $sql = "SELECT ";
            $sql .= join(',', $selectionFields);
            $sql .= " FROM `{$this->getRawTableName()}` ";
            $sql .= $joinsSql;


            if(isset($options['conditions']) && is_array($options['conditions']) && !empty($options['conditions'])){
                $sqlArray = array();

                foreach($options['conditions'] as $column=>$value){
                    @list($columnName,$sign) = explode(' ',$column);
                    if(empty($sign)){
                        $sign = '=';
                    }
                    $value = $this->adodb->qstr($value,self::$magicQuotes);
                    $sqlArray [] = $columnName.' '.$sign.$value;
                }
                $sql .= " WHERE " . join(" AND ",$sqlArray);
            }


            //Checks if a table is sortable
            $posExists = $this->isTableSortable();

            if($posExists){
                $orderBy = " ORDER BY `{$this->getRawTableName()}`.`pos` ASC ";
            } else {
                $orderBy = " ORDER BY `{$this->getRawTableName()}`.`id` DESC ";
            }



            if(!empty($orderBy)){
                $sql .= ' '.$orderBy;
            }

            if(isset($options['limit'])){
                $options['limit'] = (int) $options['limit'];
                $sql .= " LIMIT {$options['limit']} ";
            } else if(isset($pagination)){
                $offset = $pagination->offset();
                $perPage = $pagination->per_page;
                $sql .= " LIMIT {$offset},{$perPage}";
            }

            $resultArray =  $this->findSql($sql)  ;


            if(is_array($resultArray)){
                foreach ($resultArray as $resultKey => $result){
                    foreach ( $result as $key => $value ) {
                        if(strpos($key,'__') !== false ) {
                            list ( $belongsToTableName , $fieldName ) = explode ('__',$key);
                            if(in_array ($belongsToTableName,$belongsToTableNames)){
                                $resultArray[$resultKey][$belongsToTableName][$fieldName]  = $value; 
                            }
                        }

                    }
                }
            }

            return $resultArray;
        }

    }
?>
