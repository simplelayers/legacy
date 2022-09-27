<?php

namespace symbols;

use \model\mapping\HilightDriver;
use \SymbolSize;

class Polygon extends Symbol {

    const STANDARD = 1;
    const OUTLINE = 2;
    const COMPLEX = 4;

    private static $symbolTypeEnum;
    private static $default = SymbolSize::XLARGE;
    private static $solid = SymbolSize::XLARGE;
    private static $hatch_horizontal = SymbolSize::LARGE;
    private static $hatch_vertical = SymbolSize::LARGE;
    private static $hatch_right = SymbolSize::XXLARGE;
    private static $hatch_left = SymbolSize::XXLARGE;
    private static $grid = SymbolSize::XLARGE;
    private static $grid_angled = SymbolSize::XXLARGE;
    private static $grid_inverted = SymbolSize::XLARGE;
    private static $outline_thickness_1 = 1;
    private static $outline_thickness_2 = 2;
    private static $outline_thickness_3 = 3;
    private static $outline_thickness_4 = 5;
    private static $outline_thickness_5 = 8;
    private static $outline_thickness_6 = 10;
    private static $outlines = array(
        'outline_dashes',
        'outline_dashes_long',
        'outline_dashes_medium',
        'outline_dashes_short',
        'outline_dotted',
        'outline_dotted_spaced',
        'outline_dot_and_dash',
        'outline_dot_and_long_dash',
        'outline_flow',
        'outline_center',
        'outline_center_half',
        'outline_center_2x',
        'outline_border',
        'outline_border_half',
        'outline_border_2x',
        'outline_phantom',
        'outline_phantom_2x',
        'outline_phantom_half',
        'complex_railroad',
        'complex_railroad_multi',
        'complex_railroad_in_street',
        'complex_dotted',
        'complex_railroad_trunkline',
        'complex_rail',
        'complex_nautical_single',
        'complex_nautical_double',
        'complex_square_tooth',
        'complex_nautical_single_2',
        'complex_square_tooth_hollow',
        'complex_nautical_single_3',
        'complex_square_tooth_filled',
        'complex_nautical_single_4'
    );
    private static $unfillable = array(
        'complex_nautical_single',
        'complex_nautical_double',
        'complex_square_tooth_hollow',
        'complex_square_tooth_filled'
    );
    private static $complexes = array(
        'complex_screen',
        'complex_railroad',
        'complex_railroad_multi',
        'complex_railroad_in_street',
        'complex_dotted',
        'complex_railroad_trunkline',
        'complex_rail',
        'complex_nautical_single',
        'complex_nautical_double',
        'complex_square_tooth',
        'complex_nautical_single_2',
        'complex_square_tooth_hollow',
        'complex_nautical_single_3',
        'complex_square_tooth_filled',
        'complex_nautical_single_4'
    );

    public static function GetSymbolTypeEnum($symbolName) {
        if (!isset(self::$symbolTypeEnum)) {
            self::$symbolTypeEnum = new \FlagEnum();
            $self::$symbolTypeEnum->AddItem('standard');
            $self::$symbolTypeEnum->AddItem('outline');
            $self::$symbolTypeEnum->AddItem('complex');
        }
        return $enum;
    }

    public static function GetSymbolType($symbolName) {
        $type = 0;
        if (self::IsOutline($symbolName)) {
            $type += self::OUTLINE;
        }
        if (self::IsComplex($symbolName)) {
            $type += self::COMPLEX;
        }
        if (!($isOutline || $isComplete)) {
            $type = self::STANDARD;
        }

        return $type;
    }

    public static function IsOutline($symbolName) {
        return in_array($symbolName, self::$outlines);
    }

    public static function IsComplex($symbolName) {
        return in_array($symbolName, self::$complexes);
    }

    public function Get($what, $size = null) {
        $val = null;
        $cmd = '$val = self::$' . $what;
        if (!is_null($size))
            $cmd .= '_' . $size;
        $cmd .= ";";
        eval($cmd);
        return $val;
    }

    private static $enum = null;

    public static function GetEnum($replace = false) {
        if ((self::$enum !== NULL) and ! $replace)
            return self::$enum;
        self::$enum = new \Enum(array());
        self::$enum->AddItem('polygon_default', 'default');
        self::$enum->AddItem('polygon_solid', 'solid');
        self::$enum->AddItem('hatch horizontal', 'hatch_horizontal');
        self::$enum->AddItem('hatch vertical', 'hatch_vertical');
        self::$enum->AddItem('hatch right', 'hatch_right');
        self::$enum->AddItem('hatch left', 'hatch_left');
        self::$enum->AddItem('grid', 'grid');
        self::$enum->AddItem('grid angled', 'grid_angled');
        self::$enum->AddItem('grid inverted', 'grid_inverted');

        return self::$enum;
    }

