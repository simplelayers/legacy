<?php

class DataTypes extends custom_types\CustomTypeFactory {

    // the defines for what datatypes exist in the database
    const TEXT = 'text';
    const INTEGER = 'int';
    const FLOAT = 'float';
    const BOOLEAN = 'boolean';
    const DATE = 'date';

    private static $types = null;
    private static $aliases = [
        'integer' => 'integer',
        'int' => 'integer',
        self::TEXT => self::TEXT,
        'string'=>self::TEXT,
        self::FLOAT => self::FLOAT,
        self::BOOLEAN => self::BOOLEAN,
        self::DATE => self::DATE
        
    ];

    public static function GetTypes($replace = false) {
        if (!is_null(self::$types) and!$replace)
            return self::$types;

        self::$types = array(self::TEXT, self::INTEGER, self::FLOAT, self::BOOLEAN, self::DATE);
        self::$types = array_merge(self::$types, custom_types\CustomTypeFactory::GetTypes());
        return self::$types;
    }
    public static function GetAliases() {
        return self::$aliases;
    }
    public static function IsValidType($type) {
        $types = self::GetTypes();
        return in_array($type, $types);
    }

}

?>