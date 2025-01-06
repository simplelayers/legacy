<?php

namespace model\mapping\mapfile;

use utils\ParamUtil;
use symbols\Symbol;
use utils\ColorUtil;
use utils\SymbolSizes;
use model\mapping\HilightDriver;

class Point {

    private $label;

    public static function SetLabel($label) {
        $label->set('anglemode', \MS_FALSE);
        $label->set('minfeaturesize', 0);
        $label->set('force', \MS_TRUE);
    }

    public static function SetFilteredSymbol($innerOuter, $map, $class, $symbol, $layerInfo, HilightDriver $hilightDriver, $outlineOnly) {
        $symbolObj = new \symbolObj($map, $symbol);
        $sr = $sg = $sb = $fr = $fg = $fb = - 1;
        $entrySize = $layerInfo->symbol_size;
        $entryStrokeColor = $layerInfo->stroke_color;
        if (is_null($entryStrokeColor))
            $entryStrokeColor = 'trans';

        if (($entryStrokeColor == 'trans') && $outlineOnly) {
            $opacity = "trans";
        }
        $size = \SymbolSize::GetOutlineThickness($entrySize);

        $isComplex = stripos($symbol, 'complex_') === 0;
        $isOutline = stripos($symbol, 'polygon_outline_') === 0;
        $isStandard = !($isComplex || $isOutline);

        if ($outlineOnly) {
            ColorUtil::Web2RGB('trans', $fr, $fg, $fb);
        }
        #ColorUtil::Web2RGB($stroke_color,$sr,$sg,$sb);

        $size = ($isStandard) ? $size - 1 : $size;
        if ($innerOuter === 'outer') {
            $hilightDriver->SetStyleVars(\model\mapping\HilightDriver::UNDERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);
            $xpacity255 = 255 * $xpacity / 100;
            /**
             * $outerHilight \styleObj
             */
            $outerHilight = ms_newStyleObj($class);
            // if($filtering) {$sr=$sg=$sb=0;}
        
            $outerHilight->outlinecolor->setRGB($fr, $fg, $fb, $xpacity255);
            $outerHilight->set('width', 3);
            $outerHilight->set('opacity', $xpacity);
            $outerHilight->updateFromString("geomtransform (buffer([shape],$entrySize))");
        }
        if ($innerOuter === 'inner') {
            $hilightDriver->SetStyleVars(\model\mapping\HilightDriver::OVERLAY, $sr, $sg, $sb, $fr, $fg, $fb, $xpacity);
            $xpacity255 = 255 * $xpacity / 100;
            $innerHilight = ms_newStyleObj($class);
            $innerHilight->outlinecolor->setRGB($fr, $fg, $fb, $xpacity255);
            $innerHilight->set('opacity', $xpacity);
            $innerHilight->set('width', 2);
            $innerHilight->updateFromString("geomtransform (buffer([shape], -$entrySize))");
            $innerHilight->set('opacity', $opacity);
            $fill = ms_newStyleObj($class);
            $fill->updateFromString("geomtransform (buffer([shape], -$size+1))");
            $fill->color->setRGB($fr, $fg, $fb, $xpacity255);
        }

        return $symbolObj;
    }

    /* public static function SetFilteredSymbol($map, $class, $symbol, $layerInfo, $stroke_color, $fill_color, $opacity, $glopacity, $outlineOnly)
      {
      $opacity255 = ($opacity * 255);
      $glopacity255 = ($glopacity * 255);
      $symbolObj = new \symbolObj($this->map, $symbol);
      $sr = $sg = $sb = $fr = $fg = $fb = - 1;
      $entrySize = $layerInfo->symbol_size;
      $entryStrokeColor = $layerInfo->stroke_color;
      if(is_null($entryStrokeColor)) $entryStrokeColor = 'trans';

      if(($entryStrokeColor ==  'trans') && $outlineOnly) {
      $opacity = "trans";
      }
      $size = \SymbolSize::GetOutlineThickness($entrySize);

      $isComplex = stripos($symbol, 'complex_') === 0;
      $isOutline = stripos($symbol, 'polygon_outline_') === 0;
      $isStandard = ! ($isComplex || $isOutline);

      if ($outlineOnly) {
      ColorUtil::Web2RGB('trans', $fr, $fg, $fb);
      }
      ColorUtil::Web2RGB($stroke_color,$sr,$sg,$sb);

      $size = ($isStandard) ? $size - 1 : $size;
      $outerHilight = ms_newStyleObj($class);
      // if($filtering) {$sr=$sg=$sb=0;}
      $outerHilight->outlinecolor->setRGB($fr, $fg, $fb,$glopacity255);
      $outerHilight->set('width', 3);
      $outerHilight->set('opacity', $glopacity);
      $outerHilight->updateFromString("geomtransform (buffer([shape],$entrySize))");

      $innerHilight = ms_newStyleObj($class);
      $innerHilight->outlinecolor->setRGB($fr, $fg, $fb,$opacity255);
      $innerHilight->set('opacity', $glopacity);
      $innerHilight->set('width', 2);
      $innerHilight->updateFromString("geomtransform (buffer([shape], -$entrySize))");
      $innerHilight->set('opacity', $opacity);

      ColorUtil::Web2RGB($fill_color, $fr, $fg, $fb);

      ColorUtil::Web2RGB($fill_color, $fr, $fg, $fb);
      $fill = ms_newStyleObj($class);
      $fill->updateFromString("geomtransform (buffer([shape], -$size+1))");
      ColorUtil::Web2RGB($fill_color, $fr, $fg, $fb);
      $fill->color->setRGB($fr, $fg, $fb);
      $fill->set('opacity', $opacity);
      return $symbolObj;
      } */

