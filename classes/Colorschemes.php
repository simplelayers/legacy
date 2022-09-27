<?php
System::RequireColorschemes();

class Colorschemes {
	
	const MANUAL = 'manual';
	const SINGLE ='single';
	const UNIQUE = 'unique';
	const QUANTILE = 'quantile';
	const EQUALINTERVAL = 'equalinterval';
	
	const TYPE_SEQUENTIAL = 'sequential';
	const TYPE_QUALITATIVE = 'qualitative';
	const TYPE_DIVERGING = 'diverging';
	const TYPE_UNIQUE = 'unique';
	
	public static function GetColorScheme( $type ,$schemenumber,$schemename) {
		global $COLORSCHEMES;
		return $COLORSCHEMES[$type][$schemenumber][$schemename];		
	}
}

?>