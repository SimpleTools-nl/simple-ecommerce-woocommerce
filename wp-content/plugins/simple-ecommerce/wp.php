<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

require_once LKN_ROOT . LKN_DS . 'helpers' . LKN_DS . 'woocommerce.php';


class simpleToolsEcommerce
{

    private $_db;
    private $_db_prefix_mask;

    private $_wp_actions;

    private $_post_params;


    public $_vars;


    function __construct()
    {
        $this->import();

        global $wpdb;

        $this->_db = &$wpdb;
        $this->_db_prefix_mask = "#__";

        require_once LKN_ROOT . LKN_DS . 'wp_actions.php';

        $this->_wp_actions = simpleToolsEcommerce_WP_Actions::getInstance();
        // $this->_wp_actions->addheader();
        add_action('admin_enqueue_scripts', array($this->_wp_actions, 'addheader'));


        $this->_vars = array();
    }

    /**
     * Summary of getDb
     * @return wpdb
     */
    public function getDb()
    {
        return $this->_db;
    }

    function get($var)
    {
        if (isset($this->$var)) {
            return $this->$var;
        } else {
            return null;
        }
    }


    /**
     *
     * @return simpleToolsEcommerce
     */
    public static function getInstance()
    {
        static $_instance;
        if (!isset($_instance)) {
            $_instance = new simpleToolsEcommerce();
        }

        return $_instance;
    }


    private function import()
    {

        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'phpinputfilter' . LKN_DS . 'phpinputfilter.inputfilter.php';

        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'library' . LKN_DS . 'registery.php';
        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'library' . LKN_DS . 'class.template.php';
        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'library' . LKN_DS . 'class.user.php';
        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'library' . LKN_DS . 'functions.php';
        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'library' . LKN_DS . 'class.db.php';
        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'library' . LKN_DS . 'class.config.php';
        require_once SIMPLETOOLS_LIBRARY . LKN_DS . 'library' . LKN_DS . 'class.paging.php';
    }

    function setUserData()
    {
        add_action('init', array($this->_wp_actions, 'setUserData'));
    }

    function addToAdminMenu()
    {
        add_action('admin_menu', array($this->_wp_actions, 'addToAdminMenu'));
    }


    function getAdminPage()
    {

        ob_clean();

        ob_start(); // Start output buffering
        $this->error();
        require_once LKN_ROOT . LKN_DS . 'simple-ecommerce-admin.php';

        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean(); // End buffering and discard


        return $contents;
    }


    function loadParams()
    {


        $row = $this->getSettings();

        if (count($row) > 0) {

            /**
             *
             * $tmpl->set('simple_ecommerce_api_key',$simple_ecommerce_api_key);
             * $tmpl->set('simple_ecommerce_api_user_id',$simple_ecommerce_api_user_id);
             *
             *
             * $tmpl->set('post_format',$post_format);
             */

            foreach ($row as $item) {
                $this->_post_params[$item->SettingsKey] = $item->SettingsValue;
            }
        } else {
            $this->_post_params = array();
        }
    }

    private function createTable()
    {
        $db = lknDb::getInstance();
        $db->query("CREATE TABLE IF NOT EXISTS `#__simple_ecommerce_settings` (
  `ID` tinyint(1) NOT NULL,
  `settings` mediumtext NOT NULL,
  `date_created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $db->setQuery();


        $db->query("ALTER TABLE `#__simple_ecommerce_settings` ADD UNIQUE KEY `ID` (`ID`)");
        $db->setQuery();


        $db->query("CREATE TABLE IF NOT EXISTS `#__simple_ecommerce_sync_history` (
        `product_id` int(11) UNSIGNED NOT NULL,
  `date_created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $db->setQuery();

        $db->query("ALTER TABLE `#__simple_ecommerce_sync_history` ADD UNIQUE KEY `product_id` (`product_id`)");
        $db->setQuery();


        return $this->getSettings();
    }


    function getSettings()
    {


        $db = lknDb::getInstance();
        $db->query("SELECT * FROM #__simple_ecommerce_settings");
        $db->setQuery();
        if ($db->getErrorMessage() != '') {
            return $this->createTable();
        } else {
            $row = $db->loadObjectList();
        }


        return $row;
    }


    function registerAccountsAction()
    {
        add_action('wp_ajax_simple_ecommerce_accounts', array($this->_wp_actions, 'simple_ecommerce_accounts'));
    }

    function getPostParam($param)
    {
        if (isset($this->_post_params[$param])) {
            return $this->_post_params[$param];
        }

        return '';
    }

    function addMetaBox()
    {
        add_action("add_meta_boxes", array($this->_wp_actions, 'addMetaBox'));
    }


    function actionPostUpdated()
    {
        add_action('save_post', array($this->_wp_actions, 'post_updated'), 10, 3);
        add_action('post_updated', array($this->_wp_actions, 'post_updated'), 10, 3);
    }

    function actionCategoryUpdated()
    {
        add_action("edited_category", array($this->_wp_actions, 'post_updated'), 10, 3);
    }

    function error()
    {

        $error = lknStripSlash(lknInputFilter::filterInput($_REQUEST, 'simple_ecommerce_error'));
        $msg = lknStripSlash(lknInputFilter::filterInput($_REQUEST, 'simple_ecommerce_msg'));

        if ($error != '' || $msg != '') {
?>
            <?php if ($error != '') { ?>
                <div id="simple_ecommerce_error-message">
                    <div class="error settings-error notice">
                        <p><strong><?php echo $error; ?></strong></p>
                    </div>

                </div>
            <?php
            } elseif ($msg != '') {

            ?>
                <div id="simple_ecommerce_info-message">
                    <div class="updated settings-error notice">
                        <p><strong><?php echo $msg; ?></strong></p>
                    </div>

                </div>

<?php
            }
        }
    }
}

?>