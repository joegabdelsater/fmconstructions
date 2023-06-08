<?php

require("myfunctions.php");

$TestArray = ["pizza", "burger", "pasta"];

$twin = new Jihane();

$modifiedArray = $twin->replace($TestArray,2,"hot dog");
// print_r($modifiedArray);

$newString = joinArray($modifiedArray, "and");
// echo "<pre>";
//  print_r($newString);
//  echo "</pre>";

$finalString = addToString($newString,"love");
echo "<pre>";
 print_r($finalString);
 echo "</pre>";
//
//
// $finalArray= splitString($finalString,"and");
//
// print_r($finalArray);
//
// foreach ($finalArray as $value) {
//     echo $value;
// }




 ?>
