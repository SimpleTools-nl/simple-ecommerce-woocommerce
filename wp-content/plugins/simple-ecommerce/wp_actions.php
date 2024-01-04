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


    //	function addMetaBox(){
    //		add_meta_box("demo-meta-box","Auto Post To Social Media With simpleToolsEcommerce.com",array(
    //			$this,
    //			'custom_meta_box_markup'
    //		),null,"side","high");
    //
    //		add_action('admin_enqueue_scripts',array($this,'addheader'));
    //	}


    function addheader()
    {
        if (!$_POST) {
            wp_enqueue_script('simpletools_main', _SIMPLE_BASE_PATH . '/views/assets/js/main.js?' . time());
            wp_enqueue_style('simpletools_css', _SIMPLE_BASE_PATH . "/views/assets/css/main.css?3" . time());
            wp_enqueue_style('simpletools_toast_css', _SIMPLE_BASE_PATH . "/views/assets/jquery/toastr/toastr.css?3" . time());
            wp_enqueue_script('simpletools_toast_js', _SIMPLE_BASE_PATH . "/views/assets/jquery/toastr/toastr.js?3" . time());
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


    function custom_meta_box_markup($meta_boxes)
    {

        $simpleEcommercePlugin = simpleToolsEcommerce::getInstance();
        $msg = '';


        $simpleEcommercePlugin->loadParams();
        $simpletools_api_key = trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simpletools_api_key"))));
        $simpletools_api_user_id = trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simpletools_api_user_id"))));
        $post_format = $simpleEcommercePlugin->getPostParam('post_format');


?>
        <div class="simpletools_categorydiv">

            <?php wp_nonce_field(basename(__FILE__), "simpletools_custom_meta_box_markup"); ?>

            <?php if ($simpletools_api_key != '' && $simpletools_api_user_id != '' && $post_format != '') { ?>
                <div class="_simpletools_meta_box">
                    Loading...
                </div>

                <table width="100%">
                    <tbody>

                        <tr>
                            <td scope="row"><label for="simpletools_post_format">Post Format</label></td>

                        </tr>
                        <tr>
                            <td><textarea name="simpletools_post_format" id="simpletools_post_format" style="width: 100%;" cols="20" rows="3"><?php echo $post_format; ?></textarea>

                                <div class="simpletools_how">
                                    <?php add_thickbox(); ?>
                                    <a href="<?php echo _SIMPLE_BASE_PATH; ?>/task/parameters.php?TB_iframe=true&width=600&height=550" class="thickbox">View Parameters Information</a><br /><br />

                                    <a href="<?php echo _SIMPLE_BASE_PATH; ?>/task/how.php?TB_iframe=true&width=600&height=550" class="thickbox">How this plugin works</a>
                                </div>

                        </tr>


                    </tbody>
                </table>
            <?php } else {
            ?>

                <div>
                    <h5>Please enter your API key and User ID before you start using it. You can edit your settings from
                        <a href="<?php echo admin_url(); ?>admin.php?page=simple-ecommerce-admin.php">this link</a>
                    </h5>

                </div>
            <?php
            } ?>


        </div>
<?php


    }

    function postProduct()
    {
        $simpleEcommercePlugin = simpleToolsEcommerce::getInstance();

        $simpleEcommercePlugin->loadParams();
        $simpletools_api_key = trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simpletools_api_key"))));
        $simpletools_api_user_id = trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simpletools_api_user_id"))));


        // Setter action
        $url = "https://www.simpletools.com/api/publisher/accounts?user_id=$simpletools_api_user_id&token=$simpletools_api_key";


        // First, we try to use wp_remote_get
        $response = wp_remote_get($url);
        if (!is_wp_error($response)) {

            $response = $response['body'];
            if ($response != '') {
                $row = json_decode($response);

                if (isset($row->status) && $row->status == '1') {
                    $Psr16Adapter->set($keyword, $response, 86400);
                }
            } else {
                //no response or empty body from server
                lknredirect("admin.php?page=simple-ecommerce-admin.php", '', 'Your settings are saved but we are able to get response from lksuite.com. Please wait 2 minutes are try it again');
            }
        } else {

            //we are not able to get content because of CURL - hosting issue wp_remote_get returns WP_Error
            $wp_error_text = $response->get_error_message();
            lknredirect("admin.php?page=simple-ecommerce-admin.php", '', 'Your settings are saved but we are able to get your because of your hosting. CURL returns ' . urlencode($wp_error_text));
        }


        return $response;
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




?>