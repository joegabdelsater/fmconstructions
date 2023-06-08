<?php
require_once('api-adodb/config.php');
require_once(LIB_PATH.DS.'table.class.php');

/**
 * Handles everything that should happen after the data has been validated and right before they are saved.
 */
class AfterSave extends Table {

    static $errors = array();
    static $postValues;
    protected $columnInfo;

    protected $id = null ; //Id of the current entry

    protected $currentColumn;

    private $displayAllErrorsAtOnce = true; // Set to true if you want to display all errors at once.

    function __construct($tableName,&$postValues,$id){
        parent::__construct($tableName);
        self::$postValues = $postValues;


        $this->id = $id;


        $this->getColumnTypes();

        $this->afterSave();

    }



    public function afterSave(){
        $postValues = self::$postValues;

        foreach ($this->columnInfo as $column){
            $this->currentColumn = $column;


            /**
             *Check whether the field types are valid or not. Functions are named exactly as the field types are named in the system table
             */

            if(method_exists($this,$column['field_type'])){

                //  if( isset($postValues[$column['field_name']]) && !empty($postValues[$column['field_name']]) ){
                if( isset($postValues[$column['field_name']]) ){
                    $this->{$column['field_type']}($postValues[$column['field_name']]);
                }

            }

        }
    }



    /**
     * Saves data into the HABTM table
     *
     * @param mixed $value
     */
    function habtm_foreign($value){

        $parameters = $this->getFieldParameters($this->currentColumn['field_name']);
        $habtmTable = new Table($parameters['habtm_table']);

        /**
         * Insert new data
         */
        if(!empty($this->id)){
            //HABTM unset the $_FILES while saving
            $files = $_FILES;
            unset($_FILES);
            $ids = explode("|",$value);
            foreach($ids as $id){
                $habtmTable->save(array (
                    $parameters['foreignKey'] => $id,
                    $parameters['currentField'] => $this->id,

                ));
            }
            //Set them again after the data is saved
            $_FILES = $files;
        }
    }

    /**
     * Creates a slug from a set field
     *
     * @param mixed $value
     */
    function slug ( $value ) {
        $parameters = $this->getFieldParameters($this->currentColumn['field_name']);
        //Create the slug from this field
        $fromField = $parameters['from_field'];
        $slug =  seoUrl ($_POST[$fromField]);

        if(  $this->findInTable(array ('slug ' => $slug , 'id !'=> $this->id) ) ){
            $slug =  $this->id.'-'. seoUrl ($_POST[$fromField]);
        }


        $toSave = array (
            'id' => $this->id,
            'slug' => $slug
        );

        $habtmTable = new Table( $this->getRawTableName());
        $habtmTable->update ( $toSave );
    }


}