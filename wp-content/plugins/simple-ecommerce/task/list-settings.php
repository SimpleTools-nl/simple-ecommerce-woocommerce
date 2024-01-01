<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');


$tmpl = lknTemplate::getInstance();

$list_url = "admin.php?page=simple-ecommerce-admin.php";
$simple_q = (string)lknInputFilter::filterInput($_GET, 'simple-q');
$tmpl->set('simple_q', "");
$params = array();
if ($simple_q != '') {
    $params["sku"] = $simple_q;
    $simple_q = urldecode($simple_q);
    $tmpl->set('simple_q', $simple_q);

    $list_url .= '&simple-q=' . urlencode($simple_q);
}

$simple_q_id = (string)lknInputFilter::filterInput($_GET, 'simple-q-id');
$tmpl->set('simple_q_id', "");
if ($simple_q_id != '') {
    $params["product_id"] = $simple_q_id;
    $simple_q_id = urldecode($simple_q_id);
    $tmpl->set('simple_q_id', $simple_q_id);

    $list_url .= '&simple-q-id=' . urlencode($simple_q_id);
}

$sync_status = (string)lknInputFilter::filterInput($_GET, 'sync_status');
$tmpl->set('sync_status', "");
if ($sync_status != '') {
    $params["sync_status"] = $sync_status;
    $sync_status = urldecode($sync_status);
    $tmpl->set('sync_status', $sync_status);

    $list_url .= '&sync_status=' . urlencode($sync_status);
}



$simple_ecommerce_last_full_sync = "";
$simpleWooCommerce = simpleToolsWooCommerce::getInstance();
$count = $simpleWooCommerce->getProductsCount($params);
$rows = $simpleWooCommerce->getProducts($params);
$product_ids = array();
foreach ($rows as $row) {
    $product_ids[] = $row->get_id();
}
$syncHistory = $simpleWooCommerce->getSyncHistory(array('product_id_in' => $product_ids, 'disableLimit' => 1));
$syncHistoryCount = $simpleWooCommerce->getSyncHistoryCount(array('date_created' => -1, 'date_created_min' => 1));
if ($syncHistoryCount == $count) {
    $simple_ecommerce_last_full_sync = "1";
}

$tmpl->set('list_url', $list_url);

$tmpl->set('rows', $rows);
$tmpl->set('count', $count);
$tmpl->set('syncHistory', $syncHistory);
$tmpl->set('paging', lknPaging::getPageLinks($list_url, $count));
$tmpl->set('currency_symbol', $simpleWooCommerce->getCurrency());

$simpleEcommercePlugin = simpleToolsEcommerce::getInstance();

$simpleEcommercePlugin->loadParams();
$simple_ecommerce_user_id = (isset($_REQUEST['simple_ecommerce_user_id']) ? $_REQUEST['simple_ecommerce_user_id'] : trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simple_ecommerce_user_id")))));
$simple_ecommerce_user_token = (isset($_REQUEST['simple_ecommerce_user_token']) ? $_REQUEST['simple_ecommerce_user_token'] : trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simple_ecommerce_user_token")))));


$tmpl->set('simple_ecommerce_user_id', $simple_ecommerce_user_id);
$tmpl->set('simple_ecommerce_user_token', $simple_ecommerce_user_token);
$tmpl->set('simple_ecommerce_last_full_sync', $simple_ecommerce_last_full_sync);
$tmpl->set('i', (lknInputFilter::filterInput($_REQUEST, 'start', 1) - 1) * lknConfig::getInstance()->get('recordPerPage') + 1);

$tmpl->set('simple_page_title','Plugin Settings');

echo $tmpl->fetch_view("list-settings");
