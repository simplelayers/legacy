<?php

namespace symbols;

use \model\mapping\HilightDriver;
use utils\SymbolSizes;

class Lines {

    private static $enum = null;

    const ARROW_MIDDLE_LEFT = - 2;
    const ARROW_START = - 1;
    const ARROW_START_REV = - 4;
    const ARROW_BOTH = 0;
    const ARROW_MIDDLE_RIGHT = 2;
    const ARROW_END = 1;
    const ARROW_END_REV = 4;

    public static function GetEnum($replace = false) {
        if ((self::$enum !== NULL) and ! $replace)
            return self::$enum;
        self::$enum = new \Enum(array());
        self::$enum->AddItem('solid', 'default');
        self::$enum->AddItem('dashes long', 'dashes_long');
        self::$enum->AddItem('dashes medium', 'dashes_medium');
        self::$enum->AddItem('dashes short', 'dashes_short');
        self::$enum->AddItem('dotted', 'dotted');
        self::$enum->AddItem('dotted spaced', 'dotted_spaced');
        self::$enum->AddItem('dot and dash', 'dot_and_dash');
        self::$enum->AddItem('rail-single track', 'complex_rail');
        self::$enum->AddItem('arrow-end', 'complex_arrow_end');
        self::$enum->AddItem('arrow-start', 'complex_arrow_start');
        self::$enum->AddItem('arrow-start-end', "complex_arrow_start_end");
        self::$enum->AddItem('arrow-middle-left', "complex_arrow_middle_left");
        self::$enum->AddItem('arrow-middle-right', "complex_arrow_middle_right");
        self::$enum->AddItem('railroad', "complex_railroad");
        self::$enum->AddItem('railroad-multitrack', "complex_railroad_multi");
        self::$enum->AddItem('railroad-in-street', "complex_railroad_in_street");
        self::$enum->AddItem('railroad-trunkline', "complex_railroad_trunkline");
        self::$enum->AddItem('nautical-single', "complex_nautical_single");
        self::$enum->AddItem('nautical-double', "complex_nautical_double");
        self::$enum->AddItem('dot and long dash', "dot_and_long_dash");
        self::$enum->AddItem('border', 'border');
        self::$enum->AddItem('border x 2', 'border_2x');
        self::$enum->AddItem('border x 1/2', 'border_half');
        self::$enum->AddItem('center', 'center');
        self::$enum->AddItem('center (2x)', 'center_2x');
        self::$enum->AddItem('center (.5x)', 'center_half');

        self::$enum->AddItem('flow', 'flow');
        self::$enum->AddItem('phantom', 'phantom');
        self::$enum->AddItem('phantom (2x)', 'phantom_2x');
        self::$enum->AddItem('phantom (.5x)', 'phantom_half');

        return self::$enum;
    }