    public static function SetPattern($ms_symbolObj, $styleObj = NULL, $size = null) {
        if (IS_DEV_SANDBOX) {
// $styleObj->set('antialias', MS_TRUE);
        }
        $one = 25 * $size;
        $name = $ms_symbolObj->name;

// $name = 'polygon_outline_center';
        switch ($name) {
            case "polygon_hatch_right":
                $styleObj->set('angle', 45);
                $styleObj->set('size', $size * 4);
                return true;
                break;
            case "polygon_hatch_left":
                $styleObj->set('angle', - 45);
                $styleObj->set('size', $size * 4);
                return true;
                break;
            case "polygon_hatch_vertical":
            case "polygon_hatch_horizontal":
            case "polygon_grid":
            case "polygon_grid_inverted":
            case "polygon_grid_angled":
                $styleObj->set('size', $size * 4);
                $styleObj->set('width', $size * .75);

                return true;
                break;
            case "outline_dashes":
                self::UpdatePattern($styleObj, array(
                    8,
                    4 * $size,
                    8,
                    4 * $size
                ));
                return true;
                break;
            case "outline_dashes_long":
                self::UpdatePattern($styleObj, array(
                    20,
                    5 * $size,
                    20,
                    5 * $size
                ));
                break;
            case "outline_dashes_medium":
                self::UpdatePattern($styleObj, array(
                    10,
                    5 * $size,
                    10,
                    5 * $size
                ));
                break;
            case "outline_dashes_short":
                self::UpdatePattern($styleObj, array(
                    6,
                    3 * $size,
                    6,
                    3 * $size
                ));
                break;
            case "outline_dotted":


                self::UpdatePattern($styleObj, array(
                    1,
                    3 * $size,
                    1,
                    3 * $size
                ));

                break;
            case "outline_dotted_spaced":
                self::UpdatePattern($styleObj, array(
                    1,
                    7 * $size,
                    1,
                    7 * $size
                ));
                break;
            case "outline_dot_and_dash":
                self::UpdatePattern($styleObj, array(
                    10,
                    3 * $size,
                    2,
                    3 * $size
                ));
                break;
            case "outline_dot_and_long_dash":
                self::UpdatePattern($styleObj, array(
                    10 * $size,
                    2 * $size,
                    2,
                    2 * $size
                ));
                break;
            case "outline_flow":
                self::UpdatePattern($styleObj, array(
                    (.1 * $one), // line
                    (.1 * $one), // gap
                    (.1 * $one), // line
                    (.1 * $one), // gap
                    (.1 * $one), // line
                    (.1 * $one),
                    (.1 * $one),
                    (.1 * $one),
                    (.5 * $one),
                    (.1 * $one)
                ));
                break;
            case "outline_center":
                self::UpdatePattern($styleObj, array(
                    (1.25 * $one), // line
                    (.25 * $one), // gap
                    (.25 * $one), // line
                    (.25 * $one)
                ));
                break;
            case "outline_center_half":
                self::UpdatePattern($styleObj, array(
                    (.75 * $one), // line
                    (.125 * $one), // gap
                    (.125 * $one), // line
                    (.125 * $one)
                ));
                break;
            case "outline_center_2x":
                self::UpdatePattern($styleObj, array(
                    (2.5 * $one), // line
                    (.5 * $one), // gap
                    (.5 * $one), // line
                    (.5 * $one)
                ));
                break;
            case "outline_border":
                self::UpdatePattern($styleObj, array(
                    (.5 * $one), // line
                    (.25 * $one), // gap
                    (.5 * $one), // line
                    (.25 * $one),
                    1,
                    (.25 * $one)
                ));
                break;
            case "outline_border_half":
                self::UpdatePattern($styleObj, array(
                    (.25 * $one), // line
                    (.125 * $one), // gap
                    (.25 * $one), // line
                    (.125 * $one),
                    0,
                    (.125 * $one)
                ));

                break;
            case "outline_border_2x":
                self::UpdatePattern($styleObj, array(
                    (1.0 * $one), // line
                    (.5 * $one), // gap
                    (1 * $one), // line
                    (.5 * $one),
                    0,
                    (.5 * $one)
                ));
                break;
            case "outline_phantom":
                self::UpdatePattern($styleObj, array(
                    (1.25 * $one), // line
                    (.25 * $one), // gap
                    (.25 * $one), // line
                    (.25 * $one), // gap
                    (.25 * $one), // line
                    (.25 * $one)
                ));
                break;
            case "outline_phantom_2x":
                self::UpdatePattern($styleObj, array(
                    (2.5 * $one), // line
                    (.5 * $one), // gap
                    (.5 * $one), // line
                    (.5 * $one), // gap
                    (.5 * $one), // line
                    (.5 * $one)
                ));
                break;
            case 'outline_phantom_half':
                self::UpdatePattern($styleObj, array(
                    (.625 * $one), // line
                    (.125 * $one), // gap
                    (.125 * $one), // line
                    (.125 * $one), // gap
                    (.125 * $one), // line
                    (.125 * $one)
                ));
                break;
            case "polygon_default":
            default:
                return false;
        }
        return true;
    }

