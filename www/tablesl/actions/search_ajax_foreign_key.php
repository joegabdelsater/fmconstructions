<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();

$tableName = $_GET['table'];
$foreignKey = $_GET['foreignKey'];
$originalTableName = $_GET['originalTable'];
$originalTable = new Table($originalTableName);
$table = new TableRelationship($tableName);
$term = $_GET['q']['term'];
$displayField = $table->getDisplayField();
$results =  $table->findContainingAllBelongsTo(array(
		'conditions' => array (
				'`'.$table->getRawTableName().'`.`'.$displayField.'`' .' LIKE' => '%'.$term.'%'
		),
		'limit' => 20
));

/*


$hierarichalResult = array (); 

$path = $table->getTableTraversalPath();

if(isset($path[0]['join_table'])){
$parentTableName =   $path[0]['join_table']; 
$parentTable = new Table($parentTableName);
$parentTableDisplayField = $parentTable->getDisplayField();
}

*/



$json = array ();
$i  = 0;
if(is_array($results)){
		foreach($results as $result){
				/*
				if(isset($parentTableName)){
				$json[$result[$parentTableName]['id']]['text'] = $result[$parentTableName][$parentTableDisplayField];
				$json[$result[$parentTableName]['id']]['children'][$i]['id'] = $result['id'];
				$json[$result[$parentTableName]['id']]['children'][$i]['text'] = $result[$displayField];   

				}else{
				*/            

				$json[$i]['id'] = $result['id'];
				$json[$i]['text'] = $result[$displayField];   
				$json[$i]['text'] = strip_tags(join(' > ', array_reverse($originalTable->createAdminBreadCrumb($result['id'],$foreignKey))) );

				/*       
				}
				*/

				$i++;
		}
}

$result = array (); 
foreach ( $json as $r ) {
		$result[] = $r;   
}

echo json_encode($result);
?>