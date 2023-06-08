<?php
    require_once('api-adodb/config.php');
    require_once(LIB_PATH.DS.'database.class.php');
    require_once(LIB_PATH.DS.'log.class.php');
    require_once(LIB_PATH.DS.'session.class.php');

   

    class MailingListQueue {

        protected static $table_name="cmsgen_mailing_list_queue";
        protected static $db_fields = array('id', 'date', 'subscriber_id','message_id','sent' );

        public $id;
        public $date;
        public $subscriber_id;
        public $message_id;
        public $sent;

        public static $errors = array();

        public static function enqueue($subscriber,$message_id,$sent){
            $object = new self;
            $object->subscriber_id = $subscriber->id;
            $object->message_id = $message_id;
            $object->sent = $sent;

            $date = date('Y-m-d');
            while(self::queuedOnDate($date) >= EMAIL_LIMIT){
                $date = date('Y-m-d',strtotime('+1 day',strtotime($date)));
            }

            $object->date = $date;
            $object->save();
        }

        public static function queuedOnDate($date=""){
            $date =  empty($date) ? date('Y-m-d') : $date;
            $sql = "SELECT COUNT(*) FROM ".self::$table_name." WHERE date = '{$date}' LIMIT 1";

            $database = MySQLDatabase::getInstance();
            $res = $database->query($sql);
            $row = $database->fetch_array($res);

            return $row['COUNT(*)'];
        }


        public static function allowSendEmail(){
            $date = date('Y-m-d');
            $sql = "SELECT COUNT(*) FROM ".self::$table_name." WHERE date = '{$date}' AND sent = 1";
            $database = MySQLDatabase::getInstance();
            $res = $database->query($sql);
            $row = $database->fetch_array($res);

            $count = $row['COUNT(*)'];
            if($count < EMAIL_LIMIT){
                return true;
            }else{
                return false;
            }
        }

        public static function findByLimit(){
            $sql = "SELECT * FROM ".self::$table_name." ORDER BY date ASC LIMIT ".EMAIL_LIMIT;
            return self::find_by_sql($sql);
        }

        public function getMessage(){
            // $message = $this->listMessageHeader();
            $message = $this->message;
            //  $message .= $this->listMessageFooter();
            return $message;
        }


        // Common Database Methods
        public static function find_all() {
            global $user;
            $sql = "SELECT * FROM ".self::$table_name;

            //Do not show users that are more important than the logged in user
            if(isset($user) && !empty($user)){
                $userLevel = (int)$user->user_level;
                $sql .= " WHERE user_level <= $userLevel";
            }
            return self::find_by_sql($sql);
        }

        public static function find_by_id($id=0) {
            $id = intval($id);
            $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
            return !empty($result_array) ? array_shift($result_array) : false;
        }

        public static function find_by_sql($sql="") {
            $database = MySQLDatabase::getInstance();
            $result_set = $database->query($sql);
            $object_array = array();
            while ($row = $database->fetch_array($result_set)) {
                $object_array[] = self::instantiate($row);
            }
            return $object_array;
        }

        public static function count_all() {
            $database = MySQLDatabase::getInstance();
            $sql = "SELECT COUNT(*) FROM ".self::$table_name;
            $result_set = $database->query($sql);
            $row = $database->fetch_array($result_set);
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
            $database = MySQLDatabase::getInstance();
            $attributes = $this->sanitized_attributes();
            $sql = "INSERT INTO ".self::$table_name." (";
            $sql .= join(", ", array_keys($attributes));
            $sql .= ") VALUES ('";
            $sql .= join("', '", array_values($attributes));
            $sql .= "')";
            if($database->query($sql)) {
                $this->id = $database->insert_id();
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
            $database->query($sql);
            return ($database->affected_rows() == 1) ? true : false;
        }

        public function delete() {
            $database = MySQLDatabase::getInstance();
            $sql = "DELETE FROM ".self::$table_name;
            $sql .= " WHERE id=". $database->escape_value($this->id);
            $sql .= " LIMIT 1";
            $database->query($sql);
            return ($database->affected_rows() == 1) ? true : false;
        }

    }

?>