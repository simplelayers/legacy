<?php

class ProblemTypes {
	const PROBLEM_OBJ_SYSTEM = 'System';
	const PROBLEM_OBJ_MAP = 'Map';
	const PROBLEM_OBJ_LAYER = 'Layer';
	const PROBLEM_OBJ_VIEWER = 'Viewer';
	const PROBLEM_OBJ_VIEWER_EMBEDDED='Embedded Viewer';	
	
	private static $enum = null;
	
	public static function GetEnum($replace=false) {
		if((self::$enum !== null) and !$replace) return self::$enum;
		self::$enum = new FlagEnum(self::PROBLEM_OBJ_MAP,self::PROBLEM_OBJ_LAYER,self::PROBLEM_OBJ_VIEWER,self::PROBLEM_OBJ_VIEWER_EMEBEDDED);
		self::$enum->AddItem(self::PROBLEM_OBJ_SYSTEM,0);
		return self::$enum;
	}
	
}



?>