<?php
    require_once('api-adodb/config.php');

    class MySQLDatabase  extends db {

        public $connection;
        public $last_query;
        private $magic_quotes_active;
        private $real_escape_string_exists;

         static $instance = NULL;

        function __construct() {

            db::__construct();

           // $this->open_connection();
            $this->magic_quotes_active = get_magic_quotes_gpc();
            $this->real_escape_string_exists = function_exists( "mysql_real_escape_string" );
        }


        public static function getInstance(){
            if(!isset(self::$instance)){
                $obj = new self();
                self::$instance = $obj;
                return $obj;
            } else {
                return self::$instance;
            }
        }

        public function open_connection() {
            $this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
            if (!$this->connection) {
                die("Database connection failed: " . mysql_error());
            } else {
                $db_select = mysql_select_db(DB_NAME, $this->connection);
                if (!$db_select) {
                    die("Database selection failed: " . mysql_error());
                }
            }
        }

        public function close_connection() {
            if(isset($this->connection)) {
                mysql_close($this->connection);
                unset($this->connection);
            }
        }

        public function query($sql) {
            $this->last_query = $sql;
            $result = $this->adodb->Execute ($sql);

            return $result;
        }

        public function escape_value( $value ) {
            if( $this->real_escape_string_exists ) {
                // undo any magic quote effects so mysql_real_escape_string can do the work
                if( $this->magic_quotes_active ) { $value = stripslashes( $value ); }
                $value = mysql_real_escape_string( $value );
            } else {
                // if magic quotes aren't already on then add slashes manually
                if( !$this->magic_quotes_active ) { $value = addslashes( $value ); }
                // if magic quotes are active, then the slashes already exist
            }
            return $value;
        }


        public function fetch_array($result_set) {

            return $result_set->FetchRow();
        }

        public function num_rows($result_set) {
            return $result_set->RecordCount();
        }

        public function insert_id() {
            // get the last id inserted over the current db connection

            return  $this->adodb->Insert_ID();
        }

        public function affected_rows() {

            return $this->adodb->Affected_Rows();
        }

        private function confirm_query($result) {
            if (!$result) {
                $output = "Database query failed: " . mysql_error() . "<br /><br />";
                $output .= "Last SQL query: " . $this->last_query;

                die( $output );

            }
        }




    }

    $database = MySQLDatabase::getInstance();
    $db =& $database;

?>