    public static function SetStandardStroke($ms_classObj, $width, $size, $buffer, HilightDriver $hilightDriver, $layerOffset, $strokeOpacity = null) {
        #var_dump("setting standard stroke");
        #var_dump($width,$size);
        $hilightingStage = $hilightDriver->GetHilightStage($layerOffset);
        $hilightDriver->SetStyleVars($hilightingStage, $sr, $sg, $sb, $fr, $fg, $fb, $opacity);

        $style = \ms_newStyleObj($ms_classObj);
        if ($hilightingStage === HilightDriver::UNDERLAY) {
            #$width = $width * 4;
        }
        $opacity255 = $opacity * 255 / 100;
        if (!is_null($strokeOpacity)) {
            $opacity255 = round($strokeOpacity * 255);
        }
        $buffer = ($buffer === 0) ? "" : "geomtransform(buffer([shape], $buffer))";

        $joinmax = $size * 10;
        $styleString = <<<LINE
STYLE
LINECAP ROUND
LINEJOIN ROUND
$buffer
                
WIDTH $width
END #STYLE
LINE;
        $style->updateFromString($styleString);

        $style->outlinecolor->setRGB($sr, $sg, $sb, $opacity255);
        #$style->color->setRGB(0, 0, 0, 0);
        #$style->symbolname = $symbolObj->name;
        #var_dump("setting standard stroke");
        #var_dump($width, $size, $opacity255, $buffer);

        return $style;
    }

    public static function SetStroke($map, $ms_classObj, $symbolObj, $size, HilightDriver $hilightDriver, $layerOffset,$strokeOpacity=null) {
        $hilightingStage = $hilightDriver->GetHilightStage($layerOffset);
        $strokeStyle = null;
        if ($hilightDriver->GetColor($hilightingStage, 'stroke') === 'trans') {
            return null;
        }
        if ($hilightingStage === HilightDriver::UNDERLAY) {
            $symbolName = $symbolObj->name;
            $parts = explode('_', $symbolName);
            #if ($parts[0] === 'polygon') {
            $strokeStyle = self::SetStandardStroke($ms_classObj, $size * 3, $size, 0, $hilightDriver, $layerOffset,$strokeOpacity);
            #} 
            #$size = $size * 3;
        }
        if (is_null($strokeStyle)) {
            $strokeStyle = self::SetStandardStroke($ms_classObj, $size, $size, 0, $hilightDriver, $layerOffset,$strokeOpacity);
        }
        if (is_null($strokeStyle)) {
            return;
        }
        self::SetPattern($symbolObj, $strokeStyle, $size);
        return $strokeStyle;
    }