    /*public static function SetSymbol($map, $class, $symbol, $layerInfo, HilightDriver $hilightDriver) {


        $symbolObj = new \symbolObj($map, $symbol);

        $sr = $sg = $sb = $fr = $fg = $fb = - 1;
        $hilightDriver->SetStyleVars(HilightDriver::NATURAL, $sr, $sg, $sb, $fr, $fg, $fb, $opacity);

        $entrySize = $layerInfo->symbol_size;
        
        $isComplex = stripos($symbol, 'complex_') === 0;
        $isOutline = stripos($symbol, 'polygon_outline_') === 0;
        $isStandard = !($isComplex || $isOutline);
        
        if ($isComplex) {
            $symbolName = $entry->symbol;
            // var_dump($symbolName);
            // $style = ms_newStyleObj($class);
            // $style->color->setRGB($fr, $fg, $fb);
            if ($outlineOnly) {
                ColorUtil::Web2RGB($glowColor, $fr, $fg, $fb);
         
                ColorUtil::Web2RGB($entry->color, $sr, $sg, $sb);
            }
            $newStyle = Polygon::SetComplexSymbol($symbol, $size, $class, array(
                        $sr,
                        $sg,
                        $sb
                            ), array(
                        $fr,
                        $fg,
                        $fb
                            ), $outlineOnly, $this->map);
        } elseif ($isOutline) {

            // $symbol = substr($symbol, strlen('polygon_outline_'));
            // $symbol = 'line_' . $symbol;
            // var_dump($symbol);
            $style = ms_newStyleObj($class);
            if ($outlineOnly)
                ColorUtil::Web2RGB($glowColor, $fr, $fg, $fb);
            $style->color->setRGB($fr, $fg, $fb);
            // $style->set ( 'symbolname', $symbol );
            $style = ms_newStyleObj($class);
            if ($outlineOnly)
                ColorUtil::Web2RGB($glowColor, $sr, $sg, $sb);
            $style->outlinecolor->setRGB($sr, $sg, $sb);

            $symbolObj->set('name', $symbol);

            Polygon::SetPattern($symbolObj, $style, $entrySize);
            $style->set('width', $size);
            $style->set('size', $size);
            $style->updateFromString("geomtransform (buffer([shape], 1))");
        } else {

            $symbolObj->set('name', $symbol);
            // var_dump($symbol);
            // var_dump("stroke color",$stroke_color);
            ColorUtil::Web2RGB($stroke_color, $sr, $sg, $sb);
            // var_dump($sr,$sg,$sb);
            // var_dump("fill color",$fill_color);
            ColorUtil::Web2RGB($fill_color, $fr, $fg, $fb);

            $stroke = ms_newStyleObj($class);
            $stroke->set('width', $size);
            $stroke->outlinecolor->setRGB($sr, $sg, $sb);
            $stroke->set('size', $size);
            ColorUtil::Web2RGB('trans', $fr, $fg, $fb);
            // var_dump($fr, $fg, $fb);
            $stroke->color->setRGB($fr, $fg, $fb);
            $stroke->updateFromString("geomtransform (buffer([shape], -2))");

            // var_dump($fr, $fg, $fb);
            if (!$filtering) {
                $fill = ms_newStyleObj($class);

                $fill->set('size', $size);
                // var_dump($stroke_color,$fill_color);
                $fill->color->setRGB($fr, $fg, $fb);

                if ($symbol != 'polygon_default') {
                    $fill->set('symbolname', $symbol);
                }
                if (!Polygon::SetPattern($symbolObj, $fill, $entrySize)) {
                    $fill->set('width', $entrySize);
                }

                $fill->updateFromString("geomtransform (buffer([shape], -2))");
            }
        }
    }*/

}

?>