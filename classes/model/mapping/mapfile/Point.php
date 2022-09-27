<?php

namespace model\mapping\mapfile;

use utils\ParamUtil;
use symbols\Symbol;
use utils\ColorUtil;
use utils\SymbolSizes;
use \model\mapping\HilightDriver;

class Point {

    private $label;

    public static function SetLabel($label) {
        $label->set('anglemode', \MS_FALSE);
        $label->set('minfeaturesize', 0);
        // $label->set('force', \MS_TRUE);
    }

    public static function SetHilightedSymbol($innerOuter, $map, $class, $symbol, $layerInfo, HilightDriver $hilightDriver, $layerOffset, $outlineOnly) {
        $hilightStage = $hilightDriver->GetHilightStage($layerOffset);

        $hilighting = $hilightStage === HilightDriver::UNDERLAY;
        $symbolObj = new \symbolObj($map, $symbol);

        if ($outlineOnly)
            $opacity = 0;


        // $size = \SymbolSize::XXLARGE;
        $inSize = $layerInfo->symbol_size;

        $size = min($inSize, \SymbolSize::XXLARGE);
        list ($size1, $size2) = \SymbolSize::GetSymbolSizes($layerInfo->symbol, $size);
        $sr = $sg = $sb = $fr = $fg = $fb = - 1;


        list ($outerSize, $innerSize) = \SymbolSize::GetSymbolSizes($layerInfo->symbol, $inSize);

        switch ($hilightStage) {
            case HilightDriver::UNDERLAY:

                $symbolNameRoot = 'point_' . $layerInfo->symbol;
                switch ($symbolNameRoot) {
                    case 'point_tent':
                        $symbolNameRoot = 'point_triangle';
                    case 'point_circlex_fnt':
                        $symbolNameRoot = 'point_default';
                }

                $hilightDriver->SetStyleVars(HilightDriver::UNDERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);
                $fill = ms_newStyleObj($class);

                $symbolName = $symbolNameRoot;
                $fill->set('size', $outerSize * 1.732);
                if (stripos($symbolName, '_fnt') === -1) {
                    $symbolName = "{$symbolNameRoot}_filled";
                } else {
                    $fill->outlinecolor->setRGB($sr, $sg, $sb);
                    #$fill->set('outlinewidth',$size);
                    #$fill->set('size', $outerSize *1.5);
                }
                $fill->set('symbolname', "$symbolName");
                $fill->color->setRGB($fr, $fg, $fb, 255);
                Symbol::SetPattern($symbolObj, $fill);

                //$fill->opacity = $xpacity;

                break;
            case \model\mapping\HilightDriver::OVERLAY:

                $symbolObj = new \symbolObj($map, $symbol);
                Symbol::SetPattern($symbolObj);

                $size = $layerInfo->symbol_size;

                $sr = $sg = $sb = $fr = $fg = $fb = - 1;
                $hilightDriver->SetStyleVars(HilightDriver::OVERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);
                $xpacity = 255;

                list ($size1, $size2) = \SymbolSize::GetSymbolSizes($layerInfo->symbol, $size);

                #$style = ms_newStyleObj($class);
                #$style->set('size', $size1);

                $style2 = ms_newStyleObj($class);
                #ColorUtil::Web2RGB($stroke_color, $sr, $sg, $sb);
                $style2->outlinecolor->setRGB($fr, $fg, $fb);
                $style2->color->setRGB($sr, $sg, $sb);

                $symbolParts = explode('_', ($layerInfo->symbol));
                if (array_pop($symbolParts) == "fnt") {
                    $style2->set('symbolname', "point_{$layerInfo->symbol}");
                } else {
                    $style2->set('symbolname', "point_{$layerInfo->symbol}_filled");
                }

                $style2->set('size', $size1);
                /*
                  if ($outlineOnly) {
                  $xpacity255 = 0;
                  }
                  $hilightDriver->SetStyleVars(HilightDriver::OVERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);
                  $xpacity255 = 255; // * $xpacity / 100;
                  $outerHilight = ms_newStyleObj($class);
                  $outerHilight->set('size', $innerSize);
                  $outerHilight->set('symbolname', "point_{$layerInfo->symbol}_filled");
                  $outerHilight->outlinecolor->setRGB($sr, $sg, $sb, $xpacity255);
                  $outerHilight->color->setRGB($fr, $fg, $fb, $xpacity255);
                  Symbol::SetPattern($symbolObj, $outerHilight);
                  #$outerHilight->set('size', $size);
                  // $outerHilight->opacity = $xpacity;
                 */
                break;
        }

        /*
         * $stroke = ms_newStyleObj($class);
         * $stroke->set('size', $size1+2);
         * $stroke->set('symbolname', "point_{$layerInfo->symbol}_filled");
         * ColorUtil::Web2RGB($stroke_color,$sr,$sg,$sb);
         * $stroke->outlinecolor->setRGB($sr,$sg,$sb);
         * Symbol::SetPattern($symbolObj,$stroke);
         * $stroke->set('width', 2);
         * $stroke->opacity = $glopacity;
         *
         *
         * $innerHilight = ms_newStyleObj($class);
         * $innerHilight->set('symbolname', "point_{$layerInfo->symbol}_filled");
         * Symbol::SetPattern($symbolObj,$innerHilight);
         * ColorUtil::Web2RGB($fill_color, $sr, $sg, $sb);
         * $innerHilight->outlinecolor->setRGB($sr, $sg, $sb);
         * //$stroke->color->setRGB($fr,$fg,$fb);
         * $innerHilight->set('size', $size1-2);
         * $innerHilight->set('width', 2);
          $innerHilight->opacity = $glopacity;
         */

        return $symbolObj;

        /*
         * else {
         * ColorUtil::Web2RGB($filterColor, $fr, $fg, $fb);
         * $style2-
         *
         * }
         */
    }

    public static function SetSymbol($map, $class, $symbol, $layerInfo, HilightDriver $hilightDriver, $layerOffset) {//$stroke_color, $fill_color, $opacity) {
        $symbolObj = new \symbolObj($map, $symbol);
        Symbol::SetPattern($symbolObj);

        $size = $layerInfo->symbol_size;

        $sr = $sg = $sb = $fr = $fg = $fb = - 1;
        $hilightDriver->SetStyleVars(HilightDriver::NATURAL, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);


        list ($size1, $size2) = \SymbolSize::GetSymbolSizes($layerInfo->symbol, $size);
        
        #$style = ms_newStyleObj($class);
        #$style->set('size', $size1);

        $style2 = ms_newStyleObj($class);
        #ColorUtil::Web2RGB($stroke_color, $sr, $sg, $sb);
        $style2->outlinecolor->setRGB($sr, $sg, $sb);
        $style2->color->setRGB($fr, $fg, $fb);

        $symbolParts = explode('_', ($layerInfo->symbol));
        if (array_pop($symbolParts) == "fnt") {
            $style2->set('symbolname', "point_{$layerInfo->symbol}");
        } else {
            $style2->set('symbolname', "point_{$layerInfo->symbol}_filled");
        }

        $style2->set('size', $size1);
        return;
        /* ColorUtil::WEb2RGB($fill_color, $fr, $fg, $fb);

          // if (! $filtering) {
          $style2 = ms_newStyleObj($class);
          $style2->color->setRGB($fr, $fg, $fb);
          $style2->set('opacity', $opacity);
          $style2->set('symbolname', "point_{$symbol}_filled");
          $style2->set('size', $size1);
          // }
         *
         */
        return $symbolObj;
    }

}

?>