<?php
    require_once('../../../server/api-adodb/config.php');

    function checkIf($t1,$t2,$name){

        if($t1 != $t2){
            echo '<strong>'.$name.'</strong> ' .$t1.'<br />';
        }else{
            echo $name.' is correctly configured. <br />';
        }
    }
    $remove =  DS.'web'.DS.'install'.DS.'tools';

    $siteRoot = substr(getcwd(),0,strlen(getcwd()) - strlen($remove));


    checkIf($siteRoot,SITE_ROOT,'SITE_ROOT');

    $htmlSite = HTML_SITE;

    $htmlServer = $_SERVER['HTTP_HOST'];

    if(strpos($_SERVER['REQUEST_URI'],DS.'trunk'.DS) !== false){
        $htmlServer .= DS.'trunk'.DS.'web'; 
    }elseif(strpos($_SERVER['REQUEST_URI'],DS.'web'.DS) !== false){
        $htmlServer .= DS.'web'.DS; 
    }

    checkIf($htmlServer,HTML_SITE,'HTML_SITE');

    $htmlRoot = str_replace($_SERVER['HTTP_HOST'],"",$htmlServer);

    checkIf($htmlRoot,HTML_ROOT,'HTML_ROOT');
    
    if(is_dir(PUBLIC_PATH.DS.'install')){
    echo '<strong style="color:red">WARNING!</strong> Please, do not forget to delete the installation folder!';
    }

?>