    protected static function AddArrow($which, $size = 1, $ms_classObj, HilightDriver $hilightDriver, $layerOffset) {
        //$hilightDriver->GetColor($layerOffset, 'stroke');
        $hilightStage = $hilightDriver->GetHilightStage($layerOffset);
        $hilightDriver->SetStyleVars($hilightStage, $r, $g, $b, $fr, $fg, $fb, $xpacity);
        $opacity = 1;
        $hilighting = ( $hilightStage === HilightDriver::UNDERLAY);
        $adjMult = ($hilighting) ? 5 : 4;
        $adjSize = round($size * $adjMult + (($hilighting === true) ? ($size * 2) : $size * .5));
        $offset = ($hilighting) ? $adjSize / 8 : 0;
        switch ($which) {
            case self::ARROW_START:
                $styleString = <<<ARROW_END
                STYLE
SIZE $adjSize
GEOMTRANSFORM "start"
POLAROFFSET -$offset 0
SYMBOL "arrow_left"
ANGLE AUTO
END # STYLE
ARROW_END;
                break;
            case self::ARROW_START_REV:
                $styleString = <<<ARROW_END
                STYLE
SIZE $adjSize
GEOMTRANSFORM "start"
POLAROFFSET -$offset 0
SYMBOL "arrow_right_rev"
ANGLE AUTO
END # STYLE
ARROW_END;
                break;
            case self::ARROW_MIDDLE_RIGHT:
                $styleString = <<<ARROW_END
                STYLE
GEOMTRANSFORM "center"
SYMBOL "arrow_right_middle"
SIZE $adjSize
POLAROFFSET $offset 0
GAP -100
ANGLE AUTO
END # STYLE
ARROW_END;
                break;
            case self::ARROW_BOTH:
                self::AddArrow(self::ARROW_START, $size, $ms_classObj, $hilightDriver, $layerOffset);
                self::AddArrow(self::ARROW_END, $size, $ms_classObj, $hilightDriver, $layerOffset);
                return;
                break;
            case self::ARROW_END:
                $symbolName = 'arrow_right';

                $styleString = <<<ARROW_END
                STYLE
SIZE $adjSize
GEOMTRANSFORM "end"
POLAROFFSET $offset 0
SYMBOL "$symbolName"
ANGLE AUTO
END # STYLE
ARROW_END;
                break;
            case self::ARROW_END_REV:
                $styleString = <<<ARROW_END
                STYLE
SIZE $adjSize
GEOMTRANSFORM "end"
POLAROFFSET $offset 0
SYMBOL "arrow_left_rev"
ANGLE AUTO
END # STYLE
ARROW_END;
                break;
            case self::ARROW_MIDDLE_LEFT:
                //$offset = ($hilighting) ? 1000 : 0; // 10;//($hilighting) ? $offset * 2 : $offset;
                $gap = $size * 20;
                $styleString = <<<ARROW_END
STYLE
    SIZE $adjSize
    GEOMTRANSFORM "center"
    SYMBOL "arrow_left_middle"
    GAP -$gap
    ANGLE AUTO
END # STYLE
ARROW_END;
                break;
        }
        $style = \ms_newStyleObj($ms_classObj);
        $style->updateFromString($styleString);
        $style->color->setRGB($r, $g, $b);
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

    public static function SetPattern($ms_symbolObj, $styleObj, $size = 1) {

        $styleObj->set('gap', null);

        // $styleObj->set('antialias', 1);
        $one = 25 * $size;
        $name = $ms_symbolObj->name;
        $olen = strlen('outline_');
        if (substr($name, 0, $olen) === 'outline_') {
            $name = substr($name, $olen);
        }
        $size5 = 5 * $size;

        // $name = 'line_phantom';
        switch ($name) {
            case "dashes":
                self::UpdatePattern($styleObj, array($size5, $size5, $size5, $size5));
                break;
            case "dashes_long":
                self::UpdatePattern($styleObj, array(20, $size5, 20, $size5));
                break;
            case "dashes_medium":
                self::UpdatePattern($styleObj, array(10, $size5, 10, $size5));
                break;
            case "dashes_short":
                self::UpdatePattern($styleObj, array(
                    6,
                    3 * $size,
                    6,
                    3 * $size
                ));
                break;
            case "dotted":
                self::UpdatePattern($styleObj, array(
                    1,
                    3 * $size,
                    1,
                    3 * $size
                ));
                break;
            case "dotted_spaced":
                self::UpdatePattern($styleObj, array(
                    1,
                    7 * $size,
                    1,
                    7 * $size
                ));
                break;
            case "dot_and_dash":
                self::UpdatePattern($styleObj, array(
                    10,
                    3 * $size,
                    2,
                    3 * $size
                ));
                break;
            case "dot_and_long_dash":
                self::UpdatePattern($styleObj, array(
                    10 * $size,
                    2 * $size,
                    2,
                    2 * $size
                ));
                break;
            case "flow":
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
            case "center":
                self::UpdatePattern($styleObj, array(
                    (1.25 * $one), // line
                    (.25 * $one), // gap
                    (.25 * $one), // line
                    (.25 * $one)
                ));
                break;
            case "center_half":
                self::UpdatePattern($styleObj, array(
                    (.75 * $one), // line
                    (.125 * $one), // gap
                    (.125 * $one), // line
                    (.125 * $one)
                ));
                break;
            case "center_2x":
                self::UpdatePattern($styleObj, array(
                    (2.5 * $one), // line
                    (.5 * $one), // gap
                    (.5 * $one), // line
                    (.5 * $one)
                ));
                break;
            case "border":
                self::UpdatePattern($styleObj, array(
                    (.5 * $one), // line
                    (.25 * $one), // gap
                    (.5 * $one), // line
                    (.25 * $one),
                    1,
                    (.25 * $one)
                ));
                break;
            case "border_half":
                self::UpdatePattern($styleObj, array(
                    (.25 * $one), // line
                    (.125 * $one), // gap
                    (.25 * $one), // line
                    (.125 * $one),
                    0,
                    (.125 * $one)
                ));
                break;
            case "border_2x":
                self::UpdatePattern($styleObj, array(
                    (1.0 * $one), // line
                    (.5 * $one), // gap
                    (1 * $one), // line
                    (.5 * $one),
                    0,
                    (.5 * $one)
                ));
                break;
            case "phantom":
                self::UpdatePattern($styleObj, array(
                    (1.25 * $one), // line
                    (.25 * $one), // gap
                    (.25 * $one), // line
                    (.25 * $one), // gap
                    (.25 * $one), // line
                    (.25 * $one)
                ));
                break;
            case "phantom_2x":
                self::UpdatePattern($styleObj, array(
                    (2.5 * $one), // line
                    (.5 * $one), // gap
                    (.5 * $one), // line
                    (.5 * $one), // gap
                    (.5 * $one), // line
                    (.5 * $one)
                ));
                break;
            case 'line_phantom_half':
                self::UpdatePattern($styleObj, array(
                    (.625 * $one), // line
                    (.125 * $one), // gap
                    (.125 * $one), // line
                    (.125 * $one), // gap
                    (.125 * $one), // line
                    (.125 * $one)
                ));
                break;
            case "default":
            default:
                return;
                break;
        }
    }

    public static function SetStandardLine($symbolName, $size, $width, $ms_classObj, $color, $offsetx = 0, $offsety = 0, $lineCap = 'round', $opacity = 100, $asPolyOutline = false) {

        $r = $g = $b = 0;
        if (is_string($color)) {
            $color = \utils\ColorUtil::Web2RGB($color, $r, $g, $b);
        } else {
            list ($r, $g, $b) = $color;
        }

        $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
        $styleString = <<<LINE
STYLE
SYMBOL "line_default"
OFFSET $offsetx $offsety
SIZE $size
WIDTH $width
LINECAP $lineCap
END #STYLE
LINE;
        $style->updateFromString($styleString);

        $style->color->setRGB($r, $g, $b);

        return $style;
    }

    public static function SetComplexStroke($ms_classObj, $symbolObj, $size, HilightDriver $hilightDriver, $layerOffset, $asPolyOutline = false) {

        #$size = $size = \SymbolSize::GetOutlineThickness($size);
        $ini = \System::GetIni();
        $hilightingStage = $hilightDriver->GetHilightStage($layerOffset);
        $hilighting = $hilightingStage === HilightDriver::UNDERLAY;

        $opacity = $fr = $fg = $fb = $r = $g = $b = -1;

        $vlineNH = 10; // vertline symbol's "height" is 10
        $lineNW = 1; // vertline symbol's "width" is 1;

        $color = $hilightDriver->GetColor($layerOffset, 'stroke');
        $hilightDriver->SetStyleVars($hilightingStage, $r, $g, $b, $fr, $fg, $fb, $opacity);
        $opacity255 = 100 * 255 / 100;
        $hilightOffset = ($hilighting) ? ($size * 1.5) : 0;
        $outlineSize = ($hilighting) ? max(($size * 1.25), 3) : 0;
        $adjSize = $size + $outlineSize;
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
        $symbolName = $symbolObj->name;
        switch ($symbolName) {
            case 'complex_railroad':
                self::SetStandardLine($symbolName, $adjSize, $size, $ms_classObj, $color, 0, 0, 'square', $opacity, $asPolyOutline);
                #$gap = $size * 10; //($hilighting) ? 51 : 50;
                $gap = ($hilighting) ? 5 + $size * 6 : 5 + $size * 6;
                $styleString = <<<STYLE_STR
 STYLE
 LINECAP SQUARE               
 SYMBOL "vertline"
    ANGLE 90
    GAP -$gap
ANGLE AUTO
END # STYLE
STYLE_STR;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);

                $style->updateFromString($styleString);
                $style->size = 5 + $size * 4;
                $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                #$style->outlinewidth = ($hilighting) ? .75 : 0;

                $style->width = ($hilighting) ? (1.75 * $size) : $size;
                $style->color->setRGB($r, $g, $b);





                break;
            case 'complex_railroad_multi':
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, - $size, 'square', $opacity, $asPolyOutline);
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, $size, 'square', $opacity, $asPolyOutline);
                #$gap = $size * 10; //($hilighting) ? 51 : 50;
                $gap = ($hilighting) ? 5 + $size * 6 : 5 + $size * 6;
                $styleString = <<<STYLE_STR
 STYLE
 LINECAP SQUARE               
 SYMBOL "vertline"
    ANGLE 90
    GAP -$gap
ANGLE AUTO
END # STYLE
STYLE_STR;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);

                $style->updateFromString($styleString);
                $style->size = 5 + $size * 4;
                $style->outlinecolor->setRGB($r, $g, $b, $opacity255);
                #$style->outlinewidth = ($hilighting) ? .75 : 0;

                $style->width = ($hilighting) ? (1.75 * $size) : $size;
                $style->color->setRGB($r, $g, $b);

                $size = 2 * $size;
                break;
            case 'complex_railroad_in_street':
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 1, - 1.5 * $size, 'square', $opacity, $asPolyOutline);
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, 1.5 * $size, 'square', $opacity, $asPolyOutline);

                $vertSize = ($size <= \utils\SymbolSizes::SYMBOLSIZE_MEDIUM) ? ($size) : 13;

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

                        break;
                    default:
                        $vertSize = ($hilighting) ? 13 : 10;
                        $initialGap = ($hilighting) ? $adjSize : $adjSize;
                }
                $angle = ($hilighting) ? 0 : 0;
                $width = $adjSize;

                #var_dump($r,$g,$b);
                $styleString = <<<ARROW_END
