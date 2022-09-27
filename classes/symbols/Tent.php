<?php

namespace symbols;

class Tent extends Symbol {

    public static $XSMALL = array(6, 4);
    public static $SMALL = array(8, 6);
    public static $MEDIUM = array(11, 7);
    public static $LARGE = array(13, 9);
    public static $XLARGE = array(16, 12);
    public static $XXLARGE = array(19, 15);

    public static function SetPattern($ms_symbolObj) {
        return;
    }

}

?>