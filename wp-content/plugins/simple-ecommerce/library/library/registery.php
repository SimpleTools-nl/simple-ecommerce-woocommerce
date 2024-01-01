<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

class lknRegistery{
	
	private $vars;

	function __construct(){
	
	}
	
	
	function getAll(){
		return $this->vars;
	}
	
	
	/**
	 * @return lknRegistery
	 */
	
	public static function getInstance(){
		static $_instance;
		if(!isset($_instance)){
			$_instance=new lknRegistery();
		}
		
		return $_instance;
	}
	
	
	// Prevent users to clone the instance
	public final function __clone(){
		trigger_error('Clone is not allowed.',E_USER_ERROR);
		exit('No Clone');
	}
	
	/**
	 * Set a template variable.
	 */
	function set($name,$value){
		$this->vars[$name]=$value;
	}
	
	
	/**
	 * @param $name
	 *
	 * @return null
	 */
	
	function get($name){
		
		return isset($this->vars[$name])?$this->vars[$name]:null;
	}
}

?>