STYLE
    SYMBOL "vertline_bold"
    ANGLE 90
    INITIALGAP $initialGap
    GAP -50
    SIZE $size
    WIDTH $width
    ANGLE AUTO
END # STYLE
ARROW_END;
                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString);
                $style->color->setRGB($r, $g, $b, $opacity255);
                #var_dump($style->convertToString());
                $size = $size;
                break;
            case 'complex_railroad_trunkline':
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, - $size, 'square', $opacity, $asPolyOutline);
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, $size, 'square', $opacity, $asPolyOutline);

                $styleString = <<<ARROW_END
STYLE
    SYMBOL "vertline"
    ANGLE 0
    GAP -50
    INITIALGAP 1
    ANGLE AUTO                    
    LINECAP square
END # STYLE
ARROW_END;
                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString);
                $style->size = $size * 2 + $hilightOffset;
                $style->width = $size * 2 + $hilightOffset;
                $style->color->setRGB($r, $g, $b);

                $size = 2 * $size;
                break;
            case 'complex_rail':
            case 'complex_nautical_single':
                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);

                $style->color->setRGB($r, $g, $b);
                $adjSize = $adjSize * 2;
                $style->set('width', $adjSize + 4); //$size + 4 + $hilightOffset);
                $style->set('size', $size + 4);
                $style->set('linecap', 'square');
                $style->set('opacity', 100); //$opacity;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                list ($r, $g, $b) = array(
                    254,
                    254,
                    254
                );
                if (!$hilighting) {
                    // list ($r, $g, $b) = $glowColor;
                    $style->color->setRGB($r, $g, $b);
                    $style->set('width', $adjSize + 2);
                    $style->set('size', $size + 2);
                    $style->set('linecap', 'square');
                    #$style->set('initialgap',(($hilighting) ? (-3 * $size) : 0));
                    self::UpdatePattern($style, array(
                        (6 * $size),
                        (6 * $size)
                    ));
                    $style->set('opacity', 100);
                    #$style->set('opacity', $opacity);
                    $size = $size + 4;
                }
                break;

            case 'complex_nautical_double':
                self::SetStandardLine($symbolName, ($size * 3 + (2 / $size)), ($size * 3 + (2 / $size)), $ms_classObj, $color, 0, 0, 'square', $opacity, $asPolyOutline);
                $patternOn = 5 * $size;
                $patternOff = 7 * $size;
                // $size = $size * 1.5;

                $styleString1 = <<<STYLE_STRING
