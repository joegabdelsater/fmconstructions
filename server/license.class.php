<?php

    /**
    * Text encryption class - Encrypts a string using a Grind License Key defined in as a class constant
    */
    class License
    {
        const ENCRYPTION_CYPHER = MCRYPT_RIJNDAEL_256;
        const MODE   = MCRYPT_MODE_CBC;
        //Unique license key used for each project
        const GRIND_LICENSE_KEY    = 'GrindCMSGEN2012';

        const GRIND_MAIL_TO    = 'joegabdelsater@gmail.com';
        const ALLOW_SEND_EMAIL    = false; // Turn on / off email notifications to GRIND_EMAIL_TO
        const MAXIMUM_DOMAINS_LICENSE = 1; //Allow the use of this CMS on x number of domain names


        /**
        * Folder where the license file is placed in
        * 
        */
        public static function getFilePath (){
            return LIB_PATH.DS.'adodb5'.DS.'drivers'.DS.'adodb-lds.php';
         //   return LIB_PATH.DS.'api-adodb'.DS.'notification.txt';
        }



        /**
        * Function that initializes a system. passed with the encrypted text that contanins all validity information
        * 
        * @param mixed $encryptedText
        */

        public static function initializeSystem($encryptedText){
            $result = self::decrypt($encryptedText);
            $result = json_decode($result);

            if(isset($result['expiry_date'])){
                if(date() > strtotime($result['expiry_date'])){
                    throw new Exception("Your subscription has expired. Please renew it.");   
                }
            }

            self::notifyUsageByDomain();

        }

        /**
        * Notify Grind that the CMSGEN is being used.
        * 
        */
        public static function notify(){

            $domain = $_SERVER['HTTP_HOST'];   
            $file = License::getFilePath();

            $content = self::readNotificationFile();

            if(isset($content->notifiedDomains)){
                foreach($content->notifiedDomains as $domainUsed){
                    if($domainUsed->domain  == $domain){
                        if($domainUsed->notified){
                            return true;   
                        }
                    }
                }
            }else{
                $content = (object) $content;
                $content->notifiedDomains = array ();
            }
            //If not notified yet : 
            $content->notifiedDomains[] = array (
                'domain' => $domain,
                'notified' => true
            );

            self::writeToFile($content);

            if(self::ALLOW_SEND_EMAIL){
                sendmail(
                    self::GRIND_MAIL_TO,
                    'CMSGEN Usage Notification',
                    "CMS is being used on the following domain: {$domain}."
                );
            }
        }

        /**
        * Write the license details to the notifications file
        * 
        * @param string $key
        * @param mixed $value
        */

        public static function writeToLicenseDetails($key, $value = NULL ){

            $content = self::readNotificationFile();
            if(isset($content->licenseDetails)){
                $content->licenseDetails->$key = $value;
            }else{
                $content->licenseDetails[$key] = $value;
            }

            return self::writeToFile($content);
        }

        /**
        * Write the allowed domains to the notification file
        * 
        * @param array $allowedDomains
        */
        public static function setAllowedDomains($allowedDomains = array () ){
            return self::writeToLicenseDetails('allowedDomainsList',$allowedDomains);
        }

        /**
        * Write the client name to the notification file
        * 
        * @param string $allowedDomains
        */
        public static function setClient( $client ){
            return self::writeToLicenseDetails('client',$client);
        }      

        /**
        * Write the client name to the notification file
        * 
        * @param int $allowedDomains
        */
        public static function setMaximumAllowedDomains( $value ){
            return self::writeToLicenseDetails('maximumDomains',$value);
        }  

        /**
        * Write the license expiry date to the notification file
        * 
        * @param date $allowedDomains
        */
        public static function setExpiryDate( $value ){
            return self::writeToLicenseDetails('licenseExpiryDate',$value);
        }


        public static function setDefaults(){
            ####################################################################
            self::setAllowedDomains(array ('grindd.com','*.grindd.com' , '*.local' ));
            self::setClient( 'Grindd' );
            self::setMaximumAllowedDomains( 10 );
            self::setExpiryDate( date('Y-m-d', strtotime("+1 year") ));
            ####################################################################   
        }


        /**
        * Write content to notification file
        * 
        * @param array $content
        */
        public static function writeToFile($content){
            $file = License::getFilePath();
            $content = json_encode($content);
            $content = self::encrypt($content);
            file_put_contents($file, $content); 
        }

        /**
        * Read the encrypted notifications file
        * @return stdClass Object Array
        */
        public static function readNotificationFile(){

            $file = License::getFilePath();
            $notification = array () ;
            if(file_exists($file)){
                $notifcation = file_get_contents($file);
                $notifcation = self::decrypt($notifcation);
                $notification = json_decode($notifcation);
            }else{
                $content = array (); 
                $content = json_encode($content);
                $content = self::encrypt($content);
                file_put_contents($file, $content);  
            }

            return $notification;
        }

        /**
        * Controller function to notify the usage of the cmsgen on a certain domain name
        * 
        */
        public static function notifyUsageByDomain(){
        return true;
            try{
                $notifcation = '';
                $domain = $_SERVER['HTTP_HOST'];   

                self::notify();

                $notification = self::readNotificationFile();

                $domains = array ();
                if(isset($notifcation->notifiedDomains)){
                    foreach($notification->notifiedDomains as $n){
                        $domains[$n->domain] = true;
                    }
                }

                if(!isset($notification->licenseDetails) || !isset($notification->licenseDetails->allowedDomainsList) || empty($notification->licenseDetails->allowedDomainsList)){
                    $allowedToUseOnDomain = true; // Unlimited Usage , no domain specified
                }else{
                    $allowedToUseOnDomain = false;
                }


                if(isset($notification->licenseDetails) && isset($notification->licenseDetails->allowedDomainsList)){
                    foreach ( $notification->licenseDetails->allowedDomainsList as $allowedDomain ) {
                        if($domain == $allowedDomain){
                            $allowedToUseOnDomain = true;  
                        } else if (strpos($allowedDomain,'*') !== false ) {

                            //Check for wild card
                            list($dummy,$allowedDomain) = explode("*.",$allowedDomain);

                            if(strpos($domain,$allowedDomain) !== false){
                                $allowedToUseOnDomain = true;  
                            }
                        }
                    }
                }

                if(!$allowedToUseOnDomain){
                    throw new Exception("You are currently on {$domain}. You are only allowed to use this CMS on the following domains: " . join (", ",$notification->licenseDetails->allowedDomainsList));
                }

            } catch (Exception $e){
                echo $e->getMessage();
                exit;
            }

        }

        /**
        * Returns a string with the encrypted text
        * 
        * @param mixed $plaintext
        */
        public static function encrypt($plaintext)
        {
            $td = mcrypt_module_open(self::ENCRYPTION_CYPHER, '', self::MODE, '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            mcrypt_generic_init($td, self::GRIND_LICENSE_KEY, $iv);
            $encryptedText = mcrypt_generic($td, $plaintext);
            mcrypt_generic_deinit($td);
            return $iv.$encryptedText;
        }

        /**
        * Returns a string with the decrypted text
        * 
        * @param mixed $encryptedText
        */
        public static  function decrypt($encryptedText)
        {
            $encryptedText = $encryptedText;
            $plaintext = '';
            $td        = mcrypt_module_open(self::ENCRYPTION_CYPHER, '', self::MODE, '');
            $ivsize    = mcrypt_enc_get_iv_size($td);
            $iv        = substr($encryptedText, 0, $ivsize);
            $encryptedText = substr($encryptedText, $ivsize);
            if ($iv)
            {
                mcrypt_generic_init($td, self::GRIND_LICENSE_KEY, $iv);
                $plaintext = mdecrypt_generic($td, $encryptedText);
            }
            return trim($plaintext);
        }



    }

?>
