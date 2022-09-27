<?php

class LayerTypes {

    const NONE = 0;
    const VECTOR = 1;
    const RASTER = 2;
    const WMS = 3;
    const ODBC = 4;
    const RELATIONAL = 5;
    const COLLECTION = 6;
    const SMART_LAYER = 7;
    const RELATABLE = 8;

    private static $enum = NULL;
    private static $featureEnum = NULL;
    private static $tabularEnum = NULL;

    /**
     * 
     * @param boolean $replace 
     * @return Enum
     */
    public static function GetEnum($replace = false) {
        if ((self::$enum !== NULL) and!$replace)
            return self::$enum;
        self::$enum = new Enum('', 'vector', 'raster', 'wms', 'odbc', 'relational', 'collection', 'smart_layer', 'relatable');
        return self::$enum;
    }

    public static function IsValidType($type) {
        $enum = self::GetEnum();
        $isItem = $enum->IsItem($type);
        return $isItem;
    }

    private static function GetFeatureEnum($replace = false) {
        if ((self::$featureEnum !== NULL) and!$replace)
            return self::$featureEnum;
        self::$featureEnum = new Enum(array());
        self::$featureEnum->AddItem('vector', self::VECTOR);
        self::$featureEnum->AddItem('odbc', self::ODBC);
        self::$featureEnum->AddItem('relational', self::RELATIONAL);

        return self::$featureEnum;
    }

    public static function IsFeatureSource($layerType) {
        self::GetFeatureEnum();

        return isset(self::$featureEnum[$layerType]);
    }

    public static function GetTabularEnum($replace = false) {
        if ((self::$tabularEnum !== NULL) and!$replace) {
            return self::$tabularEnum;
        }
        self::$tabularEnum = new Enum(array());
        self::$tabularEnum->AddItem('vector', self::VECTOR);
        self::$tabularEnum->AddItem('odbc', self::ODBC);
        self::$tabularEnum->AddItem('relational', self::RELATIONAL);
        self::$tabularEnum->AddItem('relatable', self::RELATABLE);
        return self::$tabularEnum;
    }

    public static function IsTabular($typeId) {
        $enum = self::GetTabularEnum();
        return isset(self::$tabularEnum[$typeId]);
    }

    public static function GetGeomType($typeId) {
        $enum = self::GetEnum();
        return $enum[$typeId];
    }

    public static function IsRaster($type) {
        return $type == self::RASTER;
    }

   

}

?>