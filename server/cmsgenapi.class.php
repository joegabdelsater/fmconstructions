<?php

    class CMSGENAPI extends Table {

        protected $contentEncoding = 'json'; //Default content encoding
        protected static $supportedContentEncodings = array('json','php'); //Supported content encoding

        public $result = false; //Result of the find

        /**
        * Create a new CMSGENApi object 
        * 
        * @param string $tableName = null
        * @param string $action = readAll
        * @param string $contentEncoding = json
        * @return CMSGENAPI
        */

        function __construct($tableName , $action = 'readAll' , $contentEncoding = 'json'){
            parent::__construct($tableName);

            if(empty($action)){
                $action = 'readAll';
            }

            //Allow no table only when using the action outputError
            if(!$this->tableExists() && $action != 'outputError'){
                throw new Exception("Table not found!");   
            }
            if(!method_exists($this,$action)){
                throw new Exception("Action ".h($action)." not found!");   
            }

            if(!in_array($contentEncoding,self::$supportedContentEncodings)){
                throw new Exception('Unsupported content encoding!');   
            }

            $this->action  = $action;
            $this->contentEncoding  = $contentEncoding;

        }

        /**
        * Function to output the error / exception produced in the specified encoding type
        * 
        * @param Exception $e
        * @param string $contentEncoding
        * @return self
        */

        static function outputError(Exception $e,$contentEncoding){

            $object = new self('','outputError',$contentEncoding);
            $object->result['status'] = 'fail';
            $object->result['error'] = $e->getMessage();

            return $object;
        }

        /**
        * Execute the selected action
        * 
        */
        function execute(){
            $this->{$this->action}(); 

            return $this;
        }

        /**
        * Outputs the result in the specified content encoding
        * 
        */
        function __toString(){
            $contentType = $this->getContentTypeHeader();
            header("content-type: {$contentType}; charset=utf-8");   

            return $this->output();
        }

        /**
        * Formats the result in the content encoding type specified
        * 
        */
        function output(){
            switch($this->contentEncoding){
                case 'json' : 
                    return json_encode($this->result);
                    break;   

                case 'php':
                
                    return print_r($this->result,true);
                    break;

                default : 
                    return json_encode($this->result);
                    break;
            }   
        }

        /**
        * Returns the content encoding type header that will be used in the PHP  function header()
        * 
        */
        function getContentTypeHeader(){
            switch($this->contentEncoding){
                case 'json' : 
                    return 'application/json';
                    break;      

                case 'php' : 
                    return 'text/html';
                    break; 
                    
                default : 
                    return 'application/json';
                    break;
            }    
        }

        /**
        * Accepts conditions that will be used as parameters in the called actions
        * 
        * @param mixed $options
        */
        function addConditions($options = array ()){
            $this->options = $options;
        }

        /**
        * Reads all the records from the selected table
        * 
        */

        function readAll(){

            //Limit 
            if(isset($this->options['limit'])){
                $limit = intval($this->options['limit']);
            }else{
                $limit = 20;
            }

            //Order by Column
            if(isset($this->options['orderBy'])){
                $orderBy = (string)$this->options['orderBy'];
            }else{
                if($this->isTableSortable()){
                    $orderBy = 'pos';
                }else{
                    $orderBy = 'id';
                }
            }

            //Order direction
            if(isset($this->options['orderDirection'])){
                $orderDirection = in_array($this->options['orderDirection'],array ('asc','desc','ASC','DESC')) ? $this->options['orderDirection'] : 'DESC';
            }else{
                $orderDirection = 'DESC';
            }

            $result = $this->safeFind(array (
                    'order' => $orderBy.' '.$orderDirection,
                    'limit' => $limit
                ));

            $this->result = $result;
        }

        /**
        * Reads an item from the table by id
        * 
        */
        function readById(){
            if(isset($this->options['id'])){
                $id = (int)$this->options['id'];    
            }

            $this->result = $this->findItemById($id);
        }
}

?>