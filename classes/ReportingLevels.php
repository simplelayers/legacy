<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportingLevels
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class ReportingLevels {
	const NONE = 0;
	const VIEW = 1;
	const EXPORT = 2;
	const GEOEXPORT = 3;	
	
	private static $enum = NULL;
	private static $layerLabels = NULL;
	
	public static function GetEnum($replace=false) {
	    if( !(self::$enum ==NULL) and !$replace ) return self::$enum;
		self::$enum = new Enum('none','view','export','geoexport');		
		return self::$enum;
	}

	public static function GetLayerAccessLabels($replace=FALSE) {
		if( (self::$layerLabels !==NULL) and !$replace ) return self::$layerLabels;
		
		self::$layerLabels = array(
			self::NONE => 'None: This layer is not available for searches and reporting',
			self::READ => 'View: This laye may be used in report UIs',
			self::EXPORT => 'Export: Reports may export non spatial tabular information in this layer',
			self::GEOEXPORT => 'GeoExport: Interfaces may export tabular and spatial content from this layer.'
		);
		return self::$layerLabels;
	}
	
	public static function HasAccess($reporting_level,$level) {
	    return $reporting_level >= $level;
	}
	
	public static function GetLevel($levelId) {
	    $enum = self::GetEnum();
	    return $enum[$levelId];
	}
	

    //put your code here
}
