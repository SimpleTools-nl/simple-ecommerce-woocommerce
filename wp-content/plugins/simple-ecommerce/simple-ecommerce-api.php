<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!defined('_SIMPLE_ECOMMERCE_PLUGIN')) {

    define('_SIMPLE_ECOMMERCE_PLUGIN', true);
    define('_SIMPLE_DS', DIRECTORY_SEPARATOR);
    define("_SIMPLE_ROOT", __DIR__);
    define("_SIMPLE_LIBRARY", __DIR__ . _SIMPLE_DS . 'library');
    define('_SIMPLE_WP_ROOT', dirname(dirname(dirname(__DIR__))));

    $plugins_url = str_replace(_SIMPLE_WP_ROOT, '', dirname(__DIR__));
    $plugins_url = str_replace(_SIMPLE_DS, '/', $plugins_url);
    define("_SIMPLE_BASE_PATH", $plugins_url . '/simple-ecommerce');

    require_once _SIMPLE_WP_ROOT . _SIMPLE_DS . 'wp-load.php';
    require_once(_SIMPLE_WP_ROOT . _SIMPLE_DS . 'wp-admin/includes/media.php');
    require_once(_SIMPLE_WP_ROOT . _SIMPLE_DS . 'wp-admin/includes/file.php');
    require_once(_SIMPLE_WP_ROOT . _SIMPLE_DS . 'wp-admin/includes/image.php');

    define("_SIMPLE_WP_ADMIN_URL", get_admin_url());
}