STYLE
SIZE $size
WIDTH $size
INITIALGAP 0
LINECAP square
PATTERN $patternOn $patternOff 
END #STYLE
STYLE_STRING;
                $patternOn = 6 * $size + + $hilightOffset;
                $patternOff = 6 * $size + + $hilightOffset;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString1);
                list ($r, $g, $b) = array(
                    254,
                    254,
                    254
                );
                $style->color->setRGB($r, $g, $b);
                $style->set('offsetx', 0);
                $style->set('offsety', - $size - $hilightOffset + ($size / 2));
                $style->set('width', $size);
                // $style->set('opacity',$opacity);

                $styleString2 = str_replace('INITIALGAP 0', 'INITIALGAP ' . (6 * $size), $styleString1);
                $styleString2 .= "\n" . 'END #STYLE';
                $styleString2 = str_replace('-', '', $styleString2);
                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString2);
                $style->set('offsety', $size + $hilightOffset - $size / 2);
                $style->color->setRGB($r, $g, $b);

                $size = $size * 2;
                break;
            case 'complex_square_tooth':
            case 'complex_nautical_single_2':
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, 0, 'square', $opacity, $asPolyOutline);

                #$hilightOffset = ($hilighting) ? 2.5 : 0;
                $gap = 12 * $size;
                #list ($fr, $fg, $fb) = $color;
                $width = $size * (($hilighting) ? 1.25 : 1);
                $size2 = 3 * $size;
                //$size2 / 2; //$size2/4 - 1;//
                $initGap = ($hilighting) ? ($gap / 2) - (1.125 * $size) / 2 + 1 : $gap / 2;
                $styleString1 = <<<STYLE_STRING
