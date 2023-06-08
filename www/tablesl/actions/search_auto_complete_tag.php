<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();

$tableName = $_GET['table'];
$table = new Table($tableName);
$term = $_GET['q'];

$displayField = $table->getDisplayField();

$results =  $table->safeFind(array(
				'conditions' => array (
						$displayField .' LIKE' => '%'.$term.'%'
				),
				'limit' => 20
		));

$json = array ();
$i  = 0;
if(is_array($results)){
		foreach($results as $result){
				$json[$i]['id'] = $result['id'];   
				$json[$i]['text'] = $result[$displayField];   
				$i++;
		}
}
echo json_encode($json);
?>