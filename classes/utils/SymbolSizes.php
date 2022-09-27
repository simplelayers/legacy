<?php

namespace utils;

class SymbolSizes {
	const DEFAULT_XSMALL_WIDTH = 5;
	const DEFAULT_XSMALL_HEIGHT = 3;
	
	const SYMBOLSIZE_XSMALL =  1;
	const SYMBOLSIZE_SMALL  =  2;
	const SYMBOLSIZE_MEDIUM =  3;
	const SYMBOLSIZE_LARGE  =  4;
	const SYMBOLSIZE_XLARGE =  5;
	const SYMBOLSIZE_XXLARGE = 6;

	private static $enum = null;
	
	public static function GetEnum($replace=false) {
		if( (self::$enum !==NULL) and !$replace ) return self::$enum;
		self::$enum = new \Enum('default', 'xsmall','small','medium','large','xlarge','xxlarge');
		self::$enum->AddItem('default','large');
		return self::$enum;
	}	
	

}

?>