if (!is_admin()) {
    require_once __DIR__ . _SIMPLE_DS . 'wp.php';
    $simpleEcommercePlugin = simpleToolsEcommerce::getInstance();

    $action = lknInputFilter::filterInput($_GET, 'action');
    if ($action == 'test') {
        echo json_encode(array('status' => 1)),
        exit();
    } else if ($action == 'check_sync') {
        /**
         * 
         * DO NOT EDIT THIS. YOU HAVE BEEN WARNED. 
         * 
         * IF the ecommerce.simpletools.nl can not access this endpoint, it may stop sync this account
         * 
         * This endpoint sends the changed your products to ecommerce.simpletools.nl. so ecommerce.simpletools.nl can sync the product your other ecommerce sites
         */

        $site_title = get_bloginfo('name');
        $config = lknConfig::getInstance();


        $simpleEcommercePlugin->loadParams();
        $simpletools_api_user_id = trim(ltrim(rtrim((string)$simpleEcommercePlugin->getPostParam("simple_ecommerce_user_id"))));
        $simple_ecommerce_user_token = trim(ltrim(rtrim((string)$simpleEcommercePlugin->getPostParam("simple_ecommerce_user_token"))));
        $simple_ecommerce_account_id = trim(ltrim(rtrim((string)$simpleEcommercePlugin->getPostParam("simple_ecommerce_account_id"))));
        if ($simpletools_api_user_id == "" || $simple_ecommerce_user_token == "" || $simple_ecommerce_account_id == "") {
            echo json_encode(array('status' => 0, 'msg' => 'SimpleTool.nl settings is empty. Please complete the following steps 1) Visit plugin home page 2) Scroll down the page 3) You will see "SimpleTool.nl settings" page 4) Update those values ')),
            exit();
        }


        $responseText = array();
        try {
            require_once _SIMPLE_ROOT . _SIMPLE_DS . 'library' . _SIMPLE_DS . 'guzzle' . _SIMPLE_DS . 'vendor' . _SIMPLE_DS . 'autoload.php';
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $config->get('api_get_pending'), [
                'form_params' => [
                    'user_id' => $simpletools_api_user_id,
                    'token' => $simple_ecommerce_user_token,
                    'account_id' => $simple_ecommerce_account_id
                ],
                'timeout' => $config->get('timeout')
            ]);


            $responseText = json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable $e) {
            $responseText = array('status' => 0, 'msg' => $e->getMessage());
        }


        $result = array();
        if (isset($responseText['status'])) {

            if ($responseText['status'] == "1") {
                $rows = $responseText['msg'];
                if (!is_array($rows)) {
                    //return if the value is "There is no pending task"
                    echo json_encode(array('status' => 1, "msg" => "No product to sync"));

                    exit();
                }

                $simpleWooCommerce = simpleToolsWooCommerce::getInstance();


                $newsAdded = array();

                foreach ($rows as $pending_id => $row) {
                    $sync_product_id_target = $row['sync_product_id_target'];
                    $product_id = $row['product_id'];
                    $product_name = $row['product_name'];

                    $productData = $simpleWooCommerce->getProductByID($sync_product_id_target);


                    $productData->set_name($product_name);
                    $productData->set_description($row['product_description']);
                    $productData->set_short_description($row['product_short_description']);

                    // $product_model=$row['product_model'];
                    $productData->set_sku($row['product_sku']);
                    $productData->set_status($row['product_status']);

                    $product_price_original = $row['product_price_original']; //base price on ecommerce.simpletools.nl
                    $productData->set_sale_price($row['product_price_final']); //price will be this one
                    $productData->set_price($row['product_price_final']);
                    $productData->set_regular_price($row['product_price_final']);

                    $productData->set_weight($row['product_weight']);
                    $productData->set_length($row['product_length']);
                    $productData->set_width($row['product_width']);
                    $productData->set_height($row['product_height']);

                    $productData->set_stock_quantity($row['product_stock_quantity']);
                    $productData->set_stock_status($row['product_stock_status']);



                    $productData->set_virtual($row['product_virtual']);
                    $productData->set_downloadable($row['product_downloadable']);

                    //product downloads
                    $downloads = array();
                    $tmp = $file_id = $download = null;
                    $product_download_url = $row['product_download_url'];
                    if ($product_download_url != '') {
                        $download = new WC_Product_Download();
                        $file_id = md5($product_download_url);
                        $tmp = explode("/", $product_download_url);
                        $download->set_file($product_download_url);
                        $download->set_id($file_id);
                        $downloads[$file_id] = $download;
                    }

                    $productData->set_downloads($downloads);
                    $file_id = $downloads = $download = null;


                    //SET THE PRODUCT attributes        
                    $attributes = array();
                    $product_attributes = $row['product_attributes'];
                    if (count($product_attributes) > 0) {
                        foreach ($product_attributes as $product_attribute) {
                            $product_attribute_name = $product_attribute['attribute_title'];
                            $product_attribute_value = $product_attribute['attribute_value'];

                            //  array(9) {
                            // [0]=>
                            // array(2) {
                            //   ["attribute_title"]=>
                            //   string(8) "pa_color"
                            //   ["attribute_value"]=>
                            //   string(15) "white-with-gold"
                            // }
                            // [1]=>
                            // array(2) {
                            //   ["attribute_title"]=>
                            //   string(9) "pa_brands"
                            //   ["attribute_value"]=>
                            //   string(4) "asus"
                            // }
                            // ...

                            $attribute = new WC_Product_Attribute();
                            $attribute->set_name($product_attribute_name);
                            $attribute->set_options(array($product_attribute_value));
                            $attribute->set_visible(1);

                            $attributes[] = $attribute;
                        }
                    }

                    $productData->set_attributes($attributes);
                    $attributes = null;




                    $sync_product_id_target_new = $productData->save();
                    if ($sync_product_id_target <= 0) {
                        $newsAdded[$product_id] = $sync_product_id_target_new;
                        $sync_product_id_target = $sync_product_id_target_new;
                    }

                    //default image                    
                    $product_image = $row['product_image']; //thumb-default image
                    $product_image_name = explode('/', $product_image);
                    $product_image_name = $product_image_name[count($product_image_name) - 1];
                    $product_image_id_main = $simpleWooCommerce->getIDfromImageName($product_image_name);
                    if ($product_image_id_main <= 0) {
                        $product_image_id_main = media_sideload_image($product_image, 0, $product_image_name, 'id');
                        $simpleWooCommerce->updatePostName($product_image_id_main, $product_image_name);
                    }
                    set_post_thumbnail($sync_product_id_target, $product_image_id_main);

                    //image gallery
                    $product_images = $row['product_images'];
                    $image_ids = array();
                    if (is_array($product_images) && count($product_images) > 0) {
                        foreach ($product_images as $product_image) {
                            $product_image_name = explode('/', $product_image);
                            $product_image_name = $product_image_name[count($product_image_name) - 1];
                            $product_image_id = $simpleWooCommerce->getIDfromImageName($product_image_name);
                            if ($product_image_id <= 0) {
                                $product_image_id = media_sideload_image($product_image, 0, $product_image_name, 'id');
                                $simpleWooCommerce->updatePostName($product_image_id, $product_image_name);
                            }

                            if ($product_image_id_main != $product_image_id) {
                                // because $product_image_id_main is the default thumb
                                $image_ids[] = $product_image_id;
                            }
                        }
                    }

                    $simpleWooCommerce->updateProdductGallery($sync_product_id_target, $image_ids);


                    $product_tags = trim(ltrim(rtrim($row['product_tags']))); //comma sepeparated tags like usb,computer,red etc
                    //SET THE PRODUCT TAGS
                    wp_set_object_terms($sync_product_id_target, explode(',', $product_tags), 'product_tag');


                    //SET THE PRODUCT CATEGORIES        
                    $categories = array();
                    $product_categories = (array)$row['product_categories'];
                    if (count($product_categories) > 0) {
                        foreach ($product_categories as $product_category) {

                            //   array(1) {
                            //     [0]=>
                            //     array(2) {
                            //       ["category_title"]=>
                            //       string(21) "Headphone Accessories"
                            //       ["category_id_target_site"]=>
                            //       string(2) "72"
                            //     }
                            //   }

                            $categories[] = $product_category['category_title'];
                        }
                    }

                    wp_set_object_terms($sync_product_id_target, array_unique($categories), 'product_cat');
                    $categories = null;

                    $result[$pending_id] = $sync_product_id_target;
                }

                try {
                    //we are going to inform
                    $client = new \GuzzleHttp\Client();
                    $response = $client->request('POST', $config->get('api_get_pending_result'), [
                        'form_params' => [
                            'user_id' => $simpletools_api_user_id,
                            'token' => $simple_ecommerce_user_token,
                            'account_id' => $simple_ecommerce_account_id,
                            'update_result' => json_encode($result)
                        ],
                        'timeout' =>  $config->get('timeout')
                    ]);


                    $responseText = json_decode($response->getBody()->getContents(), true);
                } catch (\Throwable $e) {
                    //do nothing
                }
                $return = json_encode(array('status' => 1));
            } else {
                $return = json_encode(array('status' => 0, 'msg' => json_encode($responseText)));
            }
        } else {
            /**
             * that means there is a maintance period on ecommerce.simpletools.nl. 
             * do nothing
             **/
            $return = json_encode(array('status' => 0, 'msg' => json_encode($responseText)));
        }


        echo $return;
        exit();
    } else if ($action == 'do_sync') {

        /**
         * this endpoint is called by 2 different urls. it sends changes to your ecommerce.simpletools.nl account
         * 1. ecommerce.simpletools.nl will trigger this endpoint to check if there is any pending changes. We will try to access https://www.sitename.com/wp-content/plugins/simple-ecommerce/simple-ecommerce-api.php?action=do_sync
         * 2. Plugin Home > "Full Sync" button (which is on top right)
         * 
         * DO NOT EDIT THIS. YOU HAVE BEEN WARNED. 
         * 
         * IF the ecommerce.simpletools.nl can not access this endpoint, it may stop sync this account
         * 
         * This endpoint sends the changed your products to ecommerce.simpletools.nl. so ecommerce.simpletools.nl can sync the product your other ecommerce sites
         */
        $simpleWooCommerce = simpleToolsWooCommerce::getInstance();
        $syncHistoryCount = $simpleWooCommerce->getSyncHistoryCount(array('date_created' => -1, 'date_created_min' => 0));
        $totalProduct = $simpleWooCommerce->getProductsCount();
        $config = lknConfig::getInstance();

        $product_id = lknInputFilter::filterInput($_REQUEST, 'product_id');

        if ($product_id != '') {
            $sql = array('status' => 'publish', 'product_id' => $product_id);
        } else {
            $sql = array('status' => 'publish', 'post__not_in' => "1");
        }

        $sql['recordPerPage'] = $config->get('recordPerPageAPI');

        $rows = $simpleWooCommerce->getProducts2Send($sql);
        if (count($rows) <= 0) {
            if ($product_id != '') {
                echo json_encode(array('status' => 0, 'msg' => 'Product does not exists or it\'s not published'));
            } else {
                echo json_encode(array('status' => 2, 'msgnext' => '<h1>There is no product to sync or all products are synced</h1>'));
            }
            exit();
        }


        $simpleEcommercePlugin->loadParams();
        $simpletools_api_user_id = trim(ltrim(rtrim((string)$simpleEcommercePlugin->getPostParam("simple_ecommerce_user_id"))));
        $simple_ecommerce_user_token = trim(ltrim(rtrim((string)$simpleEcommercePlugin->getPostParam("simple_ecommerce_user_token"))));
        $simple_ecommerce_account_id = trim(ltrim(rtrim((string)$simpleEcommercePlugin->getPostParam("simple_ecommerce_account_id"))));
        if ($simpletools_api_user_id == "" || $simple_ecommerce_user_token == "" || $simple_ecommerce_account_id == "") {
            echo json_encode(array('status' => 0, 'msg' => 'SimpleTool.nl settings is empty. Please complete the following steps 1) Visit plugin home page 2) Scroll down the page 3) You will see "SimpleTool.nl settings" page 4) Update those values ')),
            exit();
        }

        $responseText = array();
        try {
            require_once _SIMPLE_ROOT . _SIMPLE_DS . 'library' . _SIMPLE_DS . 'guzzle' . _SIMPLE_DS . 'vendor' . _SIMPLE_DS . 'autoload.php';
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST',  $config->get('api_save_product'), [
                'form_params' => [
                    'user_id' => $simpletools_api_user_id,
                    'token' => $simple_ecommerce_user_token,
                    'account_id' => $simple_ecommerce_account_id,
                    'form_data' => json_encode($rows)
                ],
                'timeout' =>  $config->get('timeout')
            ]);


            $responseText = json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable $e) {
            $responseText = array('status' => 0, 'msg' => $e->getMessage());
        }

        if (isset($responseText['status'])) {
            if ($responseText['status'] == "1") {
                $config = lknConfig::getInstance();
                $recordPerPage = $config->get('recordPerPageAPI');

                $msgnext = "$recordPerPage products are synced. There are " . ($totalProduct - $syncHistoryCount) . " products to sync";

                if ($responseText['status'] == "1") {

                    $sql = "REPLACE INTO `#__simple_ecommerce_sync_history` (`product_id`, `date_created`) VALUES";
                    $tmp = array();
                    foreach ($rows as $k => $v) {
                        $tmp[] = " ($k, " . time() . ")";
                    }
                    $db = lknDb::getInstance();
                    $db->query($sql . implode(',', $tmp));
                    $db->setQuery();
                }

                if ($product_id != '') {
                    echo json_encode(array('status' => $responseText['status'], 'msg' => "Product is successfully updated on ecommerce.simpletools.nl", 'func' => 'document.querySelector("._simple_ecommerce_sync_date_' . $product_id . '").innerHTML="' . date('d-m-Y H:i:s') . '" '));
                } else {
                    echo json_encode(array('status' => $responseText['status'], 'msg' => $responseText['msg'], 'msgnext' => $msgnext));
                }
            } else {
                echo json_encode(array('status' => $responseText['status'], 'msg' => $responseText['msg'], 'msgnext' => ""));
            }
            exit();
        } else {

            if ($product_id != '') {
                echo json_encode(array('status' => 0, 'msg' => "Product Sync error: " . $response->getBody()->getContents()));
            } else {
                echo json_encode(array('status' => 0, 'msg' => $response->getBody()->getContents()));
            }


            exit();
        }
    } else if ($action == 'reset_all') {
        $db = lknDb::getInstance();
        $db->query('TRUNCATE TABLE #__simple_ecommerce_sync_history');
        $db->setQuery();


        echo json_encode(array('status' => 1, 'msg' => '', 'func' => 'window.location.reload();'));
        exit();
    } else if ($action == 'save_settings') {
        require_once _SIMPLE_ROOT . _SIMPLE_DS . "task" . _SIMPLE_DS . "save_settings.php";
        exit();
    }

    echo json_encode(array('status' => 2)),
    exit();
}
