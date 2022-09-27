<?php
class Units {
	
	const BYTE		= 1;
	const KILOBYTE  = 1024;
	const MEGABYTE  = 1048576;
	const GIGABYTE  = 1073741824;
	const TERABYTE  = 1099511627776;
	
	
	const BYTE_STR_ENG = 'B';
	const KILOBYTE_STR_ENG = 'KiB';
	const MEGABYTE_STR_ENG = 'MiB';
	const GIGABYTE_STR_ENG = 'GiB';
	const TERABYTE_STR_ENG = 'TiB';
	
	const BYTE_STR_FULL = 'bytes';
	const KILOBYTE_STR_FULL = 'kilobytes';
	const MEGABYTE_STR_FULL = 'megabytes';
	const GIGABYTE_STR_FULL = 'gigabytes';
	const TERABYTE_STR_FULL = 'terabytes';
	
	const STRINGMODE_NONE = null;
	const STRINGMODE_ENG = 1;
	const STRINGMODE_FULL = 2;
	
	
	public static function ValToUnits($intVal , $unit, $stringMode=self::STRINGMODE_NONE) {
		
		$val = round(1.0 * $intVal/$unit, 2);
		
		if(is_null($stringMode) ) return $val;
		
		switch($unit) {
			case self::BYTE:
				$suffix = ($stringMode == self::STRINGMODE_ENG) ? self::BYTE_STR_ENG : self::BYTE_STR_FULL;
				break;
			case self:: KILOBYTE:
				$suffix =  ($stringMode == self::STRINGMODE_ENG) ? self::KILOBYTE_STR_ENG : self::KILOBYTE_STR_FULL;
				break;
			case self::MEGABYTE:
				$suffix =  ($stringMode == self::STRINGMODE_ENG) ? self::MEGABYTE_STR_ENG : self::MEGABYTE_STR_FULL;
				break;
			case self::GIGABYTE:
				$suffix =  ($stringMode == self::STRINGMODE_ENG) ? self::GIGABYTE_STR_ENG : self::GIGABYTE_STR_FULL;
				break;
			case self::TERABYTE:
				$suffix =  ($stringMode == self::STRINGMODE_ENG) ? self::TERABYTE_STR_ENG : self::TERABYTE_STR_FULL;
				break;
		}		
		$out = $val.' '.$suffix;
		if( $val < 0 ) $out= '0'.$out;
		return $out;
	}
	
	public static function ValToEng($intVal,$stringMode=self::STRINGMODE_ENG) {
		if($intVal < 1000) return self::ValToUnits($intVal,self::BYTE,$stringMode);
		
		$value = self::ValToUnits($intVal,self::KILOBYTE);
		
		if($value< 1000) return self::ValToUnits($intVal, self::KILOBYTE,$stringMode);
		
		$value = self::ValToUnits($intVal,self::MEGABYTE);
		if($value< 1000) return self::ValToUnits($intVal, self::MEGABYTE, $stringMode);
		
		$value = self::ValToUnits($intVal,self::GIGABYTE);
		if($value < 1000) return self::ValToUnits($intVal, self::GIGABYTE,$stringMode);
		
		$value = self::ValToUnits($intVal,self::TERABYTE);
		return self::ValToUnits($intVal, self::TERABYTE,$stringMode);
		
	}
	public static function bytesToString($bytes, $pow=false, $precision = 2) { //$pow 0=B 1=KB 2=MB...
		$units = array(self::BYTE_STR_ENG, self::KILOBYTE_STR_ENG, self::MEGABYTE_STR_ENG, self::GIGABYTE_STR_ENG, self::TERABYTE_STR_ENG, 'PiB', 'EiB');
		$bytes = max($bytes, 0);
		if(!$pow) $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$numUnits = count($units);
		if($pow < 0) { $pow = 0; }
		$bytes /= (1 << (10 * $pow));
	
		return round($bytes, $precision) . ' ' . $units[$pow];
	}

}

?>
