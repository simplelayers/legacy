<?php

namespace symbols;
class Point extends Symbol {

	
	private static $enum = null;
	
	public static function GetEnum($replace=false) {
		if( (self::$enum !==NULL) and !$replace ) return self::$enum;
		self::$enum = new \Enum(array());
		self::$enum->AddItem('circle','default');
		self::$enum->AddItem('diamond','diamond');
		self::$enum->AddItem('house','house');
		self::$enum->AddItem('square','square');
		self::$enum->AddItem('star','star');
		self::$enum->AddItem('tent','tent');
		self::$enum->AddItem('triangle','triangle');
		self::$enum->AddItem('circlex','circlex_fnt');
		self::$enum->AddItem('x-thin','cross_fnt');
		self::$enum->AddItem('x-heavy','x_fnt');
		self::$enum->AddItem('hospital cross','hospital_fnt');
		
		return self::$enum;
	}	

}

?>