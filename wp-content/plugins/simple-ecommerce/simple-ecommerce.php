<?php
/*
 * Plugin Name: Simple E-commerce by SimpleTools.nl
 * Plugin URI: https://ecommerce.simpletools.nl/
 * Description: This tool allows you to integrate your web site with amazon and etsy. It also contains various marketing tool. You only need an API key it to work for you (API key is free)
 * Author: SimpleTool.nl
 * Text Domain: ecommerce.simpletools.nl
 * Domain Path: /languages/
 * Version: 1.0.0
 * */

// ini_set('display_errors', 1);
// error_reporting(E_ALL);


if (!defined('_SIMPLE_ECOMMERCE_PLUGIN')) {

    define('_SIMPLE_ECOMMERCE_PLUGIN', true);
    define('_SIMPLE_DS', DIRECTORY_SEPARATOR);
    define("_SIMPLE_ROOT", __DIR__);
    define("_SIMPLE_LIBRARY", __DIR__ . _SIMPLE_DS . 'library');
    define('_SIMPLE_WP_ROOT', dirname(dirname(dirname(__DIR__))));

    $plugins_url = str_replace(_SIMPLE_WP_ROOT, '', dirname(__DIR__));
    $plugins_url = str_replace(_SIMPLE_DS, '/', $plugins_url);
    define("_SIMPLE_BASE_PATH", $plugins_url . '/simple-ecommerce');
    define("_SIMPLE_WP_ADMIN_URL", get_admin_url());

    define("_SIMPLE_VERSION", "1_0_0"); //used for js and css files. 1_0_0 comes from "1.0.0". 
}

require_once __DIR__ . _SIMPLE_DS . 'wp.php';
$simpleEcommercePlugin = simpleToolsEcommerce::getInstance();
$simpleEcommerceWoocommerce = simpleToolsWooCommerce::getInstance();


if (is_admin()) {

    $simpleEcommercePlugin->getSettings();

    $simpleEcommercePlugin->setUserData();

    $simpleEcommercePlugin->registerAccountsAction();


    $simpleEcommercePlugin->actionPostUpdated();
    //if you are administrator, you can view the menu link
    $simpleEcommercePlugin->addToAdminMenu();
}
$simpleEcommercePlugin->actionCategoryUpdated();
