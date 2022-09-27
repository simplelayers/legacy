<?php

namespace utils;

class DOUtil {
	
	public static function Set($do) {
		\RequestUtil::Set('do',$do);
	}
	
	public static function IsDoSet() {
		return \RequestUtil::HasParam('do');
	}

	public static function Get($default=null) {
		$do= \RequestUtil::Get('do',$default);
		return $do;
	}
	
	public static function IsGet() {
		$do = self::Get();
		return $do=='get';
	}
	
	public static function Contains($matchString) {
		$do = self::Get();
		if(is_null($do)) return false;
		$do = strtolower($do);
		$matchString = strtolower($matchString);
		return (strpos($do,$matchString)!==false); 
	}
		
}

?>