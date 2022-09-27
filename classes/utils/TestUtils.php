<?php

namespace utils;

use auth\Context;
class TestUtils {
	
	public static function SimulateDMI($request) {
		$_SERVER['REQUEST_URI'] = BASEURL;		
		$_SERVER['HTTP_HOST'] = BASEURL;
		self::SimulateRequest($request);
		Context::Get();
		
	}
	
	public static function SimulateEmbedded() {
		
	}
	
	public static function SimulateWAPI($user,$pass) {
		
	}
	
	private static function SimulateRequest($request) {
		\RequestUtil::Merge($request);
	}
	
	
	
	
}

?>