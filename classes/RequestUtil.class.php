<?php
use utils\PageUtil;
class RequestUtil {
	const PATH_PARAM = 'sl_path';
	const REDIRECT_PATH_PARAM = 'REDIRECT_sl_path';
	const DEVLOPER_PARAM = 'devleoper';
	const DO_PARAM = 'do';
	const CONTEXT_PARAM='context';
	
	
	public function __construct() {
	}
	
	public static function Required() {
		$args = func_get_args();
		$missing = array();
		foreach($args as $arg) {
			if(!self::HasParam($arg)) $missing[] = $arg;
		}
		
		if(count($missing)) {
			throw new Exception('Missing Required Parameters: '.implode(',',$missing));
		}
	}
	
	public static function GetSpecial() {
	    return self::GetParamSet(array(self::PATH_PARAM,self::REDIRECT_PATH_PARAM,self::DEVLOPER_PARAM,self::DO_PARAM,self::CONTEXT_PARAM));
	}
	
	public static function GetParamSet(array $params) {
		$paramSet = array();
		foreach($params as $key=>$param) {
			$param = (is_string($key)) ? $key : $param;
			$defaultValue = (is_string($key)) ? $param : $param;
		 	
			$paramSet[] = self::Get($param,$defaultValue);		 	
		}
	}
	
	
	public static function Get($parameter, $default=null) {
	    if(is_null($parameter)) {
	        throw new Exception('null parameter');
	    }
	    
		if (isset ( $_REQUEST[$parameter] )) {
			return $_REQUEST[$parameter];
		}
		return $default;
	
	}
	
	public static function GetJSONParam($parameter,$default=null,$asArray=false) {
		$json = self::Get($parameter,null);
		if(is_null($json)) return $default;
		return json_decode($json,$asArray);
	}
	
	public static function GetAndSave($parameter, $default=null, $prefix="") {
		$session = SimpleSession::Get();
		if(!is_null(RequestUtil::Get($parameter,null)) or !isset($session[$prefix.$parameter])){
			$session[$prefix.$parameter] = RequestUtil::Get($parameter, $default);
		}
		return $session[$prefix.$parameter];
		
	}
	
	public static function SetAndSave($parameter, $value, $prefix="") {
		RequestUtil::Set($parameter, $value);
		return RequestUtil::GetAndSave($parameter, $value, $prefix);
	}

	public static function Set($parameter, $value) {
		$_REQUEST [$parameter] = $value;
	}
	

	public static function GetList($parameter,$delim,$default=null) {
		if(isset($_REQUEST[$parameter])) {
			return explode($delim,$_REQUEST[$parameter]);
		}
		return $default;
	}
	
	public static function HasParam($parameter) {
		return isset($_REQUEST[$parameter]);
	}
	
	public static function Contains($parameter,$matchString) {
		$param = self::Get($parameter);
		if ($param === $matchString) return true;
		if(is_null($param)) return false;
		$param = strtolower($param);
		$matchString = strtolower($matchString);
		return (strpos($param,$matchString)!==false);
	}
	
	public static function Merge($data) {
		if(is_string($data)) {
			$data = parse_str($data,$_REQUEST);
			return;
		}
		foreach($data as $key=>$val) {
			$_REQUEST[$key] = $val;
		}
	}
	
	public static function UnsetItems( $keys) {
		foreach($keys as $key) {
			unset($_REQUEST[$key]);
		}
	}
	
	public static function GetURL() {
		$url='';
		foreach($_REQUEST as $param=>$val) {
			$val = is_array($val) ? implode(',',$val) : $val; 
			$url.=sprintf("&%s=%s",$param,$val);
		}
		return $url;
	}
	
	public static function SetEnv() {
		$path = isset($_SERVER[self::REDIRECT_PATH_PARAM]) ? $_SERVER[self::REDIRECT_PATH_PARAM] : null;
		
		if(!defined('IS_DEV_SANDBOX')) {
		    if(substr($path,0,2)=='/~') {
		        $path = implode('/',array_slice(explode('/',$path),3));
		        PageUtil::RedirectTo($path);
                //if($path=="") $path ='/';
		    }
		}
	
		$do = null;
		$context = null;

		
		#if(is_null($path)) return null;
		if(!is_null($path)) {
			if(strpos($path,'/')===0) $path= substr($path,1);
			$path = explode('/',$path);
		
			$pathCount = count($path);
			$context = array_shift($path);
			if($pathCount >= 2) {
				$pathSliced = array_slice($path,-1);
				if(array_pop($pathSliced) == '') {
					array_pop($path);
				}
				$do = implode('/',$path);
				$path = array();
			}
		}
		if(!is_null($do)) self::Set(self::DO_PARAM,$do);
		If(!is_null($context)) self::Set(self::CONTEXT_PARAM, $context);
		if(is_null($path)) { $path = array(); }	
		if(count($path)) {
			$last = array_pop($path);
			if($last!='') {
				array_push($path,$last);
			}
		}
		$path = is_array($path) ? implode('/',$path) : $path;
		RequestUtil::Set(self::PATH_PARAM,$path);
	}
}

?>
