<?php

class SymbolSize
{
    // definitions for the size of vector symbols
    const XSMALL = 1;

    const SMALL = 2;

    const MEDIUM = 3;

    const LARGE = 4;

    const XLARGE = 5;

    const XXLARGE = 6;

    private static $enum;

    private static $sizes;

    private static $outline_thickness;

    public static function GetEnum($replace = false)
    {
        if ((self::$enum !== null) and ! $replace)
            return self::$enum;
        self::$enum = new Enum('', 'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large');
        return self::$enum;
    }

    public static function GetSymbolSizes($symbolName, $size)
    {
        self::GetSizes();
        
        return self::$sizes[$symbolName][$size];
    }

    public static function GetOutlineThickness($size)
    {
        self::GetOutlineThicknesses();
        return self::$outline_thickness[$size];
    }
    public static function GetSrcSize($thickness) {
        $thicknesses = self::GetOutlineThicknesses();
        foreach($thicknesses as $size=>$val) {
            if($thickness === $val) {
                return $size;
            }
        }
    }

    private static function GetOutlineThicknesses()
    {
        if (! is_null(self::$outline_thickness))
            return self::$outline_thickness;
        $outline_thickness[self::XSMALL] = 1;
        $outline_thickness[self::SMALL] = 2;
        $outline_thickness[self::MEDIUM] = 3;
        $outline_thickness[self::LARGE] = 5;
        $outline_thickness[self::XLARGE] = 8;
        $outline_thickness[self::XXLARGE] = 10;
        self::$outline_thickness = $outline_thickness;
    }
    
    private static function GetSizes()
    {
        if (! is_null(self::$sizes))
            return self::$sizes;
        $symbol_sizes = array();
        $symbol_sizes['default'] = array();
        $symbol_sizes['default'][self::XSMALL] = array(
            5,
            3
        );
        $symbol_sizes['default'][self::SMALL] = array(
            7,
            5
        );
        $symbol_sizes['default'][self::MEDIUM] = array(
            9,
            7
        );
        $symbol_sizes['default'][self::LARGE] = array(
            13,
            11
        );
        $symbol_sizes['default'][self::XLARGE] = array(
            15,
            13
        );
        $symbol_sizes['default'][self::XXLARGE] = array(
            18,
            16
        );
        $symbol_sizes['star'] = array();
        $symbol_sizes['star'][self::XSMALL] = array(
            5,
            1
        );
        $symbol_sizes['star'][self::SMALL] = array(
            8,
            4
        );
        $symbol_sizes['star'][self::MEDIUM] = array(
            12,
            8
        );
        $symbol_sizes['star'][self::LARGE] = array(
            15,
            11
        );
        $symbol_sizes['star'][self::XLARGE] = array(
            18,
            12
        );
        $symbol_sizes['star'][self::XXLARGE] = array(
            21,
            17
        );
        $symbol_sizes['tent'] = array();
        $symbol_sizes['tent'][self::XSMALL] = array(
            6,
            4
        );
        $symbol_sizes['tent'][self::SMALL] = array(
            8,
            6
        );
        $symbol_sizes['tent'][self::MEDIUM] = array(
            11,
            7
        );
        $symbol_sizes['tent'][self::LARGE] = array(
            13,
            9
        );
        $symbol_sizes['tent'][self::XLARGE] = array(
            16,
            12
        );
        $symbol_sizes['tent'][self::XXLARGE] = array(
            19,
            15
        );
        $symbol_sizes['triangle'] = array();
        $symbol_sizes['triangle'][self::XSMALL] = array(
            6,
            4
        );
        $symbol_sizes['triangle'][self::SMALL] = array(
            8,
            6
        );
        $symbol_sizes['triangle'][self::MEDIUM] = array(
            11,
            7
        );
        $symbol_sizes['triangle'][self::LARGE] = array(
            13,
            9
        );
        $symbol_sizes['triangle'][self::XLARGE] = array(
            16,
            12
        );
        $symbol_sizes['triangle'][self::XXLARGE] = array(
            19,
            15
        );
        $symbol_sizes['square'] = array();
        $symbol_sizes['square'][self::XSMALL] = array(
            6,
            4
        );
        $symbol_sizes['square'][self::SMALL] = array(
            8,
            6
        );
        $symbol_sizes['square'][self::MEDIUM] = array(
            10,
            8
        );
        $symbol_sizes['square'][self::LARGE] = array(
            14,
            12
        );
        $symbol_sizes['square'][self::XLARGE] = array(
            16,
            14
        );
        $symbol_sizes['square'][self::XXLARGE] = array(
            18,
            16
        );
        $symbol_sizes['diamond'] = array();
        $symbol_sizes['diamond'][self::XSMALL] = array(
            6,
            4
        );
        $symbol_sizes['diamond'][self::SMALL] = array(
            8,
            6
        );
        $symbol_sizes['diamond'][self::MEDIUM] = array(
            10,
            8
        );
        $symbol_sizes['diamond'][self::LARGE] = array(
            12,
            10
        );
        $symbol_sizes['diamond'][self::XLARGE] = array(
            14,
            12
        );
        $symbol_sizes['diamond'][self::XXLARGE] = array(
            16,
            14
        );
        $symbol_sizes['house'] = array();
        $symbol_sizes['house'][self::XSMALL] = array(
            6,
            4
        );
        $symbol_sizes['house'][self::SMALL] = array(
            8,
            6
        );
        $symbol_sizes['house'][self::MEDIUM] = array(
            10,
            8
        );
        $symbol_sizes['house'][self::LARGE] = array(
            14,
            12
        );
        $symbol_sizes['house'][self::XLARGE] = array(
            16,
            14
        );
        $symbol_sizes['house'][self::XXLARGE] = array(
            18,
            16
        );
        
        self::SetFontSymbolSizes('circlex_fnt', $symbol_sizes);
        self::SetFontSymbolSizes('cross_fnt',$symbol_sizes);
        self::SetFontSymbolSizes('x_fnt',$symbol_sizes);
        self::SetFontSymbolSizes('hospital_fnt',$symbol_sizes);
        
        
        
        $symbol_sizes['polygon_default'] = self::XLARGE;
        $symbol_sizes['polygon_solid'] = self::XLARGE;
        $symbol_sizes['polygon_hatch_horizontal'] = self::LARGE;
        $symbol_sizes['polygon_hatch_vertical'] = self::LARGE;
        $symbol_sizes['polygon_hatch_right'] = self::XXLARGE;
        $symbol_sizes['polygon_hatch_left'] = self::XXLARGE;
        $symbol_sizes['polygon_grid'] = self::XLARGE;
        $symbol_sizes['polygon_grid_angled'] = self::XXLARGE;
        $symbol_sizes['polygon_grid_inverted'] = self::XLARGE;
        self::$sizes = $symbol_sizes;
        return self::$sizes;
    }

    private static function SetFontSymbolSizes($fontSymName, &$symbol_sizes)
    {
        $symbolSizes[$fontSymName] = array();
        $symbol_sizes[$fontSymName][self::XSMALL] = array(
            10,
            10
        );
        $symbol_sizes[$fontSymName][self::SMALL] = array(
            12,
            12
        );
        $symbol_sizes[$fontSymName][self::MEDIUM] = array(
            14,
            14
        );
        $symbol_sizes[$fontSymName][self::LARGE] = array(
            18,
            18
        );
        $symbol_sizes[$fontSymName][self::XLARGE] = array(
            24,
            24
        );
        $symbol_sizes[$fontSymName][self::XXLARGE] = array(
            32,
            32
        );
    }
}

?>