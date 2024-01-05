<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

class lknTemplate
{

	private $vars; // Holds all the template variables
	private $is_admin;

	private $_filename;

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
	}


	/**
	 * @return lknTemplate
	 */

	public static function getInstance()
	{
		static $_instance;
		if (!isset($_instance)) {
			$_instance = new lknTemplate();
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
	 * Set a template variable.
	 */
	function set($name, $value)
	{
		$this->vars[$name] = $value;
	}


	function setMasterTemlateFile($name)
	{
		$this->_filename = $name;
	}

	/**
	 * returns a template variable. if $name does not exist, returns null
	 *
	 * @param $name
	 *
	 * @return null
	 */

	function get($name)
	{

		return isset($this->vars[$name]) ? $this->vars[$name] : null;
	}



	function fetch_view($filename)
	{


		$file = _SIMPLE_ROOT . _SIMPLE_DS . 'views' . _SIMPLE_DS . $filename . '.php';


		if (!file_exists($file)) {
			return "View is not found";
		}

		if ($this->vars) {
			extract($this->vars, EXTR_REFS); // Extract the vars to local namespace
		}

		ob_start(); // Start output buffering
		simpleToolsEcommerce::getInstance()->error();
		require($file); // Include the file
		$contents = ob_get_contents(); // Get the contents of the buffer
		ob_end_clean(); // End buffering and discard

		return $contents; // Return the contents
	}
}
