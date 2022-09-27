<?php
namespace enums;

class AccountTypes {
	
	const GPS = 0;
	const BRONZE = 1;
	const SILVER = 2;
	const GOLD = 3;
	const PLATINUM = 4;
	const MAX = self::PLATINUM;
	const MIN = self::GPS;
	
	private static $enum = null;
	
	public static function GetEnum($replace=false) {
		if((self::$enum !== null) and !$replace) return self::$enum;
		self::$enum = new \Enum('GPS','Bronze','Silver','Gold','Platinum');		
		return self::$enum;
	}
	
}

?>