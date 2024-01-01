<?php

defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

class lknUser{
	
	private $name=null;
	private $username=null;
	private $id=null;
	private $email=null;
	private $usertype=null;
	private $registerDate=null;
	private $lastvisitDate=null;
	private $gid=null;
	private $my=null;
	
	function __construct(){
	
	
	}
	
	function set($name,$value){
		$this->$name=$value;
	}
	
	/**
	 *
	 * @return lknUser
	 */
	public static function getInstance(){
		static $_instance;
		if(!isset($_instance)){
			$_instance=new lknUser();
		}
		
		return $_instance;
		
	}
	
	
	/**
	 * kullanıcının gerçek adını dönderir
	 *
	 * @return string
	 */
	function getName(){
		
		return $this->name;
	}
	
	/**
	 * en son ziyaret tarihi
	 *
	 * @return date
	 */
	function getLastVisitDate(){
		
		return $this->lastvisitDate;
	}
	
	/**
	 * kayıt tarihi
	 *
	 * @return date
	 */
	function getregisterDate(){
		
		return $this->registerDate;
	}
	
	/**
	 * kullanıcı adı
	 *
	 * @return string
	 */
	function getUserName(){
		
		return $this->username;
	}
	
	/**
	 * kullanıcı id'si
	 *
	 * @return integer
	 */
	function getUserID(){
		
		return $this->id;
	}
	
	/**
	 * e-mail adresi
	 *
	 * @return string
	 */
	function getEmail(){
		
		return $this->email;
	}
	
	/**
	 * kullanıcı türü
	 *
	 * @return string
	 */
	function getUserType(){
		$gids=implode(',',$this->my->roles);
		
		return $gids;
		
		
	}
	
	function isAdministrator(){
		
		if(in_array('administrator',$this->my->roles)){
			return '1';
		}else{
			return '0';
		}
	}
	
}


?>