<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access'); ?>
<div class="wrap _simple_ecommerce  _simple_ecommerce_list">
    <?php require_once __DIR__ . LKN_DS . 'list.toolbar.php'; ?>
    <hr class="wp-header-end">

    <?php if ($simple_ecommerce_user_id == '' || $simple_ecommerce_user_id == '') {
        ?>
        <div class="wrap _simple_strong _simple_fs16">
            <h2>You will need to enter your credentials to use the plugin</h2>

            1. Visit https://ecommerce.simpletools.nl/<br/>

            2. Login or register to the service (Account creation is free)<br/>

            3. After you login visit https://ecommerce.simpletools.nl/user/api-keys.html for getting your
            information<br/>

        </div>
        <?php
    } ?>
</div>
<div class="wrap">
    <form method="post" id="save_account_form<?php echo md5(time() . rand(0, 10000)); ?>">
        <h2>SimpleTool.nl settings</h2>

        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <tbody>
            <tr>
                <td class="_simple_w20"><label for="simple_ecommerce_user_token">SimpleTools.nl User Token</label></td>
                <td><input style="width: 75%;" type="text" name="simple_ecommerce_user_token"
                           id="simple_ecommerce_user_token" value="<?php echo $simple_ecommerce_user_token; ?>"/>

                    <p>You can your api key and user id with visiting <a
                                href="https://ecommerce.simpletools.nl/user/api-keys.html" target="_blank">https://ecommerce.simpletools.nl/user/api-keys.html</a>
                    </p>
                </td>
            </tr>

            <tr>
                <td class="_simple_w20"><label for="simple_ecommerce_user_id">SimpleTools.nl User ID</label></td>
                <td><input style="width: 75%;" type="text" name="simple_ecommerce_user_id" id="simple_ecommerce_user_id"
                           value="<?php echo $simple_ecommerce_user_id; ?>"/>

                    <p>You can your api key and user id with visiting <a
                                href="https://ecommerce.simpletools.nl/user/api-keys.html" target="_blank">https://ecommerce.simpletools.nl/user/api-keys.html</a>
                    </p>
                </td>
            </tr>

            </tbody>
        </table>


        <p>You can read our privacy policy on <a target="_blank"
                                                 href="https://ecommerce.simpletools.nl/support/kb-article/6-privacy-policy.html">https://ecommerce.simpletools.nl/support/kb-article/6-privacy-policy.html</a>
        </p>
        <p class="submit"><input name="submit" class="button button-primary _simple_btn_post" value="Start Integration"
                                 type="button"
                                 data-href="<?php echo LKN_BASE_PATH; ?>/simple-ecommerce-api.php?action=save_settings">
        </p>
    </form>

</div>