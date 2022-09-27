<?php

class AccessLevels {

	const NONE = 0;
	const READ = 1;
	const COPY = 2;
	const EDIT = 3;	
	
	private static $enum = NULL;
	private static $projLabels = NULL;
	private static $layerLabels = NULL;
	
	public static function GetEnum($replace=false) {
	    if( !(self::$enum ==NULL) and !$replace ) return self::$enum;
		self::$enum = new Enum('none','view','copy','edit');
		
		return self::$enum;
	}

	public static function GetProjAccessLabels($replace=FALSE) {
		
		if( !is_null(self::$projLabels) and !$replace ) return self::$projLabels;
		
		self::$projLabels = array(
				self::NONE => 'NONE: This project is not shared and is not visible in listings',
				self::READ => 'VIEW: The entire community may view this project',
				self::EDIT => 'EDIT: The entire community may edit this project'
		);
		return self::$projLabels;
	}
	
	public static function GetLayerAccessLabels($replace=FALSE) {
		if( (self::$layerLabels !==NULL) and !$replace ) return self::$layerLabels;
		
		self::$layerLabels = array(
			self::NONE => 'NONE: This layer is not shared and is not visible in listings',
			self::READ => 'VIEW: The entire community may to use this layer in projects',
			self::COPY => 'COPY: The entire community may copy this layer',
			self::EDIT => 'EDIT: The entire community may edit this layer'
		);
		return self::$layerLabels;
	}
	
	public static function HasAccess($permission,$accesLevel) {
	    
	    return $permission == $accesLevel;
	}
	
	public static function GetLevel($levelId) {
	    $enum = self::GetEnum();
	    return $enum[$levelId];
	}
	
}