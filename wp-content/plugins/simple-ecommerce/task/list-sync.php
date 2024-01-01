<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');


$tmpl = lknTemplate::getInstance();

$list_url = "admin.php?page=simple-ecommerce-admin.php";

$simple_q = lknInputFilter::filterInput($_GET, 'simple-q');
$tmpl->set('simple_q', "");
if ($simple_q != '') {
    $simple_q = urldecode($simple_q);
    $tmpl->set('simple_q', $simple_q);

    $list_url .= '&simple-q=' . urlencode($simple_q);
}

$simpleWooCommerce = simpleToolsWooCommerce::getInstance();
$count = $simpleWooCommerce->getProductsCount(array('sku' => $simple_q));
$rows = $simpleWooCommerce->getProducts(array('sku' => $simple_q));
$syncHistory = $simpleWooCommerce->getSyncHistory(array('date_created' => -1, 'date_created_min' => 1));
$syncHistoryCount = $simpleWooCommerce->getSyncHistoryCount(array('date_created' => -1, 'date_created_min' => 1));

$tmpl->set('rows', $rows);
$tmpl->set('count', $count);
$tmpl->set('syncHistory', $syncHistory);
$tmpl->set('syncHistoryCount', $syncHistoryCount);
$tmpl->set('paging', lknPaging::getPageLinks($list_url, $count));

$simpleEcommercePlugin = simpleToolsEcommerce::getInstance();

$simpleEcommercePlugin->loadParams();
$simple_ecommerce_user_id = ($_REQUEST['simple_ecommerce_user_id'] ?? trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simple_ecommerce_user_id")))));
$simple_ecommerce_user_token = ($_REQUEST['simple_ecommerce_user_token'] ?? trim(ltrim(rtrim($simpleEcommercePlugin->getPostParam("simple_ecommerce_user_token")))));

$tmpl->set('simple_ecommerce_user_id', $simple_ecommerce_user_id);
$tmpl->set('simple_ecommerce_user_token', $simple_ecommerce_user_token);

$tmpl->set('simple_page_title','Synchronization Status & Synchronization Tools');

echo $tmpl->fetch_view("list-sync");
