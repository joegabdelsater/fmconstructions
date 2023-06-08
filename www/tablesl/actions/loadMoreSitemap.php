<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');

if(!empty($_POST['tableName'])){
		$tableName = $_POST['tableName'];
}else{
		die("Please select a table!");
}
$options = array (); 

if(!empty($_POST['count'])){
		$limit = intval($_POST['count']); 
		$options['options']['userlimit'] = $limit;
}

$table = new Table($tableName);

$fields = $table->returnFieldsNamesTypes();

if(!empty($_POST['order_by'])){
		$orderBy = $_POST['order_by'];
		$orderBy = (string) $orderBy;
		if(isset($fields[$orderBy])){
				$options['options']['order_by'] = $orderBy;
		}
} 

$foreignKey = (string) $_POST['foreignKey'];
$foreignKeyId = intval($_POST['foreignKeyId']);   
$options['options']['foreignKey'] = $foreignKey;
$options['options']['foreignKeyId'] = $foreignKeyId;





printSitemapByTableName($tableName,$options);
die;
$table = new Table($tableName);

$fields = $table->returnFieldsNamesTypes();

$foreignKey = (string) $_POST['foreignKey'];
$foreignKeyId = intval($_POST['foreignKeyId']);

if(isset($fields[$foreignKey])){
		$sql = " `$foreignKey` = '$foreignKeyId'  OR find_in_set( '{$foreignKeyId}' ,cast(REPLACE( `{$foreignKey}` ,'|',',') as char)) > 0  ";
}

if(!empty($_POST['order_by'])){
		$orderBy = $_POST['order_by'];
		$orderBy = (string) $orderBy;
		if(isset($fields[$orderBy])){
				$sql .= " ORDER BY {$orderBy} ASC ";
		}
}

if(!empty($_POST['count'])){
		$limit = intval($_POST['count']);
		$sql .= " LIMIT {$limit} ";
}

$displayField = $table->getDisplayField();

$displayField = $displayField === false ?  'name' : $displayField;

$results = $table->findWhere($sql, array ('id', $displayField) );
$json = array();
foreach($results as $result){
		$append ['id'] = $result['id'];
		$append ['displayField'] = displayInSitemap($tableName, $result[$displayField] );
		$append ['link'] = pageLink("generate.php?table={$tableName}&id={$result['id']}");
		$json [] = $append;
}
echo json_encode($json);
?>