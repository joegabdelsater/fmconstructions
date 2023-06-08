<?php
    require_once('api-adodb/config.php');
    require_once(LIB_PATH.DS.'database.class.php');
    require_once(LIB_PATH.DS.'session.class.php');
    require_once(LIB_PATH.DS.'pclzip.lib.php');

    class ZipUpload {

        protected static $table_name="cmsgen_gallerypictures";
        protected static $db_fields = array('id', 'gallery_id', 'image_path');

        public $id;
        public $gallery_id;
        public $image_path;

        public $errors = array();
        public $messages = array();

        public function upload(){
            $filename = $_FILES['zip']['name']; //the filename
            if(strstr($filename,".")!=".zip"){
                $this->errors['Invalid file'] = 'This is not a valid zip file!';
                return false;
            }



            $gallery_id = (int)$_POST['gallery_id'];
            $publicUploadFolder = IMAGES_DIR.DS.'bulk'.DS.$gallery_id;

            $upload_dir = PUBLIC_PATH.DS.$publicUploadFolder; //your upload directory NOTE: CHMODD 0777
            $tmp_folder = $upload_dir.'/tmp'; //temp folder

            if(!is_dir($tmp_folder)){
                mkdir($tmp_folder);
            }

            if(!is_dir($upload_dir)){
                mkdir($upload_dir,0755,true);
            }

            $known_ext=array(".jpg",".png",".jpeg",".tiff"); // allowed extension


            //move file
            if(move_uploaded_file($_FILES['zip']['tmp_name'], $upload_dir.'/'.$filename)){
                $this->messages['Zip Uploaded'] = "Uploaded ". $filename . " - ". $_FILES['zip']['size'] . " bytes";
            } else{
                $this->errors['Error uploading'] = 'Unable to upload file!';
                return false;
            }

            $zip_dir = basename($filename, ".zip"); //get filename without extension fpr directory creation

            $archive = new PclZip($upload_dir.'/'.$filename);

            if ($archive->extract(PCLZIP_OPT_PATH, $tmp_folder) == 0){
                $this->errors['Error extracting'] = 'Unable to extract file!';
                return false;
            }

            //show what was just extracted
            $list = $archive->listContent();
            for ($i=0; $i<sizeof($list); $i++) {
                $ext=strstr($list[$i]['filename'],".");

                if(in_array(strtolower($ext),$known_ext)){
                    $pic_name[$i]= $list[$i]['filename'];
                    $newname=time()."-".$i.$ext;

                    $tempFile = $tmp_folder."/".$pic_name[$i];
                    $uploadToFile = $upload_dir."/".$newname;

                    if(!rename($tempFile,$uploadToFile)){
                        $this->errors['Error rename'] = "COULDNT RENAME/MOVE file ".$pic_name[$i];
                        return false;
                    }

                    $upload = new ZipUpload();
                    $upload->image_path = $publicUploadFolder.DS.$newname;
                    $upload->gallery_id = (int)$_POST['gallery_id'];

                    if(!$upload->save()){
                        $this->errors[] = $pic_name[$i] . ' could not be saved!';
                    }
                }
            }

            @unlink($upload_dir.'/'.$filename); //delete uploaded file
            @unlink($tmp_folder); //delete uploaded folder

            if(empty($this->errors))
                return true;
            else
                return false;
        }

        // Common Database Methods
        public static function find_all() {

            $sql = "SELECT * FROM ".self::$table_name;

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