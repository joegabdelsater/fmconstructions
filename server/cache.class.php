<?php
    class Cache {

        var $iTtl = 600; // Time To Live
        var $bEnabled = false; // APC enabled?
        var $key;

        function __construct($key,$time=600){
            $this->key = $key;
            $time = (int)$time;
            $this->iTtl = $time;
            $this->bEnabled = extension_loaded('apc');
        }

        /**
        * Deletes cached objects by Tablename. Delete all caches related to the selected table
        * 
        * @param mixed $tableName
        */
        static function deleteByTableName($tableName){

            $o = new self('s');
            if(!$o->cacheEnabled()){
                return false;
            }

            $aCacheInfo = apc_cache_info('user');

            foreach($aCacheInfo['cache_list'] as $_aCacheInfo)
                if(strpos($_aCacheInfo['info'], $tableName) === 0)
                    apc_delete($_aCacheInfo['info']);
        }

        function cacheEnabled(){
               return false;
            return $this->bEnabled;
        }

        function createKey($key,$query=false){
            if(isset($key)){
                $this->key = md5($key);
            }elseif(isset($query)){
                $this->key = md5($query);
            }
        }

        // get data from memory
        function fetch() {


            if(!$this->cacheEnabled()){

                return false;
            }

            $sKey = $this->key;
            $bRes = false;
            if(!isset($this->data))
                $this->data = apc_fetch($sKey, $bRes); //$bRes is passed by reference
            return (isset($this->data)) ? $this->data : null;
        }

        // save data to memory
        function store($vData) {
            if(!$this->cacheEnabled()){
                return false;
            }
            $sKey = $this->key;
            return apc_store($sKey, $vData, $this->iTtl);
        }

        // delete data from memory
        function delete() {
            if(!$this->cacheEnabled()){
                return false;
            }

            $data = $this->fetch();
            return (!empty($data)) ? apc_delete($this->key) : true;
        }
    }

?>