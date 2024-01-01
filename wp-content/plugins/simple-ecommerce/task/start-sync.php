<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');


$product_id = lknInputFilter::filterInput($_REQUEST, 'product_id');
$return_to = base64_decode(lknInputFilter::filterInput($_REQUEST, 'return_to'));

$sql = "REPLACE INTO `#__simple_ecommerce_sync_history` (`product_id`, `date_created`) VALUES ($product_id, 0)";
$db = lknDb::getInstance();
$db->query($sql);
$db->setQuery();

$simpleEcommercePlugin = simpleToolsEcommerce::getInstance();
$simpleEcommercePlugin->getSettings();


?>
<script>
    setTimeout(function() {
        window.location.href = '<?php echo $return_to; ?>'
    }, 1500)
</script>