STYLE
SYMBOL "rectangle_filled"
GEOMTRANSFORM "center"
INITIALGAP $initGap
GAP -$gap
WIDTH $width
SIZE $size2
ANGLE auto
END #STYLE
STYLE_STRING;
                // $patternOn = 6 * $size;
                // $patternOff = 6 * $size;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString1);
                if ($hilighting) {
                    $style->outlinecolor->setRGB($r, $g, $b);
                    $style->set('outlinewidth', 1.5);
                }
                // $style->set('gap',null);
                self::UpdatePattern($style, array(
                    20,
                    10,
                    20,
                    10
                ));

                $style->color->setRGB($r, $g, $b);
                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);
                // END


                break;
            case 'complex_square_tooth_hollow':
            case 'complex_nautical_single_3':
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, 0, 'square', $opacity, $asPolyOutline);

                #$hilightOffset = ($hilighting) ? 2.5 : 0;
                $gap = 12 * $size;
                #list ($fr, $fg, $fb) = $color;
                $width = $size * (($hilighting) ? 1.125 : 1);
                $size2 = 3 * $size;
                $initGap = ($hilighting) ? ($gap / 2) - (1.125 * $size) / 2 + 1 : $gap / 2;
                $styleString1 = <<<STYLE_STRING
STYLE
SYMBOL "rectangle_hollow_left"
GEOMTRANSFORM "center"
INITIALGAP $initGap
GAP -$gap
WIDTH $width
SIZE $size2
ANGLE auto
END #STYLE
STYLE_STRING;
                // $patternOn = 6 * $size;
                // $patternOff = 6 * $size;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString1);
                if ($hilighting) {
                    $style->outlinecolor->setRGB($r, $g, $b);
                    $style->set('outlinewidth', 1.5);
                }
                // $style->set('gap',null);
                self::UpdatePattern($style, array(
                    20,
                    10,
                    20,
                    10
                ));

                $style->color->setRGB($r, $g, $b);
                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);

                break;
            case 'complex_nautical_single_4':
                self::SetStandardLine($symbolName, $adjSize, $adjSize, $ms_classObj, $color, 0, 0, 'square', $opacity, $asPolyOutline);
                $gap = 12 * $size;
                $width = $size * (($hilighting) ? 1.25 : 1);
                $size2 = 3 * $size;
                $initGap = ($hilighting) ? ($gap / 2) - (1.125 * $size) / 2 + 1 : $gap / 2;

                $width = $size * (($hilighting) ? 1.125 : 1);
                $size2 = 3 * $size;
                $initGap = ($hilighting) ? ($gap / 2) - (1.125 * $size) / 2 + 1 : $gap / 2;

                $styleString1 = <<<STYLE_STRING
