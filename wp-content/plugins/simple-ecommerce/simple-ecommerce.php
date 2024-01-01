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
    define('LKN_DS', DIRECTORY_SEPARATOR);
    define("LKN_ROOT", __DIR__);
    define("SIMPLETOOLS_LIBRARY", __DIR__ . LKN_DS . 'library');
    define('_SITE_ROOT', dirname(dirname(dirname(__DIR__))));

    $plugins_url = str_replace(_SITE_ROOT, '', dirname(__DIR__));
    $plugins_url = str_replace(LKN_DS, '/', $plugins_url);
    define("LKN_BASE_PATH", $plugins_url . '/simple-ecommerce');
    define("LKN_WP_ADMIN_URL", get_admin_url());
}

require_once __DIR__ . LKN_DS . 'wp.php';
$simpleEcommercePlugin = simpleToolsEcommerce::getInstance();
$simpleEcommerceWoocommerce = simpleToolsWooCommerce::getInstance();


if (is_admin()) {



    $simpleEcommercePlugin->getSettings();

    $simpleEcommercePlugin->setUserData();

    $simpleEcommercePlugin->registerAccountsAction();


    $simpleEcommercePlugin->actionPostUpdated();
    //if you are administrator, you can view the menu link
    $simpleEcommercePlugin->addToAdminMenu();

    //	$simpleEcommercePlugin->addMetaBox();


}
$simpleEcommercePlugin->actionCategoryUpdated();