    public static function SetComplexStroke($ms_classObj, $symbolObj, $size, HilightDriver $hilightDriver, $layerOffset) {
        $hilightingStage = $hilightDriver->GetHilightStage($layerOffset);

        $hilighting = $hilightingStage === HilightDriver::UNDERLAY;
        $opacity = $fr = $fg = $fb = $r = $g = $b = -1;

        $vlineNH = 10; // vertline symbol's "height" is 10
        $lineNW = 1; // vertline symbol's "width" is 1;

        $color = $hilightDriver->GetColor($layerOffset, 'stroke');
        $fillColor = $hilightDriver->GetColor($layerOffset, 'fill');
        $hilightDriver->SetStyleVars($hilightingStage, $r, $g, $b, $fr, $fg, $fb, $opacity);

        $opacity255 = $opacity * 255 / 100;


        $hilightOffset = 0; //($hilighting) ? ($size * 1.5) : 0;
        $outlineSize = ($hilighting) ? $size * .00005 : 0;
        $adjSize = $size;

//$strokeColor = $hilightDriver->GetColor($layerOffset, 'stroke');
//$fillColor = $hilightDriver->GetColor($layerOffset, 'fill');

        switch ($symbolObj->name) {

            case 'complex_railroad':
                $initialGap = 0;

                switch ($size) {
                    case \utils\SymbolSizes::SYMBOLSIZE_MEDIUM:
                        $vertSize = ($hilighting) ? 5 : 4;
                        $initialGap = ($hilighting) ? $adjSize * .5 : $adjSize;
                        break;
                    case \utils\SymbolSizes::SYMBOLSIZE_SMALL;
                        $vertSize = ($hilighting) ? 4 : 3;
                        $initialGap = ($hilighting) ? $adjSize * .33 : $adjSize;
                        break;
                    case \utils\SymbolSizes::SYMBOLSIZE_XSMALL;
                        $vertSize = ($hilighting) ? 3 : 2;
                        $initialGap = ($hilighting) ? $adjSize * .125 : $adjSize;
                        $initialGap = ($hilighting) ? 5 + 4 * $adjSize : 0;
                        break;
                    default:
                        $vertSize = ($hilighting) ? 13 : 10;
                        $initialGap = ($hilighting) ? 0 : 19;
                }
                self::SetStandardStroke($ms_classObj, $adjSize, $size, 0, $hilightDriver, $layerOffset);

                if ($hilightingStage === HilightDriver::OVERLAY) {
                    die();
                }
                if ($hilighting)
                    return;


                $gap = 5 + $size * 6; //($hilighting) ? 51 : 50;

                $styleString = <<<ARROW_END
 STYLE
    ANGLE 90
    LINECAP SQUARE
    SYMBOL "vertline"
    GAP -$gap    
END # STYLE
ARROW_END;
                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString);
                $style->size = 5 + $size * 4;
                $style->width = $size;
                $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                #$style->opacity = $opacity;
                #var_dump($style->convertToString());
                #die();
                return;
                break;
            case 'complex_railroad_multi':
                $adjWidth = $adjSize;
                $buffer = $size;
                $unAdj = 0;
                if ($hilightingStage == HilightDriver::OVERLAY) {
                    #$adjWidth = $adjSize * 3;
                    #$buffer = ($adjSize / 2.0) * $size;
                    #$sr = $sg = $sb = $fr = $fg = $fb = 255;
                    #var_dump($size);
                    #die();
                } elseif ($hilightingStage == HilightDriver::UNDERLAY) {
                    $adjWidth = $size * 4;
                    $buffer = 0;
                    #$adjWidth = $adjSize * 3;
                    #$buffer = ($adjSize / 2.0) * $size;
                    #var_dump($size);
                } else {
                    #$adjWidth = $size;
                    #$buffer = $size;
                    #var_dump($size);
                }
                $top = self::SetStandardStroke($ms_classObj, $adjWidth, $size, $buffer, $hilightDriver, $layerOffset);
                $bottom = self::SetStandardStroke($ms_classObj, $adjWidth, $size, -$buffer, $hilightDriver, $layerOffset);

                $hilightDriver->SetStyleVars($hilightingStage, $sr, $sg, $sb, $fr, $fg, $fb, $opacity);
                if ($hilightingStage == HilightDriver::UNDERLAY) {
                    return;
                }

                $gap = ($hilighting) ? 6 + $size * 6 : 5 + $size * 6;
                $offsetY = -$size * 1.75;
                $initialGap = $gap * .5;
                #self::SetStandardLine($className, $size, $ms_classObj, $color, - $size, 'square', $opacity);
                #self::SetStandardLine($className, $size, $ms_classObj, $color, $size, 'square', $opacity);
                $styleString = <<<SYMBOL_END
STYLE
    SYMBOL "vertline"
    ANGLE 90
    GAP -$gap
    INITIALGAP $initialGap
    
END # STYLE
SYMBOL_END;


                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString);
                $style->size = 5 + $adjSize * 6;
                $style->width = $adjWidth / 2;
                $style->outlinecolor->setRGB($sr, $sg, $sb, 255); //$opacity255);

                break;
            case 'complex_railroad_in_street':
                $top = self::SetStandardStroke($ms_classObj, $adjSize, $size, -$size * 1.5, $hilightDriver, $layerOffset);
                $bottom = self::SetStandardStroke($ms_classObj, $adjSize, $size, $size * 1.5, $hilightDriver, $layerOffset);
                $gap = 5 + $size * 6;

                $styleString = <<<SYMBOL_END
STYLE
    SYMBOL "vertline"
    GAP -$gap                        
    ANGLE 0
END # STYLE
SYMBOL_END;
                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString);
                $style->size = $size;
                $style->width = $size * 2;
                #$style->set('offsety',$size * 1.25);
                $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                # var_dump($style->convertToString());
                #    die();
                break;
            case 'complex_dotted':

            case 'complex_railroad_trunkline':
                if ($hilighting) {
                    $adjSize *= 3;
                }
                $top = self::SetStandardStroke($ms_classObj, $adjSize, $size, -$size, $hilightDriver, $layerOffset);
                $bottom = self::SetStandardStroke($ms_classObj, $adjSize, $size, $size, $hilightDriver, $layerOffset);

                break;
            case 'complex_rail':
            case 'complex_nautical_single':
                $adjWidth = $adjSize;
                if ($hilighting) {
                    $adjWidth = $adjSize * 2.5;
                }
                $style = self::SetStandardStroke($ms_classObj, $adjWidth, $size, 0, $hilightDriver, $layerOffset);

                #$style->set('width', $adjSize);
                #$style->set('size', $size + 4);
                #$style->set('linecap', 'square');
                #die($symbol->name);
