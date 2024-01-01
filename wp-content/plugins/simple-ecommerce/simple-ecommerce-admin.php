<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

$task=lknInputFilter::filterInput($_REQUEST,"task",'list');
if(file_exists(LKN_ROOT.LKN_DS.'task'.LKN_DS.$task.'.php')){
	
	require_once LKN_ROOT.LKN_DS.'task'.LKN_DS.$task.'.php';
}else{
	echo "<h1>Task is not found</h1>";
}
