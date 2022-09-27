<?php

class GeomTypes {
	
	// the geometry types a layer can have
	const UNKNOWN = 0;
	const POINT = 1;
	const POLYGON = 2;
	const LINE = 3;
	const RASTER = 4;
	const WMS = 5;
	const COLLECTION=6;
	const RELATABLE=7;
	
	private static $vectortypes_to_mapserver = array (GeomTypes::POINT => MS_LAYER_POINT, GeomTypes::LINE => MS_LAYER_LINE, GeomTypes::POLYGON => MS_LAYER_POLYGON );
	private static $enum = null;
	private static $vectorEnum = null;
	

	
	public static function GetEnum($replace=false) {
		if((self::$enum !== null) and !$replace) return self::$enum;
		self::$enum = new Enum('unknown','point','polygon','line','raster','wms','collection','relatable');
		return self::$enum;
	}

	public static function ToMSType($vectorType) {
	    switch($vectorType) {
	        case self::POINT:
	           //return 'point';
                   return \MS_LAYER_POINT;
	        case self::LINE:
                    //return 'line';
	            return \MS_LAYER_LINE;
	        case self::POLYGON:
	            //return 'polygon';
                    return \MS_LAYER_POLYGON;
	    }
	}
	
	public static function IsValidType( $type) {
		$enum = self::GetEnum();
		$isItem = $enum->IsItem($type);
		return $isItem;
	}
	
	public static function IsVector($value) {
		$vEnum = self::GetVectorEnum();
		return isset($vEnum[$value]);
	}
	
	public static function IsExportable($value) {
		$enum  =self::GetEnum();
		if(!is_int($value)) $value = $enum[$value];
		return (self::IsVector($value) || self::IsRaster($value));		
	}
	
	public static function IsRaster($value) {
		$enum  =self::GetEnum();
		if(!is_int($value)) $value = $enum[$value];
		return ($value == self::RASTER);
	}
	
	public static function GetVectorEnum($replace=false) {
		if((self::$vectorEnum !== null) and !$replace) return self::$vectorEnum;
		self::$vectorEnum = new Enum(array());
		self::$vectorEnum->AddItem('point',self::POINT);
		self::$vectorEnum->AddItem('polygon',self::POLYGON);
		self::$vectorEnum->AddItem('line',self::LINE);
		return self::$vectorEnum;
	} 
	
	public static function GetMapserverType( $geomtype ) {
		$enum = self::GetVectorEnum();
		if( !is_int($geomtype)) {
			$geomtype = $enum[$geomtype];
		}
		if(!isset(self::$vectortypes_to_mapserver[$geomtype])) return null;
		return self::$vectortypes_to_mapserver[$geomtype];
		
	}
	
	public static function GetGeomType($typeId) {
	    $enum = self::GetEnum();
	    return $enum[$typeId];
	}
	

	
	
}

?>