#$style->updateFromString('geomtransform buffer([shape],' . ($size / 2.0) . ')');


                $style = \ms_newStyleObj($ms_classObj);
                #$style->set('symbolname','vertline');

                $fillColor = is_null($fillColor) ? array(
                    255,
                    255,
                    255
                        ) : $fillColor;
                #list ($fr, $fg, $fb) = $fillColor;
                $style->outlinecolor->setRGB($fr, $fg, $fb, 255);
                $style->set('width', $adjWidth);
                #$style->set('size', $size * 10);
                $style->set('linecap', 'square');
                $style->set('linejoin', 'miter');
                $offsetY = -($size) / 2;
                #$style->updateFromString("geomtransform(buffer([shape], $offsetY))");

                self::UpdatePattern($style, array(
                    6 * $size,
                    6 * $size
                ));

#$style->updateFromString("geomtransform buffer([shape],' . ($size / 2.0) . ')');
                #$style->opacity = $opacity;

                break;

            case 'complex_nautical_double':

                $top = self::SetStandardStroke($ms_classObj, $adjSize + 4, $adjSize + 4, 0, $hilightDriver, $layerOffset);
                $bottom = self::SetStandardStroke($ms_classObj, $adjSize + 4, $adjSize + 4, -($adjSize + 4) * 1.5, $hilightDriver, $layerOffset);


                $style = \ms_newStyleObj($ms_classObj);

                $style->outlinecolor->setRGB($fr, $fg, $fb, $opacity255);
                $style->set('width', $adjSize + 2);

                $style->updatefromString('LINECAP SQUARE');
                $style->updatefromString('LINEJOIN MITER');
                $offsetY = -($adjSize) + 1;
                $style->updateFromString("geomtransform(buffer([shape], $offsetY))");
                $style->set('initialgap', 2 * (3.14 / 180) * $size);
                self::UpdatePattern($style, array(
                    4 * $size,
                    5 * $size
                ));



                $style = \ms_newStyleObj($ms_classObj);

                $style->outlinecolor->setRGB($fr, $fg, $fb, $opacity255);
                $style->set('width', $adjSize + 2);
                #$style->set('size', $size + 2);
                $style->set('initialgap', 0);

                $style->updateFromString('LINECAP SQUARE');
                $style->updatefromString('LINEJOIN MITER');

                self::UpdatePattern($style, array(
                    4 * $size,
                    8 * $size
                ));
                $offsetY = -($adjSize + 2) * 2 + 1;
                $style->updateFromString("geomtransform(buffer([shape], $offsetY))");

                return;

// $style->updateFromString("geomtransform (buffer([shape], $buffer))");
// self::SetStandardLine($className, $size * 3 + (2 / $size), $ms_classObj, $color,$buffer);
                $width = $size + 4;
                $styleString = <<<STYLESTRING
STYLE
geomtransform (buffer([shape], $buffer),0)
OUTLINECOLOR $r $g $b
WIDTH $width
SIZE $width
LINECAP SQUARE
END
STYLESTRING;
                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString);

                $offsetY = -($size) / 2;
                $style->updateFromString("geomtransform(buffer([shape], $offsetY))");
                /*
                 * $style =\ms_newStyleObj($ms_classObj);
                 * list ($r, $g, $b) = array(
                 * 254,
                 * 254,
                 * 254
                 * );
                 * $style->outlinecolor->setRGB($r, $g, $b);
                 * $style->set('width', $size + 2);
                 * $style->set('size', $size + 2);
                 * $style->set('linecap', 'square');
                 * $style->set('linejoin', 'bevel');
                 * $style->set('initialgap',0);
                 * $style->setPattern(array(
                 * 6 * $size,
                 * 6 * $size
                 *
                 * ));
                 *
                 * $style->updateFromString("geomtransform (buffer([shape], $buffer))");
                 */

                break;
            case 'complex_square_tooth':
            case 'complex_nautical_single_2':
                $style = self::SetStandardStroke($ms_classObj, $adjSize, $size, 0, $hilightDriver, $layerOffset);
                #self::SetStandardLine($className, $size, $ms_classObj, $color, 0, 'square', $opacity);
                $size;
                $gap = 8 * $outlineSize;

                $width = $size * 4;
                $size2 = 3 * $size;
                $halfWidth = ($width / 2) - 1;
                $styleString1 = <<<STYLE_STRING
                
STYLE
geomtransform(buffer([shape], -$halfWidth))
LINECAP square
WIDTH $width
PATTERN $size2 $gap  END
END #STYLE
STYLE_STRING;
// $patternOn = 6 * $size;
// $patternOff = 6 * $size;

                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString1);
// $style->set('gap',null);

                $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);
                $offsetY = -($size * 2.5);
                $style->updateFromString("geomtransform(buffer([shape], $offsetY))");

                break;
            case 'complex_square_tooth_hollow':
            case 'complex_nautical_single_3':
                $style = self::SetStandardStroke($ms_classObj, $adjSize, $size, 0, $hilightDriver, $layerOffset);
                $gap = 12 * $size;
                $halfGap = $gap / 2.0;

                $width = $size * 2.5;
                $size2 = 3 * $size;
                $halfWidth = ($width * 2) - 1;

                $styleString1 = <<<STYLE_STRING
