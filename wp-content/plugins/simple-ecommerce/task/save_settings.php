<?php defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

foreach ($_REQUEST as $k => $v) {
    $_REQUEST[$k] = trim(ltrim(rtrim((string)$v)));
}

if (!current_user_can("administrator")) {
    $return = array();
    $return['status'] = 0;
    $return['msg'] =  'This settings can be changed by Wordpress admins. Please login with your admin account and edit your simpleToolsEcommerce settings';
    echo json_encode($return);
    exit();
}


$simpletools_api_key = (string)lknInputFilter::filterInput($_REQUEST, "simple_ecommerce_user_token");
if ($simpletools_api_key == '') {
    $return = array();
    $return['status'] = 0;
    $return['msg'] =  'Your api key is missing! Plugin needs you to enter your API key to work. It\'s free. You can get it from https://ecommerce.simpletools.nl/user/api-keys.html';
    echo json_encode($return);
    exit();
}

$simpletools_api_user_id = (string)lknInputFilter::filterInput($_REQUEST, "simple_ecommerce_user_id");
if ($simpletools_api_user_id == '') {
    $return = array();
    $return['status'] = 0;
    $return['msg'] =  'Your api user id is missing! Plugin needs you to enter your simpleToolsEcommerce user id to work. It\'s free. You can get it from https://ecommerce.simpletools.nl/user/api-keys.html';
    echo json_encode($return);
    exit();
}


require_once _SIMPLE_ROOT . _SIMPLE_DS . 'library' . _SIMPLE_DS . 'guzzle' . _SIMPLE_DS . 'vendor' . _SIMPLE_DS . 'autoload.php';
$client = new \GuzzleHttp\Client();
$response = $client->request('POST', lknConfig::getInstance()->get('api_save_site'), [
    'form_params' => [
        'user_id' => $simpletools_api_user_id,
        'token' => $simpletools_api_key,
        'site_url' => get_site_url(),
        'site_name' => get_bloginfo('name'),
        'account_type_id' => 1 //wooccomemrce
    ],
    'timeout' => 10
]);


$responseText = $response->getBody()->getContents();
$responseTextJson = json_decode($responseText, true);

if (!isset($responseTextJson['status']) || $responseTextJson['status'] != 1) {
    $return = array();
    $return['status'] = 0;
    $return['msg'] =  $responseTextJson['msg'];
    echo json_encode($return);
    exit();
}


if (!isset($responseTextJson['account_id'])) {
    $return = array();
    $return['status'] = 0;
    $return['msg'] =  "account_id does not exist";
    echo json_encode($return);
    exit();
}


$sql = array();
$sql['SettingsKey'] = 'simple_ecommerce_user_token';
$sql['SettingsValue'] = $simpletools_api_key;
$db = lknDb::getInstance();
$db->query($db->CreateInsertSql($sql, "#__simple_ecommerce_settings", 'REPLACE'));
$db->setQuery();


$sql = array();
$sql['SettingsKey'] = 'simple_ecommerce_user_id';
$sql['SettingsValue'] = $simpletools_api_user_id;
$db = lknDb::getInstance();
$db->query($db->CreateInsertSql($sql, "#__simple_ecommerce_settings", 'REPLACE'));
$db->setQuery();


$sql = array();
$sql['SettingsKey'] = 'simple_ecommerce_account_id';
$sql['SettingsValue'] = $responseTextJson['account_id'];
$db = lknDb::getInstance();
$db->query($db->CreateInsertSql($sql, "#__simple_ecommerce_settings", 'REPLACE'));
$db->setQuery();


$simpleEcommercePlugin = simpleToolsEcommerce::getInstance();
$simpleEcommercePlugin->getSettings();


$return = array();
$return['status'] = 1;
$return['msg'] = 'You have sucessfully connected your Woocommerce with your ecommerce.simpletools.nl account';
echo json_encode($return);
exit();
