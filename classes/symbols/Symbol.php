<?php

namespace symbols;

class Symbol {

    const SYMBOL_DEFAULT = 'default';
    const SYMBOL_DIAMOND = 'diamond';
    const SYMBOL_HOUSE = 'house';
    const SYMBOL_SQUARE = 'square';
    const SYMBOL_STAR = 'star';
    const SYMBOL_TENT = 'tent';
    const SYMBOL_TRIANGLE = 'triangle';

    public static function GetSymbolSize($symbol, $size) {
        $firstChar = substr($symbol, 0, 1);
        $firstChar = strtoupper($firstChar);
        $symbol = $firstChar . substr($symbol, 1);
        eval('$size = symbols\\' . $symbol . '::$' . strtoupper($size) . ';');
        return $size;
    }

    public static function SetPattern($symbolObj, $styleObj = null) {

        $symbolName = "{$symbolObj->name}";

        if (stripos($symbolName, 'line_') === 0) {
            Lines::SetPattern($symbolObj, $styleObj);
            return;
        } elseif (stripos($symbolName, 'polygon_') === 0) {
            Polygon::SetPattern($symbolObj, $styleObj);
            return;
        } elseif (stripos($symbolName, 'point_') === 0) {

            $parts = explode('_', $symbolName);
            $type = $parts[1];

            switch ($type) {
                case 'star':
                    Star::SetPattern($symbolObj);
                    break;
                case 'diamond':
                    Diamond::SetPattern($symbolObj, $styleObj);
                    break;
                case 'house':
                    House::SetPattern($symbolObj, $styleObj);
                    break;
                case 'square':
                    Square::SetPattern($symbolObj, $styleObj);
                    break;
                case 'tent':
                    Tent::SetPattern($symbolObj, $styleObj);
                    break;
                case 'triangle':
                    Triangle::SetPattern($symbolObj, $styleObj);
                    break;
            }
            return;
        }
        return;
    }

}

?>