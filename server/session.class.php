<?php
    require_once('api-adodb/config.php');
    class Session {

        private $logged_in=false;
        public $user_id;
        public $message;

        function __construct() {
            session_name(md5(DB_NAME));
            session_start();
            $this->check_message();
            $this->check_login();
        }

        public function is_logged_in() {
            return $this->logged_in;
        }

        public function login($user) {
            if($user){
                if(isset($_POST['remember'])){
                    setcookie("cookid", $user->user_id, time()+60*60*24*7, "/");
                }
                $this->user_id = $_SESSION['user_id'] = $user->id;
                $_SESSION['loginTime'] = time();
                $this->logged_in = true;
            }
        }

        public function logout() {
            if(isset($_COOKIE['cookid'])){
                setcookie("cookid", "", time()-60*60*24*7, "/");
            }
            unset($_SESSION['user_id']);
            unset($_SESSION['loginTime']);
            unset($this->user_id);
            $this->logged_in = false;
        }

        public function message($msg="") {
            if(!empty($msg)) {
                //  this is "set message"
                if(!empty($_SESSION['message'])){
                    $_SESSION['message'] .= '<br />'.$msg;
                }else{
                    $_SESSION['message'] = $msg;
                }
            } else {
                //  this is "get message"
                return $this->message;
            }
        }

        public function printMessage(){
            $message =  $_SESSION['message'];
            unset($_SESSION['message']);

            return $message;

        }

        private function validSession(){
            if(isset($_SESSION['loginTime'])){
                if(time() - $_SESSION['loginTime'] < 3600){
                    $_SESSION['loginTime'] = time(); // Renew the session
                    return true;
                }
            }
            return false;
        }

        private function check_login() {
            if($this->validSession()){
                if(isset($_SESSION['user_id'])) {
                    $this->user_id = $_SESSION['user_id'];
                    $this->logged_in = true;
                } elseif(isset($_COOKIE['cookid'])){
                    $this->user_id = $_COOKIE['cookid'];
                    $_SESSION['user_id'] = $this->user_id;
                }else {
                    unset($this->user_id);
                    $this->logged_in = false;
                }
            }else{

                if(isset($_SESSION['loginTime'])){
                    $this->message("Your session has expired!");
                }
            }
        }

        private function check_message() {
            // if there a message stored in the session
            if(isset($_SESSION['message'])) {
                // Add it as an attribute and erase the stored version
                $this->message = $_SESSION['message'];
                unset($_SESSION['message']);
            } else {
                $this->message = "";
            }
        }

    }

    $session = new Session();
    $message = $session->message();

?>