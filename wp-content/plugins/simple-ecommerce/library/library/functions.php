<?php


defined('_SIMPLE_ECOMMERCE_PLUGIN') or die('Restricted access');

/**
 *
 * @param string veya array $value
 *
 * @return string veya array
 */
function lknStripSlash($value){
	
	
	if(is_string($value)){
		$return=stripslashes($value);
	}else{
		if(is_array($value)){
			$return=array();
			foreach(array_keys($value) as $k){
				$return[$k]=lknStripSlash($value[$k]);
			}
		}else{
			$return=$value;
		}
	}
	
	return $return;
}

/**
 * Utility function redirect the browser location to another url
 *
 * Can optionally provide a message.
 *
 * @param string The file system path
 * @param string A filter for the names
 */
function lknredirect($url,$msg='',$error=''){
	
	
	// specific filters
	$iFilter=lknInputFilter::getInstance();
	$url    =$iFilter->_clean($url);
	$search =array('.',',','\\');
	if(!empty($msg)){
		$msg=$iFilter->_clean($msg);
		$msg=str_replace($search,'',$msg);
	}
	if(!empty($warn)){
		$warn=$iFilter->_clean($error);
		$warn=str_replace($search,'',$warn);
	}
	if(!empty($error)){
		$error=$iFilter->_clean($error);
		$error=str_replace($search,'',$error);
	}
	// Strip out any line breaks and throw away the rest
	$url=preg_split("/[\r\n]/",$url);
	$url=$url [0];
	if(trim($msg)){
		if(strpos($url,'?')){
			$url.='&simpletools_msg='.urlencode($msg);
		}else{
			$url.='?simpletools_msg='.urlencode($msg);
		}
	}

	if(trim($error)){
		if(strpos($url,'?')){
			$url.='&simpletools_error='.urlencode($error);
		}else{
			$url.='?simpletools_error='.urlencode($error);
		}
	}
	
	
	//$url = lknSef::url($url);
	if(headers_sent()){
		echo "<script>document.location.href='$url';</script>\n";
	}else{
		@ob_end_clean(); // clear output buffer
		header('HTTP/1.1 301 Moved Permanently');
		header("Location: ".$url);
	}
	exit ();
}


function lknvar_dump($array,$exit='0'){
	echo '<pre>';
	var_dump($array);
	echo '</pre>';
	
	if($exit=='1'){
		exit();
	}
}

?>