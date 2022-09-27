<?php
use formats\GPS;
use formats\Raster;
use formats\Shp;
use formats\WMS;
use formats\CSV;
error_reporting(E_ERROR | E_WARNING | E_PARSE);

class LayerFormats
{

    const FORMAT_RASTER = 'raster';

    const FORMAT_CSV = 'csv';

    const FORMAT_DELIMITED = 'delim';

    const FORMAT_GEN = 'gen';

    const FORMAT_GPS = 'gps';

    const FORMAT_ODBC = 'odbc';

    const FORMAT_SHP = 'shp';

    const FORMAT_WMS = 'wms';

    const FORAMT_XMV = 'xmv';

    public static $formats = array(
        'shp' => array(
            'label' => 'Shapefile Format',
            'class' => 'Shp.php',
            'classname' => 'formats\Shp'
        ),
        'raster' => array(
            'label' => 'Raster Format',
            'class' => 'Raster.php',
            'classname' => 'formats\Raster'
        ),
        'gps' => array(
            'label' => 'GPS Format',
            'class' => 'GPS.php',
            'classname' => 'formats\GPS'
        ),
        'wms' => array(
            'label' => 'WMS format',
            'class' => 'WMS.php',
            'classname' => 'formats\WMS'
        ),
        'csv' => array(
            'label' => 'CSV',
            'class' => 'CSV.php',
            'classname' => 'formats\CSV'
        )
    );

    public static function GetFormatPermissionLookup()
    {
        $permissions[self::FORMAT_RASTER] = ':Layers:Foramts:Raster:';
        $permissions[self::FORMAT_CSV] = ':Layers:Formats:CSV:';
        $permissions[self::FORMAT_DELIMITED] = ':Layers:Formats:Delimited:';
        $permissions[self::FORMAT_GEN] = ':Layers:Formats:GEN:';
        $permissions[self::FORMAT_GPS] = ':Layers:Formats:GPS:';
        $permissions[self::FORMAT_ODBC] = ':Layers:Formats:ODBC:';
        $permissions[self::FORMAT_SHP] = ':Layers:Formats:SHP:';
        $permissions[self::FORMAT_WMS] = ':Layers:Formats:WMS:';
        $permissions[self::FORAMT_XMV] = ':Layers:Formats:XMV:';
        return $permissions;
    }

    public static function GetFormatInstance($format)
    {
        if (! self::HasFormat($format))
            return null;
        switch ($format) {
            case self::FORMAT_GPS:
                return new GPS();
            case self::FORMAT_RASTER:
                return new Raster();
            case self::FORMAT_SHP:
                return new Shp();
            case self::FORMAT_WMS:
                return new WMS();
            case self::FORMAT_CSV:
                return new CSV();
        }
    }

    public static function HasFormat($format)
    {
        return array_key_exists($format, self::$formats);
    }
}
?>
