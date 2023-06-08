<?php
    require_once('api-adodb/config.php');
    require_once(LIB_PATH.DS.'table.class.php');

    /**
    *Helper class for validation.
    * 1.0 - George
    */
    class Validation extends Table {

        static $errors = array();
        static $postValues;
        protected $columnInfo;

        protected $currentColumn;

        private $displayAllErrorsAtOnce = true; // Set to true if you want to display all errors at once.

        function __construct($tableName,&$postValues){
            parent::__construct($tableName);
            self::$postValues = $postValues;
            $this->getColumnTypes();

            $this->validate();
            $this->throwExceptions();
        }

        /**
        * Converts the errors array and throws an exception with all the errors accumulated. This allows to catch all validation errors at once and not once by one.
        *
        */
        protected function throwExceptions(){
            if(!empty(self::$errors)){
                throw new Exception(join("<br />",self::$errors));
            }
        }

        protected function validate(){
            $postValues = self::$postValues;

            foreach ($this->columnInfo as $column){
                $this->currentColumn = $column;
                /**
                *Check for required fields first.
                */
                if($column['mandatory'] && ( (!isset($postValues[$column['field_name']])  && empty($_FILES[$column['field_name']]) ) || (empty($postValues[$column['field_name']]) && empty($_FILES[$column['field_name']])  )   ) ){

                    $this->addError($column['field_name'], printTableName($column['field_name']) ." is a mandatory field!");
                }

                /**
                *Check whether the field types are valid or not. Functions are named exactly as the field types are named in the system table
                */

                if(method_exists($this,$column['field_type'])){
                    try {
                        if(isset($postValues[$column['field_name']]) && !empty($postValues[$column['field_name']])){


                            $this->{$column['field_type']}($postValues[$column['field_name']]);
                        }
                    } catch (Exception $e){
                        if($this->displayAllErrorsAtOnce){
                            //Add the caught error to the errors array
                            $this->addError($column['field_name'],$e->getMessage());
                        } else {
                            //Throw an exception with only one error
                            throw new Exception($e->getMessage());
                        }
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


        function addError($fieldName,$message){
            self::$errors [$fieldName] = $message;
        }

        /**
        * Function that checks if this is a valid percentage
        *
        * @param mixed $value
        */
        function percentage($value){
            if($value < 0 || $value > 100){
                throw new Exception("This is not a valid percentage. Value must be between 0 and 100");
            }
        }


        /**
        * Validates an email field
        *
        * @param mixed $value
        */
        function email($value){

            if(!filter_var($value, FILTER_VALIDATE_EMAIL))
            {
                throw new Exception("This is not a valid email!");
            }
        }
        /**
        * Validates a URL
        *
        * @param mixed $value
        */
        function url($value){
            if(!filter_var($value, FILTER_VALIDATE_URL))
            {
                throw new Exception("This is not a valid URL!");
            }
        }

        /**
        * Validates a checkbox
        *
        * @param mixed $value
        */
        function checkbox($value){
            if(!filter_var($value, FILTER_VALIDATE_BOOLEAN))
            {
                throw new Exception("Invalid checkbox value!");
            }
        }

        /**
        * Function that checks if the date is valid
        *
        * @param mixed $value
        */

        function date($value){
            list($year,$month,$day) = explode('-',$value);
            if($year == '0000' && $month == '00' && $day == '00'){
                return true;
            }
            if(!checkdate($month, $day, $year)){
                throw new Exception("Invalid Date!");
            }
        }

        /**
        * Function that validates time field
        *
        * @param mixed $value
        */
        function time($value){
            $pattern = "#^([0-1][0-9]|[2][0-3]):[0-5][0-9]:[0-5][0-9]#";
            if(!preg_match($pattern,$value)){
                throw new Exception("This is an invalid time!");
            }
        }

        /**
        * Function that checks if a text area contains HTML tags or not
        *
        * @param mixed $value
        */
        function textarea_nostyles($value){
            $noTagsValue = strip_tags($value);
            if($value != $noTagsValue){
                throw new Exception("You are not allowed to insert HTML tags in this text area.");
            }
        }


        /**
        * Function to validate if the foreign key is valid (exists in the database or not, if foreign key is a key to itself)
        *
        * @param mixed $value
        */
        function foreign($value){

            $parameters = $this->getFieldParameters($this->currentColumn['field_name']);
            
            $tableName = $this->getForeignTable($this->currentColumn['field_name']);
            $currentTableName = $this->getRawTableName();
            $table = new Table($tableName);

            if(isset($parameters['multi'])){
              
                foreach(self::$postValues[$this->currentColumn['field_name']] as $id){
                    //Check if the record exists in the parent table
                    if($table->findItemById($id) === false){
                        throw new Exception("ID {$id} not found in table {$tableName}.");
                    }
                }
            }else{

                if( ($tableName == $currentTableName) && isset(self::$postValues['id']) ){
                    if($value == self::$postValues['id']){
                        throw new Exception("The parent cannot be the child at the same time.");
                    }
                }

                if($table->findItemById($value) === false){
                    throw new Exception("ID {$value} not found in table {$tableName}.");
                }
            }
        }


        function habtm_foreign($value){
            return $this->foreign($value);   
        }

        /**
        * Validates a textarea
        *
        * @param mixed $value
        */
        function textarea($value){

            $message = "You are not allowed to use script tags in your text area.";
            $noTagsValue = preg_replace("@<script[^>]*>.+</script[^>]*>@i", "", $value);
            if($value != $noTagsValue){
                throw new Exception($message); //We should opt for HTML Putifier if we are to totally protect the cms gen against XSS
            }else if(strpos($value,"<script")){
                throw new Exception($message);
            }
        }

        function limited_textarea($value){
            $fieldName = $this->currentColumn['field_name'];
            $params = $this->getFieldParameters($fieldName);

            $length = strlen($value);
            $maxLength = $params['maxlength'];

            if($length > $maxLength){
                throw new Exception('Maximum length cannot exceed '.$maxLength.' characters.'); 
            }
        }

        function paramValidate_thumbnail_preset_ratio($value){
            $ratio = self::$postValues['w'] / self::$postValues['h'];
            $ratio = round($ratio,2);
            if(($ratio) != $value){
                throw new Exception("The crop ratio must be $value . Your ratio is $ratio.");
            }
            return true;
        }

        /**
        * Validate the parameters pertaining to each field . ie: max_image_width , max_image_height etc...
        * 
        * @param mixed $fieldName
        */
        function validateFieldParams($fieldName){
            $params = $this->getFieldParameters($fieldName);

            foreach($params as $paramName => $value){
                $methodName = "paramValidate_".$paramName;
                if(method_exists($this,$methodName)){
                    $this->$methodName($value);
                }
            }
            return true;
        }




    }
?>
