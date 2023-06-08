
<?php

class Jihane {

    function replace ($orignalArray,$index,$newWord){
        $orignalArray [$index] = $newWord;
        return $orignalArray;
    }

    function joinArray($arrayToJoin, $separator){
        return implode($separator,$arrayToJoin);
    }

    function addToString($originalString,$stringToAdd){
        $finalString = $originalString ." ". $stringToAdd;
        return $finalString;
    }

    function splitString($string,$splitvalue){
        return explode($splitvalue,$string);
    }
    
}

?>
