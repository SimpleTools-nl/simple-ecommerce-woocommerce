<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access'); ?>

<div class="wrap _simple_ecommerce  _simple_ecommerce_list_full_synchronization">

    <?php require_once __DIR__ . _SIMPLE_DS . 'list.toolbar.php'; ?>

    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
            <tr>
                <th>Total Published Product Count</th>
                <th>Total Synced Product Count</th>
            </tr>
        </thead>

        <tbody>
            <tr class="author-self level-0 type-product has-post-thumbnail">
                <td><?php echo $count; ?></td>
                <td><?php echo $syncHistoryCount; ?></td>
            </tr>
        </tbody>
    </table>


    <hr class="wp-header-end">

    <table class="wp-list-table widefat fixed striped table-view-list posts _simple-mb-15 _simple-mt-15">
        <thead>
            <tr>
                <th class="_simple_w30">Tool Name</th>
                <th>Tool Description</th>
            </tr>
        </thead>

        <tbody>
            <tr class="author-self level-0 type-product has-post-thumbnail">
                <td class="_simple_w30"><button type="button" data-url="<?php echo _SIMPLE_BASE_PATH; ?>/simple-ecommerce-api.php?action=do_sync" class="_simple_ecommerce_list_full_synchronization_start page-title-action">Do Pending Synchronization</button></td>
                <td>
                    You can start a synchronization for the pending product without waiting system to work
                </td>
            </tr>

            <tr class="author-self level-0 type-product has-post-thumbnail">
                <td class="_simple_w30"><button data-href="<?php echo _SIMPLE_BASE_PATH; ?>/simple-ecommerce-api.php?action=reset_all" class="page-title-action _simple_btn_get">Reset All Sync Status</button></td>
                <td>
                    Reset the synchronization status and start over again
                </td>
            </tr>



        </tbody>
    </table>


    <div class="_simple-messages _simple-mt-15">
        <ul>

        </ul>
    </div>

</div>