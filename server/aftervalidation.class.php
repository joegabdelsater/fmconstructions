<?php
require_once('api-adodb/config.php');
require_once(LIB_PATH.DS.'table.class.php');

/**
 * Handles everything that should happen after the data has been validated and right before they are saved.
 */
class AfterValidation extends Table {

    static $errors = array();
    static $postValues;
    protected $columnInfo;

    protected $id = null ; //Id of the current entry

    protected $currentColumn;

    private $displayAllErrorsAtOnce = true; // Set to true if you want to display all errors at once.

    function __construct($tableName,&$postValues){
        parent::__construct($tableName);
        self::$postValues = $postValues;

        if(isset($postValues['id'])){
            $this->id = intval($postValues['id']);
        }

        $this->getColumnTypes();

        $this->afterValidate();

    }



    public function afterValidate(){
        $postValues = self::$postValues;

        foreach ($this->columnInfo as $column){
            $this->currentColumn = $column;

            /**
             *Check whether the field types are valid or not. Functions are named exactly as the field types are named in the system table
             */


            if(method_exists($this,$column['field_type'])){
                if(isset($postValues[$column['field_name']]) && !empty($postValues[$column['field_name']])){
                    $this->{$column['field_type']}($postValues[$column['field_name']]);
                }
            }

            /**
             *Unset the in active fields. Do not let the user update them.
             */
            if(!$column['active']){
                unset($postValues[$column['field_name']]);
                throw new Exception("Invalid request. User trying to edit inactive field: {$column['field_name']}");
            }
        }
    }




    /**
     * Function to validate if the foreign key is valid (exists in the database or not, if foreign key is a key to itself)
     *
     * @param mixed $value
     */
    function foreign($value){
        $parameters = $this->getFieldParameters($this->currentColumn['field_name']);
        if(isset($parameters['multi'])){
            self::$postValues[$this->currentColumn['field_name']] = join('|',self::$postValues[$this->currentColumn['field_name']]);
        }
    }



    /**
     * Deletes previous HABTM relationship details to get updated with new ones
     *
     * @param mixed $value
     */
    function habtm_foreign($value){


        $parameters = $this->getFieldParameters($this->currentColumn['field_name']);
        $habtmTable = new Table($parameters['habtm_table']);

//        if((strpos(self::$postValues[$this->currentColumn['field_name']], ',') !== false)){
//            $IDs = explode(",",self::$postValues[$this->currentColumn['field_name']]);
//            $_postValues = array();
//
//            $x = 0;
//            foreach ($IDs as $ID) {
//                $_postValues[] = self::$postValues;
//                $_postValues[$x][$this->currentColumn['field_name']] = $ID;
//                $x++;
//            }
//            self::$postValues = $_postValues;
//        }

        if((strpos(self::$postValues[$this->currentColumn['field_name']], ',') !== false)){
            self::$postValues[$this->currentColumn['field_name']] = str_replace(",","|",self::$postValues[$this->currentColumn['field_name']]);
        }else{
            self::$postValues[$this->currentColumn['field_name']] = join('|',self::$postValues[$this->currentColumn['field_name']]);
        }

//        self::$postValues[$this->currentColumn['field_name']] = join('|',self::$postValues[$this->currentColumn['field_name']]);



        /**
         * Delete previous data
         */
        if(!empty($this->id)){

            $rows = $habtmTable->safeFind( array ('conditions' => array (
                $parameters['currentField'] => $this->id
            ) ));

            if(!empty($rows)){
                foreach($rows as $row){
                    $habtmTable->delete($row['id']);
                }
            }
        }
    }


}