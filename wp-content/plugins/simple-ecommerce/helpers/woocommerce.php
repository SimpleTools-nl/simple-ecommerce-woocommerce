<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

if (!class_exists('simpleToolsWooCommerce')) {
    return;
}

class simpleToolsWooCommerce
{


    function __construct()
    {
    }


    /**
     *
     * @return simpleToolsWooCommerce
     */
    public static function getInstance(): simpleToolsWooCommerce
    {
        static $_instance;
        if (!isset($_instance)) {
            $_instance = new simpleToolsWooCommerce();
        }

        return $_instance;
    }

    public function getProductByID($product_id): WC_Product|null|false
    {
        $product = wc_get_product($product_id);

        return $product;
    }

    public function getIDfromImageName($guid)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name='%s'", $guid));
    }

    /**
     * update woocommerce product gallery
     *
     * @param  mixed $product_id
     * @param  mixed $image_id_array
     * @return void
     */
    function updateProdductGallery($product_id, $image_id_array)
    {
        if (is_array($image_id_array) && count($image_id_array) > 0) {
            update_post_meta($product_id, '_product_image_gallery', implode(',', $image_id_array));
        } else {
            //remove gallery
            update_post_meta($product_id, '_product_image_gallery', "");
        }
    }

    public function updatePostName($ID, $post_name)
    {

        $db = lknDb::getInstance();

        $post_name = $db->_escape($post_name);
        $ID = $db->_escape($ID);
        $sql = "UPDATE #__posts SET post_name='$post_name' WHERE ID=$ID";

        $db->query($sql);
        $db->setQuery();
    }

    /**
     * Get all products for first sync
     * @param array $data
     * @return array
     */
    public function getProducts($data = array()): array
    {

        $config = lknConfig::getInstance();
        if (isset($data['recordPerPage'])) {
            $recordPerPage = $data['recordPerPage'];
        } else {
            $recordPerPage = $config->get('recordPerPage');
        }


        $start = lknInputFilter::filterInput($_REQUEST, 'start', '', 'INT');

        if ($start == '' || $start == 0) {
            $start = 1;
        }

        $sayfadakiKayit = (int)$recordPerPage;
        $limitStart = ($start - 1) * $sayfadakiKayit;

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $limitStart,
            'posts_per_page' => $recordPerPage,
            'meta_query' => array(),
            'orderby' => array('meta_value_num' => 'DESC', 'ID' => 'DESC')
        );

        if (isset($data['cat']) && $data['cat'] != '') {
            $args['cat'] = $data['cat'];
        }

        if (isset($data['sku']) && $data['sku'] != '') {
            $args['meta_query'][] = array(
                'key' => '_sku',
                'value' => $data['sku']
            );
        }

        if (isset($data['product_id']) && $data['product_id'] != '') {
            $args['p'] = $data['product_id'];
        }


        if (isset($data['status']) && $data['status'] != '') {
            $args['status'] = $data['status'];
        }

        if (isset($data['post__not_in'])) {
            $syncedOnes = $this->getSyncedProductIDs();
            if (count($syncedOnes) > 0) {
                $args['post__not_in'] = $syncedOnes;
            }
        }


        /**
         * DO NOT EDIT THIS PARAMATER
         * 
         * WE DID NOT TESTED WITH "Grouped product" , "External/Affiliate product" , "Variable product"
         */
        $args['tax_query'] = array(
            'relation' => ' AND ',
            array(
                'taxonomy' => 'product_type',
                'field' => 'slug',
                'terms' => array('simple'),
            )
        );



        if (isset($data['sync_status']) && $data['sync_status'] != '') {
            $sync_status = $data['sync_status'];
            $syncedOnes = array();
            if ($sync_status == 'synced') {
                $syncedOnes = array("-1");
                $rows = $this->getSyncHistory(array('date_created_min' => 1, 'disableLimit' => 1));
                foreach ($rows as $product_id => $sync_time) {
                    $syncedOnes[] = $product_id;
                }
            } else if ($sync_status == 'will_synced') {
                $syncedOnes = array("-1");
                $rows = $this->getSyncHistory(array('date_created' => 0, 'disableLimit' => 1));
                foreach ($rows as $product_id => $sync_time) {
                    $syncedOnes[] = $product_id;
                }
            } else if ($sync_status == 'will_not_synced') {
                $syncedOnes = array("-1");
                $rows = $this->getSyncHistory(array('date_created' => -1, 'disableLimit' => 1));
                foreach ($rows as $product_id => $sync_time) {
                    $syncedOnes[] = $product_id;
                }
            }
            if (count($syncedOnes) > 0) {
                $args['post__in'] = $syncedOnes;
            }
        }




        $loop = new WP_Query($args);

        $rows = array();

        while ($loop->have_posts()) : $loop->the_post();
            /**
             * @var WC_Product
             */
            global $product;

            $product_id = $product->get_id();

            $term_list = wp_get_post_terms($product_id, 'product_cat');
            $cats = array();
            if (is_array($term_list) && count($term_list) > 0) {
                foreach ($term_list as $item) {
                    $cats[$item->term_id] = stripslashes($item->name);
                }
            }
            $product->term_list = $cats;


            $term_list = wp_get_post_terms($product_id, 'product_tag');
            $cats = array();
            if (is_array($term_list) && count($term_list) > 0) {
                foreach ($term_list as $item) {
                    $cats[$item->term_id] = stripslashes($item->name);
                }
            }
            $product->product_tags = $cats;

            $product->product_attributes = $this->getProductsAttributes($product);

            $product->product_download_url = array();

            if ($product->is_downloadable()) {
                $output = array();
                // Loop through WC_Product_Download objects
                foreach ($product->get_downloads() as $key_download_id => $download) {

                    ## Using WC_Product_Download methods (since WooCommerce 3)

                    $download_name = $download->get_name(); // File label name
                    $download_link = $download->get_file(); // File Url
                    $download_id = $download->get_id(); // File Id (same as $key_download_id)
                    $download_type = $download->get_file_type(); // File type
                    $download_ext = $download->get_file_extension(); // File extension

                    ## Using array properties (backward compatibility with previous WooCommerce versions)

                    // $download_name = $download['name']; // File label name
                    // $download_link = $download['file']; // File Url
                    // $download_id   = $download['id']; // File Id (same as $key_download_id)

                    $output[$download_id] = $download_link;
                }
                $product->product_download_url = $output;
            }

            //product images started
            $images = array();
            //            $attachment_ids = $product->get_gallery_image_ids();
            //            lknvar_dump($product->get_id());
            //            lknvar_dump($attachment_ids);
            //            echo '<hr />';
            //            foreach ($attachment_ids as $attachment_id) {
            //                $images[] = wp_get_attachment_url($attachment_id);
            //
            //                // Display Image instead of URL
            //                $images[] = wp_get_attachment_image($attachment_id, 'full');
            //            }
            //            // $images[] = $product->get_image('full');
            $product->product_images = $this->getProductImages($product_id);
            //product images 

            $rows[] = $product;

        endwhile;

        wp_reset_query();

        return $rows;
    }

    /**
     * get product images
     */
    public function getProductImages($product_id)
    {
        $sql = "SELECT p.ID, CONCAT((SELECT option_value FROM wp_options o WHERE o.option_name = 'siteurl'), '/wp-content/uploads/', am.meta_value) AS image_url FROM wp_posts p LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id' LEFT JOIN wp_postmeta am ON am.post_id = pm.meta_value AND am.meta_key = '_wp_attached_file' WHERE p.post_type = 'product' AND p.ID = $product_id AND am.meta_value IS NOT NULL";

        $db = lknDb::getInstance();

        $db->query($sql);
        $db->setQuery();
        if ($db->getErrorMessage() != '') {
            $return = array();
        } else {
            $rows = $db->loadObjectList();
            $return = array();
            foreach ($rows as $row) {
                $return[] = $row->image_url;
            }
        }


        return $return;
    }




    public function getProductsCount($data = array()): int
    {

        $config = lknConfig::getInstance();
        $recordPerPage = $config->get('recordPerPage');

        $start = lknInputFilter::filterInput($_REQUEST, 'start', '', 'INT');

        if ($start == '' || $start == 0) {
            $start = 1;
        }

        $sayfadakiKayit = (int)$recordPerPage;
        $limitStart = ($start - 1) * $sayfadakiKayit;


        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => $limitStart,
            'posts_per_page' => lknConfig::getInstance()->get('recordPerPage'),
            'meta_query' => array()
        );

        if (isset($data['sku']) && $data['sku'] != '') {
            $args['meta_query'][] = array(
                'key' => '_sku',
                'value' => $data['sku']
            );
        }

        if (isset($data['product_id']) && $data['product_id'] != '') {
            $args['p'] = $data['product_id'];
        }

        if (isset($data['status']) && $data['status'] != '') {
            $args['status'] = $data['status'];
        }


        if (isset($data['post__not_in']) && is_array($data['post__not_in']) && count($data['post__not_in']) > 0) {
            $args['post__not_in'] = $data['post__not_in'];
        }

        if (isset($data['sync_status']) && $data['sync_status'] != '') {
            $sync_status = $data['sync_status'];
            $syncedOnes = array();
            if ($sync_status == 'synced') {
                $syncedOnes = array("-1");
                $rows = $this->getSyncHistory(array('date_created_min' => 1, 'disableLimit' => 1));
                foreach ($rows as $product_id => $sync_time) {
                    $syncedOnes[] = $product_id;
                }
            } else if ($sync_status == 'will_synced') {
                $syncedOnes = array("-1");
                $rows = $this->getSyncHistory(array('date_created' => 0, 'disableLimit' => 1));
                foreach ($rows as $product_id => $sync_time) {
                    $syncedOnes[] = $product_id;
                }
            } else if ($sync_status == 'will_not_synced') {
                $syncedOnes = array("-1");
                $rows = $this->getSyncHistory(array('date_created' => -1, 'disableLimit' => 1));
                foreach ($rows as $product_id => $sync_time) {
                    $syncedOnes[] = $product_id;
                }
            }
            if (count($syncedOnes) > 0) {
                $args['post__in'] = $syncedOnes;
            }
        }


        /**
         * DO NOT EDIT THIS PARAMATER
         * 
         * WE DID NOT TESTED WITH "Grouped product" , "External/Affiliate product" , "Variable product"
         */
        $args['tax_query'] = array(
            'relation' => ' AND ',
            array(
                'taxonomy' => 'product_type',
                'field' => 'slug',
                'terms' => array('simple'),
            )
        );


        $loop = new WP_Query($args);

        $count = $loop->found_posts;

        wp_reset_query();


        return $count;
    }

    public function getCurrency(): string
    {
        return get_woocommerce_currency_symbol();
    }

    public function getSyncedProductIDs(): array
    {
        $db = lknDb::getInstance();
        $where = array();


        $where[] = "date_created>0"; //which is already synced
        $where[] = "date_created<0"; //which will not be synced
        $where = count($where) > 0 ? " WHERE " . implode(' OR ', $where) : '';
        $sql = "SELECT product_id FROM #__simple_ecommerce_sync_history";
        $sql .= $where;



        $db->query($sql);
        $db->setQuery();
        if ($db->getErrorMessage() != '') {
            $return = array();
        } else {
            $rows = $db->loadObjectList();
            $return = array();
            foreach ($rows as $row) {
                $return[] = $row->product_id;
            }
        }

        return $return;
    }

    public function getSyncHistory($data = array()): array
    {
        $db = lknDb::getInstance();
        $where = array();

        if (isset($data['product_id_in']) && is_array($data['product_id_in']) && count($data['product_id_in']) > 0) {
            $where[] = "product_id IN(" . implode(',', $data['product_id_in']) . ")";
        }


        if (isset($data['date_created'])) {
            $where[] = "date_created=" . intval($data['date_created']);
        }


        if (isset($data['date_created_min'])) {
            $where[] = "date_created>=" . intval($data['date_created_min']);
        }

        $where = count($where) > 0 ? " WHERE " . implode(' OR ', $where) : '';
        $sql = "SELECT * FROM #__simple_ecommerce_sync_history";
        $sql .= $where;
        if (!isset($data['disableLimit'])) {
            $sql .= $db->getLimit();
        }

        $db->query($sql);
        $db->setQuery();
        if ($db->getErrorMessage() != '') {
            $return = array();
        } else {
            $rows = $db->loadObjectList();
            $return = array();
            foreach ($rows as $row) {
                $return[$row->product_id] = $row->date_created;
            }
        }

        return $return;
    }

    /**
     * @param array $data
     * @return int
     */
    public function getSyncHistoryCount($data = array())
    {
        $db = lknDb::getInstance();
        $where = array();

        if (isset($data['product_id_in']) && is_array($data['product_id_in']) && count($data['product_id_in']) > 0) {
            $where[] = "product_id IN(" . implode(',', $data['product_id_in']) . ")";
        }

        if (isset($data['date_created'])) {
            $where[] = "date_created=" . intval($data['date_created']);
        }


        if (isset($data['date_created_min'])) {
            $where[] = "date_created>=" . intval($data['date_created_min']);
        }

        $where = count($where) > 0 ? " WHERE " . implode(' OR ', $where) : '';
        $sql = "SELECT * FROM #__simple_ecommerce_sync_history";
        $sql .= $where;


        $db->query($sql);
        $db->setQuery();
        if ($db->getErrorMessage() != '') {
            $return = 0;
        } else {
            $return = $db->num_rows();
        }

        return $return;
    }


    /**
     * @param WC_Product $product
     * @return array
     */
    function getProductsAttributes(WC_Product $product): array
    {

        $wc_attr_objs = $product->get_attributes();
        $prod_attrs = array();


        foreach ($wc_attr_objs as $wc_attr => $wc_term_objs) {
            if (strpos($wc_attr, 'pa_') !== false) {
                $prod_attrs[$wc_attr] = [];
                $wc_terms = $wc_term_objs->get_terms();
                if (is_array($wc_terms)) {
                    foreach ($wc_terms as $wc_term) {
                        $prod_attrs[$wc_attr][] = $wc_term->slug;
                    }
                }
            } else {
                $wc_terms = $wc_term_objs->get_data()['options'];
                if (is_array($wc_terms)) {
                    foreach ($wc_terms as $wc_term) {
                        $prod_attrs[$wc_attr][] = $wc_term;
                    }
                }
            }
        }


        return $prod_attrs;
    }


    /**
     * get products to send ecommerce.simpletools.nl
     *
     * @param  mixed $data
     * @return array
     */
    public function getProducts2Send($data = array()): array
    {


        $products = $this->getProducts($data);
        $rows = array();

        foreach ($products as $product) {

            $data = $product->get_data();


            $product_id = stripslashes($product->get_id());

            $rows[$product_id]['product_status'] = $product->get_status();
            $rows[$product_id]['product_name'] = stripslashes($product->get_name());
            $rows[$product_id]['product_sku'] = stripslashes($product->get_sku());
            $rows[$product_id]['product_image'] = get_the_post_thumbnail_url($product_id);
            $rows[$product_id]['product_images'] = $product->product_images;

            $rows[$product_id]['product_stock_status'] = stripslashes($product->get_stock_status());
            $rows[$product_id]['product_stock_quantity'] = $product->get_stock_quantity();

            $rows[$product_id]['product_description'] = stripslashes($data['description']);
            $rows[$product_id]['product_short_description'] = stripslashes($data['short_description']);

            $rows[$product_id]['product_virtual'] = $data['virtual'];
            $rows[$product_id]['product_downloadable'] = $data['downloadable'];


            $rows[$product_id]['product_weight'] = $data['weight'];
            $rows[$product_id]['product_length'] = $data['length'];
            $rows[$product_id]['product_height'] = $data['height'];
            $rows[$product_id]['product_width'] = $data['width'];


            $rows[$product_id]['product_price'] = stripslashes(wc_get_price_including_tax($product));

            $rows[$product_id]['product_categories'] = $product->term_list;
            $rows[$product_id]['product_tags'] = implode(",", array_values($product->product_tags));


            $rows[$product_id]['product_attributes'] = $product->product_attributes;

            $rows[$product_id]['product_download_url'] = $product->product_download_url;


            $with_tax = $product->get_price_including_tax();
            $without_tax = $product->get_price_excluding_tax();
            $tax_amount = $with_tax - $without_tax;

            // and the percentage would be:
            $rows[$product_id]['product_tax_percent'] = ($tax_amount / $without_tax) * 100;
        }

        return $rows;
    }
}
