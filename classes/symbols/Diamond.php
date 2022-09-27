<?php

namespace symbols;

class Diamond extends Symbol {
	public static $XSMALL = array (6, 4 );
	public static $SMALL = array (8, 6 );
	public static $MEDIUM = array (10, 8 );
	public static $LARGE = array (12, 10 );
	public static $XLARGE = array (14, 12 );
	public static $XXLARGE = array (16, 14 );
        public static function SetPattern($ms_symbolObj) {
            return;
	}
}

?>