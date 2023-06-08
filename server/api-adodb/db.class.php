<?php

require_once('config.php');
require_once(LIB_PATH . DS . 'adodb5/adodb.inc.php');


class db
{

    static public $initialized = false;

    function __construct()
    {

        $db = NewADOConnection('mysqli');
        if ($db->ErrorMsg())
        echo $db->ErrorMsg();
        $db->Connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
       
        $db->SetFetchMode(ADODB_FETCH_ASSOC);

        if (!self::$initialized) {
            $db->Execute("SET NAMES utf8");
            $db->Execute("SET CHARACTER_SET utf8");
            self::$initialized = true;
        }

        $GLOBALS['GRIND_CONFIG']['DATABASE']['conObj'] = $db;
        if (!$GLOBALS['GRIND_CONFIG']['DATABASE']['conObj'])
            die("could't connect to SQL");
        $this->adodb = $db;

        $this->adodb->debug = DEBUG;
    }




    public function disconnect()
    {
        //  mysql_close();
    }
}
