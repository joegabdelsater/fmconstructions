<?php
    require_once('api-adodb/config.php');
    require_once(LIB_PATH.DS.'session.class.php');

    class Site extends db {

        public $keywords;
        public $description;
        public $project_name;
		public $title_bar;
        public $siteOffline;
        public $contact_email;
        public $favicon;
		//public $from_email;

        function __construct(){

            db::__construct();


            $this->getContent();

        }

        public function getContent(){
            $sql = "SELECT * FROM `site_options` WHERE id='1' LIMIT 1;";
            $res = $this->adodb->Execute($sql);
            if($res) {
                $row = $res->FetchRow();

            $this->keywords = $row['meta_keywords'];
            $this->description = $row['meta_description'];
            $this->favicon = $row['favicon'];
            $this->siteOffline = $this->siteOffline();
            $this->project_name = $row['project_name'];
			$this->title_bar = $row['title_bar'];
            $this->contact_email = $row['contact_email'];
			//$this->from_email = $row['from_email'];
        }


        }


        public function siteOffline(){
            $sql = "SELECT * FROM `admin_advanced_settings` WHERE id='1' LIMIT 1;";
            $res = $this->adodb->Execute($sql);

            // $row = $res->FetchRow();

            // return ($row['site_offline'] == 1) ? true : false;
            return false;
        }



    }
