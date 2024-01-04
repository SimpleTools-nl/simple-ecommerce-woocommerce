<?php


define('_SIMPLE_ECOMMERCE_PLUGIN', '1');
define('_SIMPLE_DS', DIRECTORY_SEPARATOR);
define("_SIMPLE_ROOT", dirname(__DIR__));
define("_SIMPLE_LIBRARY", _SIMPLE_ROOT . _SIMPLE_DS . 'library');

require_once _SIMPLE_ROOT . _SIMPLE_DS . 'wp.php';
$simpleEcommercePlugin = simpleToolsEcommerce::getInstance();

$tmpl = lknTemplate::getInstance();



echo $tmpl->fetch_view("parameters");
