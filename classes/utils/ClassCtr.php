<?php
namespace utils;


class ClassCtr {
    
    private static $ctr=-1;
    
    public static function GetNext() {
        self::$ctr +=1;
        return self::$ctr;
    }
    
}

?>