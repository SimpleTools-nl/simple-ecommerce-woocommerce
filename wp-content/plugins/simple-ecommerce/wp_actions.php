<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

use phpFastCache\Helper\Psr16Adapter;


class simpleToolsEcommerce_WP_Actions
{


    function __construct()
    {
    }


    /**
     *
     * @return simpleToolsEcommerce_WP_Actions
     */
    public static function getInstance()
    {
        static $_instance;
        if (!isset($_instance)) {
            $_instance = new simpleToolsEcommerce_WP_Actions();
        }

        return $_instance;
    }


    function setUserData()
    {
        global $current_user;

        if (isset($current_user->ID)) {

            $user = lknUser::getInstance();
            $user->set('id', $current_user->ID);
            $user->set('email', $current_user->user_email);
            $user->set('name', $current_user->user_nicename);
            $user->set('username', $current_user->user_login);
            $user->set('registerDate', $current_user->user_registered);
            $user->set('lastvisitDate', $current_user->last_activity);
            $user->set('my', $current_user);
            $user->set('usertype', $current_user->roles);
        }
    }


    /**
     * Register a custom menu page.
     */
    function addToAdminMenu()
    {

        add_menu_page('Simple Ecommerce', 'Simple Ecommerce', 'manage_options', "simple-ecommerce-admin.php", array(
            $this,
            'adminPage'
        ));
    }


    function adminPage()
    {

        require_once _SIMPLE_ROOT . _SIMPLE_DS . 'simple-ecommerce-admin.php';
    }


    function addheader()
    {
        if (!$_POST) {
            wp_enqueue_script('simpletools_main', _SIMPLE_BASE_PATH . '/views/assets/js/main.js?' . _SIMPLE_VERSION);
            wp_enqueue_style('simpletools_css', _SIMPLE_BASE_PATH . "/views/assets/css/main.css?3" . _SIMPLE_VERSION);
            wp_enqueue_style('simpletools_toast_css', _SIMPLE_BASE_PATH . "/views/assets/jquery/toastr/toastr.css?3" . _SIMPLE_VERSION);
            wp_enqueue_script('simpletools_toast_js', _SIMPLE_BASE_PATH . "/views/assets/jquery/toastr/toastr.js?3" . _SIMPLE_VERSION);
        }
    }
    function simple_ecommerce_category_updated($id, $post, $update)
    {

        $simpleTools = simpleToolsWooCommerce::getInstance();
        $products = $simpleTools->getProducts(array('status' => 'publish', 'cat' => $id));


        if (count($products) > 0) {
            $product_id = stripslashes($products[0]->get_id());
            //just reset the last sync time. so system will re-sync with user account on ecommerce.simpletools.nl
            $sql = "REPLACE INTO `#__simple_ecommerce_sync_history` (`product_id`, `date_created`) VALUES ($product_id, 0)";
            $db = lknDb::getInstance();
            $db->query($sql);
            $db->setQuery();
        }
    }

    function post_updated($post_id, $post, $update)
    {

        // verify if this is an auto save routine.
        // If it is our form has not been submitted, so we dont want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }


        if (!current_user_can("edit_post", $post_id)) {
            return $post_id;
        }


        if ($post->post_status != 'publish' || $post->post_type != 'product') {
            return $post_id;
        }

        if (!$product = wc_get_product($post)) {
            return $post_id;
        }


        //just reset the last sync time. so system will re-sync with user account on ecommerce.simpletools.nl
        $sql = "REPLACE INTO `#__simple_ecommerce_sync_history` (`product_id`, `date_created`) VALUES ($post_id, 0)";
        $db = lknDb::getInstance();
        $db->query($sql);
        $db->setQuery();
    }

    function get($var)
    {
        if (isset($this->$var)) {
            return $this->$var;
        } else {
            return null;
        }
    }
}

class simpleToolsEcommerce_WP_Actions_Text
{
    function __construct()
    {
    }


    /**
     * bir metin içerisinde belirli sayıda karakteri alır
     *
     * @param string $string
     * @param integer $length
     * @param string $replacer
     *
     * @return string
     */
    function limitText($string, $length, $replacer = '...')
    {

        $l = strlen($string);
        if ($string != '' && $l > 0 && $l > $length) {
            if (function_exists('mb_substr')) {
                return mb_substr($string, 0, $length, 'utf-8') . $replacer;
            } else {
                return substr($string, 0, $length) . $replacer;
            }
        } else {
            return $string;
        }
    }

    /**
     * @param $string
     * @param $word_limit
     *
     * @return string
     */
    function limitWords($string, $word_limit, $replacer = '...')
    {
        $words = explode(' ', $string);
        if (count($words) > $word_limit) {
            return implode(' ', array_slice($words, 0, $word_limit)) . $replacer;
        } else {
            return $string;
        }
    }
}
