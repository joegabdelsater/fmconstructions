<?php
/**
*CSRF class
*
* March 13 - 2013
* V1.0 - Geroge
*
*/
    require_once('api-adodb/config.php');
    class CSRF{
        private $token = false;
        private $oneTime; // Token is only used once
        
        private $enabled = true;//Enable or disable CSRF protection

        /**
        * Constrcuts a new CSRF object. Onetime means that this token can only be used once per request.
        *
        * @param bool $oneTime
        * @return CSRF
        */
        function __construct($oneTime=true){
            if(!isset($_SESSION))
            session_start();
            
            if(isset($_SESSION['token'])){
                $this->token = $_SESSION['token'];
                $this->oneTime = $oneTime;
            }
        }

        /**
        * Function that returns a token.
        *@return string
        */
        static function generateToken(){
            if(!isset($_SESSION))
            session_start();

            if(isset($_SESSION['token'])){
                return $_SESSION['token'];
            }

            $token = md5(uniqid(rand(), TRUE));
            $_SESSION['token'] = $token;
            return $token;
        }

        /**
        * Static call to validate a token
        *
        * @param string $token
        * @param bool $oneTime
        */
        static function validate($token,$oneTime = true){
            $object = new self($oneTime);
            return $object->validateToken($token);
        }

        /**
        * Instantiated call to validate a token
        *
        * @param string $token
        */
        function validateToken($token){
            if(!$this->enabled){
                return true;   
            }
            
     
            if(!empty($token) && ($this->token == $token) ){
                $this->unsetToken();
                return true;
            }else{
                throw new Exception("Invalid Request!");
            }
        }

        /**
        * Function to that unsets the token
        *
        */

        function unsetToken(){
            if($this->oneTime){
                $this->token = false;
                unset($_SESSION['token']);
            }
        }
    }
?>