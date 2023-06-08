<?php
    require_once('api-adodb/config.php');

    ##Manage all logins##
    class Log extends db {

        protected $id;
        protected $ip;
        protected $time;
        protected $username;
        protected $action;
        protected $password;
        protected $tableName = "`logs`";

        function __construct($username="",$success=true,$action=""){
            db::__construct();
            $this->setIp();
            $this->setTime();
            $this->username = $username;

            $this->setAction($success,$action);

            //$this->setAction($success,$action);

            if(!empty($action)){
                $this->logAction(); //Log the action
            }
        }

        public function setAction($success="",$action=""){

            $actionText = $success===true ? 'Success: ' : 'Failed: ';
            $actionText .= $action;
            $this->action = $actionText;

        }
        //Setter functions
        protected function setIp(){
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        protected function setTime(){
            $this->time = date("Y-m-d g:i:s",time());
        }
        protected function setLoginAction($success){
            $this->action = "";
            if(!$success){
                $this->action .= "Failed to ";
            }
            $this->action .= "login from '".$this->username."' ";
            if(!$success){
                $this->action .= "with password '".substr($this->password,1).'...'.substr($this->password,-1)."' ";
            }
        }

        //Finds all logs
        public function findAll($limit=10){
            $limit = (int)$limit;
            $sql = "SELECT time,action,username FROM ".$this->tableName." ORDER BY time DESC ";
            if(!empty($limit)){
                $sql .= " LIMIT $limit";
            }

            $res = $this->adodb->Execute($sql);
            if($res->RecordCount() == 0){
                $logsArray = array('No logs yet');
            }else{
                $logsArray = array();
                while($logItem = $res->FetchRow()){

                    $logsArray [] = $logItem;
                }
            }
            return $logsArray;

        }
        //Saves a log
        public function logAction(){

            if(!empty($this->action) && !empty($this->username)){

                $ip = $this->adodb->qstr($this->ip ,get_magic_quotes_gpc());
                $action = $this->adodb->qstr($this->action,get_magic_quotes_gpc());
                $username = $this->adodb->qstr($this->username,get_magic_quotes_gpc());

                $sql = "INSERT INTO ".$this->tableName."
                (
                `ip` ,
                `time` ,
                `username` ,
                `action`
                )
                VALUES (
                $ip,CURRENT_TIMESTAMP,$username,$action

                );";

                $this->adodb->Execute($sql);
            }

        }



    }

?>