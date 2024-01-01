<?php


define('_SIMPLE_ECOMMERCE_PLUGIN','1');
define('LKN_DS',DIRECTORY_SEPARATOR);
define("LKN_ROOT",dirname(__DIR__));
define("SIMPLETOOLS_LIBRARY",LKN_ROOT.LKN_DS.'library');

require_once LKN_ROOT.LKN_DS.'wp.php';
$simpleEcommercePlugin=simpleToolsEcommerce::getInstance();

$tmpl=lknTemplate::getInstance();



echo $tmpl->fetch_view("how");
