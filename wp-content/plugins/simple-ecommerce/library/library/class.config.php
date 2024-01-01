<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

class lknConfig
{

    private $_vars;


    function __construct()
    {

        $this->load();

    }


    function load()
    {

        /**
         * paging result interface. Listing page
         */
        $this->_vars['recordPerPage'] = 20;

        /**
         * Batch count for API
         */
        $this->_vars['recordPerPageAPI'] = 5;


        $this->_vars['timeout']=20;//timeout for api requests
        $this->_vars['api_save_site'] = 'https://ecommerce.simpletools.nl/api/ecommerce/save_account.html';
        $this->_vars['api_save_product'] = 'https://ecommerce.simpletools.nl/api/ecommerce/save_product.html';
        $this->_vars['api_get_pending'] = 'https://ecommerce.simpletools.nl/api/ecommerce/pending_changes.html';
        $this->_vars['api_get_pending_result'] = 'https://ecommerce.simpletools.nl/api/ecommerce/pending_changes_result.html';
    }

    function reload()
    {
        $this->load();
    }

    /**
     * @return lknConfig
     */

    public static function getInstance()
    {
        static $_instance;
        if (!isset($_instance)) {
            $_instance = new lknConfig();
        }

        return $_instance;
    }

    // Prevent users to clone the instance
    public final function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
        exit('No Clone');
    }


    /**
     * class içerisindeki herhangi bir değeri dönderirir
     *
     * @param string $var
     * @param string $clean_slash
     * @param string/null/empty $default
     * @return string
     */
    function get($var, $clean_slash = '1', $default = '')
    {
        if (isset($this->_vars[$var])) {
            if ($clean_slash == '1') {
                return stripslashes($this->_vars[$var]);
            } else {
                return $this->_vars[$var];
            }
        } else {
            return $default;
        }
    }


    /**
     * class içerisindeki herhangi bir değeri dönderirir
     *
     * @param string $var
     * @param string $clean_slash
     * @param string/null/empty $default
     * @return string
     */
    function filtervar($var, $clean_slash = '1', $default = '')
    {

        $return = array();
        foreach ($var as $vv) {
            foreach ($this->_vars as $k => $v) {
                if (substr($k, 0, strlen($vv)) == $vv) {
                    $return[$k] = $v;
                }
            }
        }


        return $return;
    }

    /**
     * overwrite a configuration variable or creates a run time configuration variable
     *
     * @param string $variable
     * @param string $value
     */
    function set($variable, $value)
    {
        $this->_vars[$variable] = $value;
    }

}

?>