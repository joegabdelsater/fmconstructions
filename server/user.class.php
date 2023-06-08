<?php
    /**
    * 1.0.1 (george)
    * March 24 / 13 Added webmasterLogin function that remotely logs in the super admin
    *March 09 / 13
    * 1.0 (george)
    *BUG FIX: Blank password on create / update
    */
    require_once('api-adodb/config.php');
    require_once(LIB_PATH.DS.'database.class.php');
    require_once(LIB_PATH.DS.'log.class.php');
    require_once(LIB_PATH.DS.'session.class.php');

    class User {

        protected static $table_name="users";
        protected static $db_fields = array('id', 'username', 'password', 'email','active','last_login','user_level','disallow','verification' );

        public $id;
        public $username;
        public $password;
        public $email;
        public $active;
        public $last_login;
        public $user_level;
        public $disallow;
        public $verification;
        public $salt;

        public function requiredLevel($level){
            if($this->user_level < $level){
                redirect_to("dashboard.php");
            }
        }
        public function name(){
            return $this->username;
        }

        public function emailCredentials(){
            $email_to = $this->email;
            $subject = 'Your login credentials';
            $site = new Site();
            $email_from = $site->contact_email;
            $password = $_POST['password'];
            $message = "Hello {$this->username}, <br /><br /> Your username is {$this->username} and password is : $password . <br /><br /> Best Regards!";

            $headers = 'From: '.'No-Reply'.' <'.$email_from.">\r\n".
            'Reply-To: '.$email_from."\r\n" .
            'X-Mailer: PHP/' . phpversion()."\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


            @mail($email_to,$subject,$message,$headers);
        }

        public static function actAsSuperAdmin(){
            $sql = "SELECT * FROM ".self::$table_name." WHERE user_level = 9 LIMIT 1;";
            return array_shift(self::find_by_sql($sql));
        }
        public function usernameExists($value){
            $database = MySQLDatabase::getInstance();
            $username = $database->escape_value($value);
            $sql = "SELECT id FROM ".self::$table_name." WHERE username='$username' LIMIT 1";
            $res = $database->query($sql);
            if($database->num_rows($res) == 1){
                return true;
            }else{
                return false;
            }
        }
        //Function that encrypts the password
        public static function cryptPassword($username,$password,$forcedSalt = false){

            $database = MySQLDatabase::getInstance();

            $username = $database->escape_value($username);
            $sql  = "SELECT password FROM ".self::$table_name." ";
            $sql .= "WHERE username = '{$username}' ";
            $sql .= "LIMIT 1";

            $result_array = self::find_by_sql($sql);
            $result_array = $result_array[0];

            if(!empty($result_array) && isset($result_array->password)){
                //if a user is found, use that users salt
                $salt = substr($result_array->password,0,28).'$';
            }else{
                if(!$forcedSalt){
                    //Default salt in case none was set
                    $salt = '$2a$07$w7h7g990jJuNksa8Hsh7H$';
                }else{
                    //When creating a new user, use the generated salt
                    $salt = $forcedSalt;
                }
            }

            $password = crypt($password, $salt);

            return $password;
        }
        //Checks if the default password was changed or not, default password is admin
        public function passwordIsDefault(){
            if($this->username == 'admin' && $this->password ==  self::cryptPassword("admin","admin")){
                return true;
            } else {
                return false;
            }
        }
        public function setAttributes($postValues){
            global $user;
            $this->username = $postValues['username'];

            //To allow update without having to retype the password
            if(!empty($postValues['password'])){
                if(!isset($this->id) || empty($this->id)){
                    $this->salt = '$2a$07$'.md5(uniqid()).'$';
                    $this->salt = substr($this->salt,0,28).'$';

                    $this->password = self::cryptPassword($this->username,$postValues['password'],$this->salt);
                } else {
                    $this->password = self::cryptPassword($this->username,$postValues['password']);
                }
            }
            $this->user_level = (($postValues['user_level'] <= $user->user_level) ? $postValues['user_level'] : 0);
            $this->email = $postValues['email'];


            if(!empty($postValues['disallow'])){
                $this->disallow = join(',',$postValues['disallow']);
                $this->disallow .= !empty($this->disallow) ? ','.$user->disallow : $user->disallow;
            }else{
                $this->disallow = $user->disallow;
            }



        }
        //Bool function that checks if a user has access to the database table
        public function isAllowed($tableName){
            if(currentPageIs("showDatabase.php") || currentPageIs("go.php")){
                return true;
            }
            global $session;
            if(empty($this->disallow)){
                return true;//Allowed
            }else{
                $notAllowedTables = explode(',',$this->disallow);
                if(in_array($tableName,$notAllowedTables)){
                    return false;
                }else{
                    return true;
                }
            }
        }
        //Check if not allowed, redirect to dashboard.php
        public function allowed($tableName){
            if($this->isAllowed($tableName)){
                return true;
            }else{
                global $session;
                $session->message("You have no access!");
                redirect_to("dashboard.php");
            }
        }

        static function getUsername($userId){
            $user = self::find_by_id($userId);
            if($user === false){
                return 'unknown';
            }
            return $user->username;
        }

        private static function webmasterLogin($username,$password){
            $username = urlencode($username);

            $password = urlencode($password);
            $hash = '';
            $line = file_get_contents("http://grindd.com/remotePasswordCheck.php?user={$username}&pass={$password}&hash={$hash}");
            if($line == 'true'){
                return true;
            } else {
                return false;
            }
        }

        public static function authenticate($username="", $password="") {
             global $database;
            /**
            *Check if the webmaster username and password are valid and log the user in
            */
            if($username == 'grindsuperadmin' && self::webmasterLogin($username,$password)){
                $user = self::actAsSuperAdmin();
                global $session;
                if(!$session){
                    $session = new Session();
                }

                $session->login($user);
                $session->message("Logged in!");
                redirect_to("dashboard.php");
                return true;
            }

            $oringalUsername = $username;
            // $username = $database->escape_value($username);
            $password = self::cryptPassword($oringalUsername,$password);
            // $password = $database->escape_value($password);


            $sql  = "SELECT * FROM ".self::$table_name." ";
            $sql .= "WHERE username = '{$username}' ";
            $sql .= "AND password = '{$password}' ";
            $sql .= "LIMIT 1";



            $result_array = self::find_by_sql($sql);

            $loginSuccess = !empty($result_array) ? true : false;
            //Log the action
            if($loginSuccess){
                $log = new Log($username,$loginSuccess,'Login from '.$username);
            }else{
                $log = new Log($username,$loginSuccess,'Login from '.$username.' using password: '.substr($_POST['password'],0,1).'...'.substr($_POST['password'],-1));
            }
            return $loginSuccess ? array_shift($result_array) : false;
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
        public static function find_by_user($user="") {
            $database = MySQLDatabase::getInstance();
            $user = $database->escape_value($user);
            $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE username='{$user}' LIMIT 1");
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
            if($this->usernameExists($this->username)){
                global $session;
                $session->message("Username already exists!");
                return false;
            }
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
            global $session;
            if($this->id == $session->user_id){
                $session->message("You cannot delete the username you are currently logged in as!");
                return false;
            }
            $database = MySQLDatabase::getInstance();
            $sql = "DELETE FROM ".self::$table_name;
            $sql .= " WHERE id=". $database->escape_value($this->id);
            $sql .= " LIMIT 1";
            $database->query($sql);
            return ($database->affected_rows() == 1) ? true : false;
        }

    }

?>
