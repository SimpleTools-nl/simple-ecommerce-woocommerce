<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');


$tmpl = lknTemplate::getInstance();

$simple_message = lknInputFilter::filterInput($_GET, 'simple_message');
$tmpl->set('simple_message', $simple_message);


echo $tmpl->fetch_view("msg");
?>
