<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access'); ?>

<?php if ($simple_ecommerce_user_id == '' && $simple_ecommerce_user_id == '') {
?>
    <div class="wrap _simple_strong _simple_fs16 _simple_red">
        <h2 class="_simple_red">You will need to enter your credentials to use the plugin</h2>

        1. Visit https://ecommerce.simpletools.nl/<br />

        2. Login or register to the service (Account creation is free)<br />

        3. After you login visit https://ecommerce.simpletools.nl/user/api-keys.html for getting your information<br />

    </div>
<?php
}
?>
<div class="wrap _simple_ecommerce  _simple_ecommerce_list">
    <?php require_once __DIR__ . _SIMPLE_DS . 'list.toolbar.php'; ?>


    <hr class="wp-header-end">

    <?php if ($simple_ecommerce_last_full_sync == '') {
    ?>
        <h2 class="_simple-mb-15">You did not make any full sync with your ecommerce.simpletools.nl account. We
            highly suggest you to make full synchronization before using the plugin</h2>
    <?php
    } ?>

    <h2 class="screen-reader-text">Filter products</h2>
    <form action="admin.php" method="get">
        <input type="hidden" name="page" value="simple-ecommerce-admin.php">

        <div class="tablenav top">

            <div class="alignleft actions bulkactions">
                <input placeholder="Product SKU" type="search" id="simple-q" name="simple-q" value="<?php echo $simple_q; ?>">
            </div>


            <div class="alignleft actions bulkactions">
                <input placeholder="Product ID" type="search" id="simple-q-id" name="simple-q-id" value="<?php echo $simple_q_id; ?>">
            </div>


            <div class="alignleft actions">

                <select name="sync_status">
                    <option value="">--Select Sync Status---</option>
                    <option value="synced" <?php echo ($sync_status == 'synced' ? 'selected="selected"' : ''); ?>>Synchronised</option>
                    <option value="will_synced" <?php echo ($sync_status == 'will_synced' ? 'selected="selected"' : ''); ?>>Yet to be synchronised</option>
                    <option value="will_not_synced" <?php echo ($sync_status == 'will_not_synced' ? 'selected="selected"' : ''); ?>>Will not be synchronised</option>
                </select>

                <input type="submit" id="search-submit" class="button" value="Search products">
            </div>

            <br class="clear">
        </div>


        <h2 class="screen-reader-text">Products list</h2>
        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <thead>
                <tr>
                    <th class="_simple_w30px">#</th>
                    <th class="_simple_w70px">Image</th>
                    <th>Name</th>
                    <th class="_simple_w100px">SKU / Stock</th>
                    <th class="_simple_w70px">Price</th>
                    <th class="_simple_w20">Categories / Tags</th>
                    <th>Last Sync Date</th>

                </tr>
            </thead>

            <tbody>
                <?php if ($count > 0 && count($rows) > 0) {

                    foreach ($rows as $row) {
                        // lknvar_dump($row);
                        $product_id = stripslashes($row->get_id());
                        $product_name = stripslashes($row->get_name());
                        $product_sku = stripslashes($row->get_sku());
                        $product_images = $row->product_images;
                        if (count($product_images) > 0) {
                            $product_image = $product_images[0];
                        } else {
                            $product_image = get_the_post_thumbnail_url($product_id);
                        }

                        $product_stock_status = stripslashes($row->get_stock_status());
                        $product_stock_quantity = $row->get_stock_quantity();

                        $product_price = stripslashes($row->get_price());

                        $product_categories = implode(",", array_values($row->term_list));
                        $product_tags = implode(",", array_values($row->product_tags));

                        $product_attributes = $row->product_attributes;

                        $edit_url = _SIMPLE_WP_ADMIN_URL . 'post.php?post=' . $product_id . '&amp;action=edit';
                ?>
                        <tr id="post-<?php echo $product_id; ?> " class="author-self level-0 post-<?php echo $product_id; ?> type-product has-post-thumbnail">
                            <td class="_simple_w30px"><?php echo $i; ?></td>
                            <td class="_simple_w70px"><a href=" <?php echo $edit_url; ?>"><img style="width: 100%;height: auto;max-height: 150px;" src="<?php echo $product_image; ?>" class="attachment-thumbnail size-thumbnail" alt="" decoding="async" loading="lazy"></a></td>
                            <td><strong><a class="row-title" href="<?php echo $edit_url; ?>"><?php echo $product_name; ?></a></strong>
                                <hr />
                                <?php echo $product_id; ?>

                            </td>
                            <td class="_simple_w100px"><?php echo $product_sku; ?>
                                <hr />
                                <mark><?php echo $product_stock_status; ?> (<?php echo $product_stock_quantity ?>)
                                </mark>
                            </td>
                            <td class="_simple_w70px"><span class="woocommerce-Price-currencySymbol"><?php echo $currency_symbol; ?></span><?php echo $product_price; ?></span>
                            </td>
                            <td class="_simple_w20"><?php echo $product_categories; ?>
                                <hr /><?php echo $product_tags; ?>
                            </td>
                            <?php
                            /**
                             * <td><?php if (count($product_attributes) > 0) {
                             * foreach ($product_attributes as $product_attribute_name => $values) {
                             * ?>
                             * <h3><?php echo $product_attribute_name; ?></h3>
                             * <div>
                             * <ul>
                             * <?php foreach ($values as $value) {
                             * ?>
                             * <li><?php echo $value; ?></li>
                             * <?php
                             * } ?>
                             * </ul>
                             * </div>
                             * <?php
                             *
                             * }
                             * } else {
                             * ?>
                             * No attribute is used for this product
                             * <?php
                             * } ?>
                             * </td>
                             */
                            ?>
                            <td><?php
                                if (isset($syncHistory[$product_id])) {
                                    if ($syncHistory[$product_id] > 0) {

                                ?>
                                        <div class="_simple_ecommerce_sync_date_<?php echo $product_id; ?>"><?php echo date('d-m-Y H:i:s', $syncHistory[$product_id]); ?></div>
                                        <hr />
                                        <a target="_blank" href="https://ecommerce.simpletools.nl/ecommerce/catalog/list-products.html?&product_sku=<?php echo urlencode($product_sku) ?>">View
                                            this product on ecommerce.simpletools.nl</a>

                                        <hr />

                                        <a href="<?php echo _SIMPLE_WP_ADMIN_URL; ?>admin.php?page=simple-ecommerce-admin.php&task=stop-sync&product_id=<?php echo $product_id; ?>&return_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>">Never
                                            Sync This Product In Future</a>
                                        <hr />

                                        <a class="_simple_btn_get _simple_cursor" data-href="<?php echo _SIMPLE_BASE_PATH; ?>/simple-ecommerce-api.php?action=do_sync&product_id=<?php echo $product_id; ?>&return_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>">Sync
                                            This Product Now</a>


                                        <hr />
                                    <?php

                                    } else if ($syncHistory[$product_id] == "-1") { ?>
                                        <strong>This product will not be synced</strong>

                                        <hr />

                                        <a href="<?php echo _SIMPLE_WP_ADMIN_URL; ?>admin.php?page=simple-ecommerce-admin.php&task=start-sync&product_id=<?php echo $product_id; ?>&return_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>">Allow
                                            Sync For this product</a>
                                    <?php

                                    } else {
                                    ?>
                                        <hr />
                                        <a class="_simple_btn_get  _simple_cursor" data-href="<?php echo _SIMPLE_BASE_PATH; ?>/simple-ecommerce-api.php?action=do_sync&product_id=<?php echo $product_id; ?>&return_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>">Sync
                                            This Product Now</a>

                                        <hr />


                                        <div class="_simple_ecommerce_sync_date_<?php echo $product_id; ?>">Not synced
                                            yet
                                        </div>
                                        <hr>
                                        <a href="<?php echo _SIMPLE_WP_ADMIN_URL; ?>admin.php?page=simple-ecommerce-admin.php&task=stop-sync&product_id=<?php echo $product_id; ?>&return_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>">Never
                                            Sync This Product In Future</a>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <div class="_simple_ecommerce_sync_date_<?php echo $product_id; ?>">Not synced yet
                                    </div>
                                    <hr />

                                    <a class="_simple_btn_get  _simple_cursor" data-href="<?php echo _SIMPLE_BASE_PATH; ?>/simple-ecommerce-api.php?action=do_sync&product_id=<?php echo $product_id; ?>&return_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>">Sync
                                        This Product Now</a>

                                    <hr />

                                    <a href="<?php echo _SIMPLE_WP_ADMIN_URL; ?>admin.php?page=simple-ecommerce-admin.php&task=stop-sync&product_id=<?php echo $product_id; ?>&return_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>">Never
                                        Sync This Product In Future</a>
                                <?php
                                }


                                ?>
                            </td>
                        </tr>
                    <?php

                        $i++;
                    }
                } else {
                    ?>
                    <trclass="author-self level-0 type-product has-post-thumbnail">
                        <td colspan="8">There is no product</td>
                        </tr>
                    <?php
                } ?>
            </tbody>
        </table>

        <?php echo $paging; ?>


    </form>


</div>
<?php
