<?php

namespace symbols;

class Star extends Symbol {
	public static $XSMALL = array (5, 1 );
	public static $SMALL = array (8, 4 );
	public static $MEDIUM = array (12, 8 );
	public static $LARGE = array (15, 11 );
	public static $XLARGE = array (18, 12 );
	public static $XXLARGE = array (21, 17 );
	
	public static function SetPattern($symbolObj,$styleObj=null) {
            
		//$symbolObj->setPattern(array(1,20,1,20));
	}
	
}

?>