STYLE
SYMBOL "rectangle_hollow"
GAP -$gap
LINEJOIN  bevel
INITIALGAP 0
WIDTH $size
SIZE $size2
ANGLE 0
END #STYLE
STYLE_STRING;
// $patternOn = 6 * $size;
// $patternOff = 6 * $size;

                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString1);
// $style->set('gap',null);

                $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);
                $offsetY = -($size * 2);
                $style->updateFromString("geomtransform(buffer([shape], $offsetY))");

                break;
            case 'complex_square_tooth_filled':
            case 'complex_nautical_single_4':
                if ($hilighting) {
                    $bottom = self::SetStandardStroke($ms_classObj, $adjSize * 2, $adjSize, 0, $hilightDriver, $layerOffset);
                } else {
                    $bottom = self::SetStandardStroke($ms_classObj, $adjSize, $adjSize, 0, $hilightDriver, $layerOffset);
                }
                if ($hilighting) {
                    $hilightDriver->SetStyleVars(HilightDriver::OVERLAY, $r, $g, $b, $fr, $fg, $fb, $opacity);
                    $opacity255 = $opacity * 255 / 100;
                }
                #if($hilighting) return;
                $gap = $size * 8;
                $initialGap = $hilighting ? $gap / 4 : $gap / 22;
                $width = ($adjSize * 2);
                $size2 = $adjSize;


                $styleString1 = <<<STYLE_STRING
                
STYLE
geomtransform(buffer([shape], -$width))
SYMBOL "rectangle_hollow"
LINECAP square
WIDTH $width
SIZE $size2
GAP -$gap
END #STYLE
STYLE_STRING;
// $patternOn = 6 * $size;
// $patternOff = 6 * $size;

                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString1);
// $style->set('gap',null);
                $style->updateFromString('polaroffset 2000 45');

                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);
                if ($hilightingStage === HilightDriver::OVERLAY) {
                    $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                    return;
                } else {
                    $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                }

                $gap = 8 * $size;
                $width = $adjSize * 1.5;
                $size2 = $size * .64;
                $halfWidth = $adjSize * 2;

// $halfWidth = ($width/2)-1;
                $styleString1 = <<<STYLE_STRING
                
STYLE
SYMBOL "rectangle_hollow"
LINECAP square
WIDTH $width
SIZE $size2
GAP -$gap
END #STYLE
STYLE_STRING;
// $patternOn = 6 * $size;
// $patternOff = 6 * $size;

                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString1);
                $offsetY = -($size * 2);
                $style->updateFromString("geomtransform(buffer([shape], $offsetY))");
// $style->set('gap',null);

                $style->outlinecolor->setRGB($fr, $fg, $fb, $opacity255);

                break;

            case 'complex_transform':
                self::SetStandardLine($className, $size, $ms_classObj, $color, 0, 'square', $opacity);

                list ($r, $g, $b) = $color;
                $width = $size * 4;
                $size2 = 3 * $size;
                $gap = 3 * $width;


                $halfWidth = ($width / 2) - 1;
                $styleString1 = <<<STYLE_STRING
                
STYLE
geomtransform(buffer([shape], -$halfWidth))
LINECAP square
WIDTH 10
PATTERN 10 20  END
END #STYLE
STYLE_STRING;
// $patternOn = 6 * $size;
// $patternOff = 6 * $size;

                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString1);
// $style->set('gap',null);

                $fillColor = is_null($fillColor) ? array(
                    255,
                    255,
                    255
                        ) : $fillColor;
                list ($fr, $fg, $fb) = $fillColor;
                $style->outlinecolor->setRGB($r, $g, $b);
                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);
                $style->opacity = $opacity;

// $gap = 8 * $size;
                list ($r, $g, $b) = $color;
// $width = $size * 3;
// $size2 = 3 * $size;

                $width2 = 1.8 * $width;
                $size3 = $size;
                $gap = 3 * $width;
// $halfWidth = ($width/2)-1;
                $styleString1 = <<<STYLE_STRING
                
STYLE
Symbol "line_default"
BACKGROUNDCOLOR 255 255 255
geomtransform(buffer([shape], -$width))
LINECAP square
WIDTH 10

PATTERN 10 20  END
END #STYLE
STYLE_STRING;
// $patternOn = 6 * $size;
// $patternOff = 6 * $size;

                $style = \ms_newStyleObj($ms_classObj);
                $style->updateFromString($styleString1);