STYLE
SYMBOL "rectangle_filled"
GEOMTRANSFORM "center"
INITIALGAP $initGap
GAP -$gap
WIDTH $width
SIZE $size2
ANGLE auto
END #STYLE
STYLE_STRING;
                // $patternOn = 6 * $size;
                // $patternOff = 6 * $size;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString1);
                if ($hilighting) {
                    #$style->outlinecolor->setRGB($r, $g, $b);
                    #$style->set('outlinewidth', 1.5);
                }
                // $style->set('gap',null);
                self::UpdatePattern($style, array(
                    20,
                    10,
                    20,
                    10
                ));

                $style->color->setRGB(255, 255, 255);
                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);
                // END
                $styleString1 = <<<STYLE_STRING
STYLE
SYMBOL "rectangle_hollow_left"
GEOMTRANSFORM "center"
INITIALGAP $initGap
GAP -$gap
WIDTH $width
SIZE $size2
ANGLE auto
END #STYLE
STYLE_STRING;
                // $patternOn = 6 * $size;
                // $patternOff = 6 * $size;

                $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
                $style->updateFromString($styleString1);
                if ($hilighting) {
                    $style->outlinecolor->setRGB($r, $g, $b);
                    $style->set('outlinewidth', 1.5);
                }
                // $style->set('gap',null);
                self::UpdatePattern($style, array(
                    20,
                    10,
                    20,
                    10
                ));
                $size2 = $size2 * 1;
                $style->color->setRGB($r, $g, $b);
                $style->set('offsetx', 0);
                $style->set('offsety', 0); // $size2/2.0);
                break;
            /*


              self::SetStandardLine($symbolName, $size, $size + $hilightOffset, $ms_classObj, $color, 0, 0, 'square', $opacity);

              $gap = 12 * $size;
              list ($r, $g, $b) = $color;
              $size2 = 3 * $size + $hilightOffset;
              $initGap = ($hilighting) ? $gap / 2.75 : $gap / 2;
              $styleString1 = <<<STYLE_STRING
              STYLE
              SYMBOL "rectangle_filled"
              GEOMTRANSFORM "center"
              INITIALGAP $initGap
              GAP -$gap
              WIDTH $size
              SIZE $size2
              ANGLE auto
              END #STYLE
              STYLE_STRING;
              // $patternOn = 6 * $size;
              // $patternOff = 6 * $size;

              $style = \ms_newStyleObj($ms_classObj);
              $style->updateFromString($styleString1);
              // $style->set('gap',null);
              self::UpdatePattern($style, array(
              20,
              10,
              20,
              10
              ));
              if ($hilighting) {
              list($r2, $g2, $b2) = $color;
              $style->color->setRGB($r2, $g2, $b2);
              } else {
              $style->color->setRGB(255, 255, 255);
              }
              $style->set('offsetx', 0);
              $style->set('offsety', 0); // $size2/2.0);

              $gap = 12 * $size;
              list ($r, $g, $b) = $color;
              $size2 = 3 * $size;
              $styleString1 = <<<STYLE_STRING
              STYLE
              SYMBOL "rectangle_hollow"
              GEOMTRANSFORM "center"
              GAP -$gap
              WIDTH $size
              SIZE $size2
              ANGLE auto
              END #STYLE
              STYLE_STRING;
              // $patternOn = 6 * $size;
              // $patternOff = 6 * $size;

              $style = \ms_newStyleObj($ms_classObj);
              $style->updateFromString($styleString1);
              // $style->set('gap',null);
              self::UpdatePattern($style, array(
              20,
              10,
              20,
              10
              ));

              $style->color->setRGB($r, $g, $b);
              $style->set('offsetx', 0);
              $style->set('offsety', 0); // $size2/2.0);

              break;
             */
            case 'complex_arrow_end':
                // self::SetStandardLine($symbolName, $size, $ms_classObj, $color, 0, 0, 'square', $opacity);
                self::AddArrow(self::ARROW_END, $size, $ms_classObj, $hilightDriver, $layerOffset);
                break;
            case 'complex_arrow_end_rev':

                // self::SetStandardLine($symbolName, $size, $ms_classObj, $color, 0, 0, 'square', $opacity);
                self::AddArrow(self::ARROW_END_REV, $size, $ms_classObj, $hilightDriver, $layerOffset);
                break;

            case 'complex_arrow_start':

                // self::SetStandardLine($symbolName, $size, $ms_classObj, $color, 0, 0, 'square', $opacity);
                self::AddArrow(self::ARROW_START, $size, $ms_classObj, $hilightDriver, $layerOffset);
                break;

            case 'complex_arrow_start_rev':

                // self::SetStandardLine($symbolName, $size, $ms_classObj, $color, 0, 0, 'square', $opacity);
                self::AddArrow(self::ARROW_START_REV, $size, $ms_classObj, $hilightDriver, $layerOffset);

                break;
            case "complex_arrow_start_end":
                // self::SetStandardLine($symbolName, $size, $ms_classObj, $color, 0, 0, 'square', $opacity);
                self::AddArrow(self::ARROW_BOTH, $size, $ms_classObj, $hilightDriver, $layerOffset);
                break;
            case "complex_arrow_middle_right":

                // self::SetStandardLine($symbolName, $size, $ms_classObj, $color, 0, 0, 'square', $opacity);
                self::AddArrow(self::ARROW_MIDDLE_RIGHT, $size, $ms_classObj, $hilightDriver, $layerOffset);
                break;
            case "complex_arrow_middle_left":

                // self::SetStandardLine($symbolName, $size, $ms_classObj, $color, 0, 0, 'square', $opacity);
                self::AddArrow(self::ARROW_MIDDLE_LEFT, $size, $ms_classObj, $hilightDriver, $layerOffset);
                break;
        }
        return $size;
    }

    public static function SetSimpleStroke($ms_classObj, $symbolObj, $size, HilightDriver $hilightDriver, $layerOffset, $asPolyOutline = false) {

        #$size = \SymbolSize::GetOutlineThickness($size);
        $hilightingStage = $hilightDriver->GetHilightStage($layerOffset);
        $hilightSize = $size;
        switch ($hilightingStage) {
            case HilightDriver::UNDERLAY:
                $hilightSize = $size + 5;
                break;
        }
        $hilightDriver->SetStyleVars($hilightingStage, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);

        $style = self::CreateNewStyle($ms_classObj, $asPolyOutline);
        $style->color->setRGB($sr, $sg, $sb, 255);
        #$hilightOffset = ($hilightingAdj) ? 5 : 0;
        self::SetPattern($symbolObj, $style, $size);
        $style->set('width', $hilightSize);
        $style->set('size', $size);
        return $size;
        #$style->opacity = $xpacity;
    }

    public static function CreateNewStyle($ms_classObj, $asPolyOutline = false) {
        $style = new \styleObj($ms_classObj);
        if ($asPolyOutline === true) {
            Polygon::Linify($style);
        }
        return $style;
    }

    public static function SetLine($mapObj, $msClassObj, $symbolName, $symbolSize, HilightDriver $hilightDriver, $layerOffset) {
        $symbolObj = $mapObj->getSymbolObjectById(ms_newSymbolObj($mapObj, $symbolName));

        $isComplex = stripos($symbolName, 'complex_') === 0;
        $size = $symbolSize;
        $size = \SymbolSize::GetOutlineThickness($size);
        if ($isComplex) {
            self::SetComplexStroke($msClassObj, $symbolObj, $size, $hilightDriver, $layerOffset);
        } else {
            self::SetSimpleStroke($msClassObj, $symbolObj, $size, $hilightDriver, $layerOffset);
        }
    }

}
