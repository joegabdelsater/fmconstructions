<?php
    require_once('api-adodb/config.php');
    require_once(LIB_PATH.DS.'database.class.php');


    ##Manage all logins##
    class Statistics extends db {

        protected $id;
        public $ip;
        protected $browser;
        protected $thedate_visited;
        public $page;
        public $from_page;

        protected static $table_name="cmsgen_statistics";
        protected static $db_fields = array('id', 'ip', 'browser', 'thedate_visited','page','from_page' );


        function __construct($saveStats=true){
            db::__construct();

            $this->browser  =$_SERVER['HTTP_USER_AGENT']; // get the browser name

            $this->page = $_SERVER['PHP_SELF'];// get page name

            $this->ip  =  $_SERVER['REMOTE_ADDR'];   // get the IP address

            $this->from_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';//  page from which visitor

            $this->thedate_visited = date("Y-m-d");

            //Insert the data in the table...
            if($saveStats){
                $this->save();
            }


        }
        public function uniqueVisitors($date=""){
            if(empty($date)){
                $date = date("Y-m-d");
            }
            $query = "SELECT COUNT(*) FROM ".self::$table_name." WHERE thedate_visited='{$date}' GROUP by ip";
            $res = $this->adodb->Execute($query);
            $number_of_views = $res->RecordCount();
            return $number_of_views;
        }

        public function pageViews($date=""){
            if(empty($date)){
                $date = date("Y-m-d");
            }

            //Finds the pageviews except the ones in the admin area!
            $query = "SELECT COUNT(*) FROM ".self::$table_name." WHERE thedate_visited='{$date}' AND page NOT LIKE '/admin/%' ";
            $res = $this->adodb->Execute($query);
            $count = $res->RecordCount();
            if($count > 0){
                $row = $res->FetchRow();
                return $row['COUNT(*)'];
            }else{
                return 0;
            }

        }


        // Common Database Methods
        public static function find_all() {
            global $user;
            $sql = "SELECT * FROM ".self::$table_name;
            return self::find_by_sql($sql);
        }


        public static function find_by_sql($sql="") {
            $database = MySQLDatabase::getInstance();
            $result_set = $this->adodb->Execute($sql);
            $object_array = array();
            while ($row = $result_set->FetchRow($result_set)) {
                $object_array[] = self::instantiate($row);
            }
            return $object_array;
        }

        public static function count_all() {

            $sql = "SELECT COUNT(*) FROM ".self::$table_name;
            $result_set = $this->adodb->Execute($sql);
            $row = $result_set->FetchRow($result_set);
            return array_shift($row);
        }

        private static function instantiate($record) {
            // Could check that $record exists and is an array
            $object = new self;
            foreach($record as $attribute=>$value){
                if($object->has_attribute($attribute)) {
                    $object->$attribute = $value;
                }
            }
            return $object;
        }

        private function has_attribute($attribute) {
            return array_key_exists($attribute, $this->attributes());
        }

        protected function attributes() {
            // return an array of attribute names and their values
            $attributes = array();
            foreach(self::$db_fields as $field) {
                if(property_exists($this, $field)) {
                    $attributes[$field] = $this->$field;
                }
            }
            return $attributes;
        }

        protected function sanitized_attributes() {
            $database = MySQLDatabase::getInstance();
            if(!isset($database)){
                $database = new MySQLDatabase();
            }
            $clean_attributes = array();
            // sanitize the values before submitting
            foreach($this->attributes() as $key => $value){
                $clean_attributes[$key] = $database->escape_value($value);
            }
            return $clean_attributes;
        }

        public function save() {
            // A new record won't have an id yet.
            return isset($this->id) ? $this->update() : $this->create();
        }

        public function create() {

            $attributes = $this->sanitized_attributes();
            $sql = "INSERT INTO ".self::$table_name." (";
            $sql .= join(", ", array_keys($attributes));
            $sql .= ") VALUES ('";
            $sql .= join("', '", array_values($attributes));
            $sql .= "')";

            if($this->adodb->Execute($sql)) {
                $this->id = $this->adodb->Insert_ID();
                return true;
            } else {
                return false;
            }
        }

        public function update() {
            $database = MySQLDatabase::getInstance();
            $attributes = $this->sanitized_attributes();
            $attribute_pairs = array();
            foreach($attributes as $key => $value) {
                $attribute_pairs[] = "{$key}='{$value}'";
            }
            $sql = "UPDATE ".self::$table_name." SET ";
            $sql .= join(", ", $attribute_pairs);
            $sql .= " WHERE id=". $database->escape_value($this->id);
            $res = $this->adodb->Execute($sql);
            return ($res->RecordCount() == 1) ? true : false;
        }

        public function delete() {
            $database = MySQLDatabase::getInstance();
            $sql = "DELETE FROM ".self::$table_name;
            $sql .= " WHERE id=". $database->escape_value($this->id);
            $sql .= " LIMIT 1";
            $res = $this->adodb->Execute($sql);
            return ($res->RecordCount() == 1) ? true : false;
        }


    }

?>