// $style->set('gap',null);
                $initGap = $gap / 2.0 + 2.5 * $size;
                $style->set('initialgap', 0);

                $style->outlinecolor->setRGB($fr, $fg, $fb);
                $style->opacity = $opacity;

                break;
            /**
             * case 'complex_arrow_start':
             *
             * self::SetStandardLine($className, $size, $ms_classObj, $color,$opacity);
             *
             * self::AddArrow(self::ARROW_START, $size, $ms_classObj, $color,$opacity);
             * break;
             * case "complex_arrow_start_end":
             * self::SetStandardLine($className, $size, $ms_classObj, $color,$opacity);
             *
             * self::AddArrow(self::ARROW_BOTH, $size, $ms_classObj, $color,$opacity);
             * break;
             * case "complex_arrow_middle_right":
             * self::SetStandardLine($className, $size, $ms_classObj, $color,$opacity);
             *
             * self::AddArrow(self::ARROW_MIDDLE_RIGHT, $size, $ms_classObj, $color,$opacity);
             * break;
             * case "complex_arrow_middle_left":
             * self::SetStandardLine($className, $size, $ms_classObj, $color,$opacity);
             *
             * self::AddArrow(self::ARROW_MIDDLE_LEFT, $size, $ms_classObj, $color,$opacity);
             * break;
             */
        }
    }

    public static function SetComplexFill($mapObj, $ms_classObj, $symbolObj, $size, HilightDriver $hilightDriver, $layerOffset) {
        $hilightStage = $hilightDriver->GetHilightStage($layerOffset);
        #$opacity255 = 255;
        if ($hilightDriver->hilighting) {
            if ($hilightStage === HilightDriver::NATURAL) {

                $hilightDriver->SetStyleVars(HilightDriver::OVERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $opacity);
                $opacity255 = 254 * $opacity / 100;
            } else {
                return;
            }
        } else {
            $hilightDriver->SetStyleVars($hilightStage, $sr, $sg, $sb, $fr, $fg, $fb, $opacity);
            $opacity255 = $opacity; // * $opacity / 100;
        }

        $symbolName = $symbolObj->name;

        $fillColor = $hilightDriver->getColor($layerOffset, 'fill');
        switch ($symbolName) {
            case 'complex_screen':


                if ($fillColor === 'trans') {
                    #return;
                }
                $size = SymbolSize::GetSrcSize($size);
                $symbolId = ms_newSymbolObj($mapObj, $symbolName);
                $symbolObj = $mapObj->getSymbolObjectById($symbolId);
                $symbolObj->set('filled', true);
                $symbolObj->set('type', MS_SYMBOL_ELLIPSE);
                $symbolObj->setPoints(array(
                    $size, $size
                ));

                $style = \ms_newStyleObj($ms_classObj);

                $style->set('symbolname', 'polygon_screen');
                $style->set('symbol', $symbolId);
                /* switch ($size) {
                  case SymbolSize::XSMALL:
                  $style->size = .5;
                  break;
                  case SymbolSize::SMALL;
                  $style->size = .65;
                  #$style->gap = .65;
                  break;
                  case SymbolSize::MEDIUM:
                  $style->size = .75;
                  break;
                  case SymbolSize::LARGE:
                  $style->size = 0.85;
                  break;
                  case SymbolSize::XLARGE:
                  $style->size = 0.95;
                  break;
                  case SymbolSize::XXLARGE:
                  $style->size = 1;

                  break;
                  }
                 */
                $style->size = ($size / 2);
                #$size=100;
                $style->width = $size * 4;
//list ($r, $g, $b) = $fillColor;
                if ($size === SymbolSize::XLARGE) {
                    $style->size = $size / 2;
                    $style->width = $size * 4;
                }
                if ($size === SymbolSize::XXLARGE) {
                    $style->size = $size / 2.5;
                    $style->width = $size * 4;
                }
                $style->color->setRGB($fr, $fg, $fb, $opacity255); //$opacity255);
                #die();
                return;
                break;
        }
    }

    public static function SetFill($msClass, $symbolObj, $size, HilightDriver $hilightDriver, $layerOffset, $fillOpacity = null) {
        $hilightingStage = $hilightDriver->GetHilightStage($layerOffset);

        $hilightDriver->SetStyleVars($hilightingStage, $sr, $sg, $sb, $fr, $fg, $fb, $opacity);
        $opacity255 = 255; //*$opacity/100; 
        if ($fillOpacity) {
            $opacity255 = round(255 * floatval($fillOpacity));
        }
        $symbolName = $symbolObj->name;
        if ($symbolName === 'default') {
            $symbolName = 'polygon_default';
        }
        $useSymbolObj = false;
        switch ($hilightingStage) {
            case HilightDriver::UNDERLAY:
                #return;
                break;
            case HilightDriver::OVERLAY:
                $useSymbolObj = true;
                $symbolName = 'polygon_default';
                break;
            default:
                $useSymbolObj = false;
                break;
        }
        if ($hilightDriver->hilighting === false) {
            if(self::IsOutline($symbolName) || self::IsComplex($symbolName)) {
                $useSymbolObj = true;
            } 
            $useSymbolObj = false;
        }

        $fill_color = ($useSymbolObj) ? $symbolObj : $hilightDriver->GetColor($layerOffset, 'fill');

        if (!$useSymbolObj) {
            
            if (strlen($fill_color) === 9) {
                $fill_color = 'trans';
            }
            if ($fill_color === 'trans') {
                return;
            }
        }

        $fill = ms_newStyleObj($msClass);
        $fill->set('size', $size);
        $fill->color->setRGB($fr, $fg, $fb, $opacity255);

        #var_dump($symbolObj->name);
        if (!$useSymbolObj) {

            $fill->set('symbolname', $symbolName);
        }

        if (!Polygon::SetPattern($symbolObj, $fill, $size)) {
            $fill->set('width', $size);
        }
        return $fill;
    }

    public static function SetPolygon($mapObj, $ms_classObj, $symbolName, $entrySize, HilightDriver $hilightDriver, $layerOffset, $hadClass = false, $fillOpacity = null, $strokeOpacity = null) {

        $size = $entrySize;
        $size = SymbolSize::GetOutlineThickness($entrySize);
        $hilightingStage = $hilightDriver->GetHilightStage($layerOffset);

        if ($hilightingStage === HilightDriver::OVERLAY) {
            if ($hadClass === false) {
                #$screenSize = SymbolSize::MEDIUM;
                #$fillObj = $mapObj->getSymbolObjectById(ms_newSymbolObj($mapObj, 'complex_screen'));
                # self::SetComplexFill($mapObj, $ms_classObj, $fillObj, $screenSize, $hilightDriver, $layerOffset);
            }
        }

        $isComplex = Polygon::IsComplex($symbolName);
        $isOutline = Polygon::IsOutline($symbolName);
        $isComplexFill = ($isComplex && !$isOutline);
        $isComplexOutline = ($isComplex && $isOutline);
        $isStandard = !($isComplex || $isOutline);


        if ($isStandard) {
            if ($symbolName !== 'default') {
                $symbolName = 'polygon_' . $symbolName;
            }
        }

        $isFillable = (array_search($symbolName, self::$unfillable) === false);

        $symbolObj = $mapObj->getSymbolObjectById(ms_newSymbolObj($mapObj, $symbolName));
        if ($isComplexFill) {
            if (($hilightDriver->hilighting === true) && !($hilightingStage === HilightDriver::OVERLAY)) {
                #return;
            }
            self::SetComplexFill($mapObj, $ms_classObj, $symbolObj, $size, $hilightDriver, $layerOffset);
            $isFillable = false;

            #return;
        }

        $fillSymbolObj = ($isStandard) ? $symbolObj : $mapObj->getSymbolObjectById(ms_newSymbolObj($mapObj, 'default'));
        if ($isFillable === true || $hilightDriver->hilighting) {
            //if (($hilightingStage === HilightDriver::OVERLAY)) {
            #return;
            //} else {
            $fillStyle = self::SetFill($ms_classObj, $fillSymbolObj, $size, $hilightDriver, $layerOffset, $fillOpacity);

            //}
        }


        if ($isComplexOutline) {
            #var_dump($symbolName,$hilightDriver->GetHilightStage($layerOffset));
            #var_dump($hilightDriver->GetHilightStage($layerOffset) === HilightDriver::OVERLAY);
            self::SetComplexStroke($ms_classObj, $symbolObj, $size, $hilightDriver, $layerOffset);
        } elseif ($isOutline) {
            $strokeStyle = self::SetStroke($mapObj, $ms_classObj, $symbolObj, $size, $hilightDriver, $layerOffset,$strokeOpacity);
            
        } else {
            $strokeStyle = self::SetStroke($mapObj, $ms_classObj, $symbolObj, $size, $hilightDriver, $layerOffset,$strokeOpacity);
            
            #$symbolObj = $mapObj->getSymbolObjectById(ms_newSymbolObj($mapObj, $symbolName));
        }
    }

    public static function UpdatePattern($styleObj, $pattern) {
        $patterStr = implode(' ', $pattern);
        $updateStr = <<<PATTERNSTR
PATTERN
     $patterStr
END #PATTERN
PATTERNSTR;
        $styleObj->updateFromString($updateStr);
    }

    public static function Linify($style) {
        $ini = \System::GetIni();
        $jsFile = WEBROOT . $ini->maps_js_path . 'geomtransform_poly2line.js';

        $geomTransformString = "\"javascript://$jsFile\"";

        $styleStr = <<<STYLE_STR
STYLE
WIDTH 2
GEOMTRANSFORM $geomTransformString
END #STYLE
STYLE_STR;

#$style->updateFromString($styleStr);
        $style->setGeomTransform('javascript:///var/sites/staging/app-services/media/js/geomtransform_poly2line.js');
    }

}

?>