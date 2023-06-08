<?php
    require_once('api-adodb/config.php');
    require_once(LIB_PATH.DS.'database.class.php');
    require_once(LIB_PATH.DS.'log.class.php');
    require_once(LIB_PATH.DS.'session.class.php');

    class MailingList {

        protected static $table_name="cmsgen_mailing_list";
        protected static $db_fields = array('id', 'email', 'group_id','verification' ,'verified' );

        public $id;
        public $email;
        public $group_id;
        public $verification;
        public $verified;

        public static $errors = array();

        public static function emailExists($value){
            $database = MySQLDatabase::getInstance();
            $value = $database->escape_value($value);
            $sql = "SELECT id FROM ".self::$table_name." WHERE email='$value' LIMIT 1";
            $res = $database->query($sql);
            if($database->num_rows($res) == 1){
                return true;
            }else{
                return false;
            }
        }


        public static function newUser(){
            $email = $_POST['email'];
            if(!isemail($email) ){
                self::$errors['email'] = 'Please input a valid email.';
                return false;
            }

            if(self::emailExists($email) ){
                self::$errors['email'] = 'This email already exists.';
                return false;
            }

            $object = new self;
            $object->email = $email;
            $object->verification = uniqid().md5(uniqid().$email).uniqid();
            if($object->save()){
                $object->emailVerification();
                return true;
            }else{
                return false;
            }
        }


        public function verify($verification){
            if($verification == $this->verification){
                $this->verified = 1;
                $this->save();
                return true;
            }else{
                return false;
            }
        }

        public function unsubscribe($verification){
            if($verification == $this->verification){
                $this->verified = 0;
                $this->save();
                return $this->delete();
            }else{
                return false;
            }
        }

        public function emailMessage($message,$subject=""){
            static $count = 0;
            $count++;
            $email_to = $this->email;
            //Email from is the default site's contact email
            $site = new Site();
            $email_from = $site->contact_email;
            $headers = 'From: '.'No-Reply'.' <'.$email_from.">\r\n".
            'Reply-To: '.$email_from."\r\n" .
            'X-Mailer: PHP/' . phpversion()."\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            //
            //            echo $email_to . '<br />';
            //            echo $message. '<br />';
            //            return;
            //echo $count . '<br />';
            //Remove the slashes
            $message = stripslashes($message);

            @mail($email_to,$subject,$message,$headers);
        }

        public function listMessageHeader(){
            $table = new Table('admin_advanced_settings');
            $row = $table->findItemById(1);
            return $row['mailinglist_header'];
        }

        public function listMessageFooter(){
            $table = new Table('admin_advanced_settings');
            $row = $table->findItemById(1);
            $footer  = $row['mailinglist_footer'];

            $footer .= $this->unsubscribeMessage();

            return $footer;
        }

        public function formMessage($postMessage){
            $message = $this->listMessageHeader();
            $message .= $postMessage;
            $message .= $this->listMessageFooter();
            return $message;
        }
        public static function emailList(){
            $postValues = $_POST;

            if(!isset($_POST['group_id'],$_POST['mailingListMessage']) || empty($_POST['mailingListMessage'])){
                self::$errors [] = 'Group and Message are mandatory.';
                return false;
            }

            $groupId = (int)$_POST['group_id'];

            $subject = $_POST['mailingListSubject'];
            $message = $_POST['mailingListMessage'];

            $subscribers = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE group_id = {$groupId} AND verified ='1' ");

            if(empty($subscribers)){
                self::$errors [] = 'Group is empty.';
                return false;
            }
            $emailsSent = 0;

            $dbMessage = new MailingListMessage($subject,$message);
            $dbMessage->save();

            foreach($subscribers as $subscriber){
                if(MailingListQueue::allowSendEmail()){
                    MailingListQueue::enqueue($subscriber,$dbMessage->id,1);
                    $message = $subscriber->formMessage($_POST['mailingListMessage']);
                    $subscriber->emailMessage($message,$subject);
                    self::sleep();
                    //Increment the number of sent emails
                    $emailsSent++;
                }else{
                    MailingListQueue::enqueue($subscriber,$dbMessage->id,0);
                }
            }

            return true;
        }

        public static function emailQueue(){
            //If the user is still allowed to send emails:
            error_reporting(E_ALL);
            $queuedMails = MailingListQueue::findByLimit();
            foreach($queuedMails as $queuedMail){
                if(MailingListQueue::allowSendEmail()){
                    $subscriber = self::find_by_id($queuedMail->subscriber_id);
                    $messageId = $queuedMail->message_id;

                    $messageObject = MailingListMessage::find_by_id($messageId);

                    $message = $subscriber->formMessage($messageObject->getMessage());
                    $subject = $messageObject->subject;
                    $subscriber->emailMessage($message,$subject);
                    $queuedMail->sent = 1;
                    $queuedMail->save();

                }
            }
        }

        public static function sleep(){
            // sleep(1);
        }
        public function unsubscribeMessage(){
            $message = "To unsubscribe, please click on the following link: ".PUBLIC_HTML_SITE.DS."mailing-list-unsubscribe.php?id={$this->id}&verification={$this->verification} ";
            return $message;
        }

        public function emailVerification(){
            $subject = 'Please verify your email.';
            $message = "Hello {$this->email}, <br /><br /> Please verify your email by clicking on the following link: ".PUBLIC_HTML_SITE.DS."mailing-list-verify.php?id={$this->id}&verification={$this->verification} <br /><br /> Best Regards!";

            $this->emailMessage($message,$subject);
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