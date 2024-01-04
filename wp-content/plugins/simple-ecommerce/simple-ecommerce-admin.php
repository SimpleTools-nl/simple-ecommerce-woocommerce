<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

$task = lknInputFilter::filterInput($_REQUEST, "task", 'list');
if (file_exists(_SIMPLE_ROOT . _SIMPLE_DS . 'task' . _SIMPLE_DS . $task . '.php')) {

	require_once _SIMPLE_ROOT . _SIMPLE_DS . 'task' . _SIMPLE_DS . $task . '.php';
} else {
	echo "<h1>Task is not found</h1>";
}
