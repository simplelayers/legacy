<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5\virtual;

use GeomTypes;
use layer_utils\LayerUtils;
use LayerTypes;
use model\classifcation\Fonts;
use model\mapping\Labels;
use utils\ParamUtil;
use function GuzzleHttp\json_decode;

/**
 * Description of VirtualLayer
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class VirtualLayer {

//put your code here
    public $hilighting = false;
    public $data = '';
    public $filter_gids = '';
    public $filter_color = "FFFF00";
    public $filter_field = "gid";
    public $opacity = 1.0;
    public $glopacity = 0.0;
    public $geom_type = GeomTypes::POLYGON;
    public $type = LayerTypes::VIRUTAL;
    public $labelStyle;
    public $staticLabelStyle;
    public $proxyLayer;
    public $proxyPLayer;
    public $minscale;
    public $buffer = 0;
    public $attributeInfo = ['gid' => 'integer'];
    public $name = 'SLV Virtual Layer';
    public $labelsOn = false;
    private $classification = [];

    public function __construct($params = []) {
        $criteria = ParamUtil::Get($params, 'criteria');

        $labelsOn = ParamUtil::GetBoolean($params, 'labels', false);
        $this->labelsOn = $labelsOn;
        if (!is_null($criteria)) {
            $this->criteria = $criteria;
        }
        $labelStyle = ParamUtil::Get($params, 'labelStyle', Labels::GetDefault());
        if (!is_array($labelStyle)) {
            $labelStyle = json_decode($labelStyle, true);
        }
        if ($labelStyle) {
            $this->labelStyle = $labelStyle;
        }
        $label = ParamUtil::Get($params,'label');
        if($label) { $this->staticLabel = $label; };
        $color = ParamUtil::Get($params, 'color');
        if (!is_null($color)) {
            $this->ParseColorString($color);
            list($this->hilighting) = ParamUtil::GetBoolean($params,'hilight');
        }
    }

    public function __get($what) {
        $method = strtolower('__get_' . $what);
        if (\method_exists($this, $method)) {
            return $this->$method();
        }
    }

    public function __set($what, $value) {
        $method = strtolower('__set_' . $what);
        if (\method_exists($this, $method)) {
            return $this->$method($value);
        }
    }

    public function __get_geomtype() {
        switch ($this->type) {
            case \LayerTypes::RASTER:
                return \GeomTypes::RASTER;
            case $this->type == \LayerTypes::WMS:
                return \GeomTypes::WMS;
            case $this->type == \LayerTypes::ODBC:
                return \GeomTypes::POINT;
            case $this->type == \LayerTypes::COLLECTION:
                return \GeomTypes::COLLECTION;
            default:
                return $this->geom_type;
        }
    }

    public function __get_geomtypestring() {
        $gType = \intval($this->geom_type);
        if (\is_int($gType)) {
            return LayerUtils::ToGeomTypeString($this);
        }
        $layerTypes = LayerTypes::GetEnum();
        return $layerTypes[$this->type];
    }

    public function __get_geomtyperaw() {
        switch ($this->type) {
            case \LayerTypes::RASTER:
                return \GeomTypes::RASTER;
            case \LayerTypes::WMS:
                return \GeomTypes::WMS;
            case \LayerTypes::ODBC:
                return \GeomTypes::POINT;
            case \LayerTypes::COLLECTION:
                return \GeomTypes::COLLECTION;
            default:
                return 'GEOMETRY';
        }
    }

    public function __get_colorscheme() {

        $scheme = new class() {

            function getAllEntries() {
                return $this->parent->GetColorSchemeEntries(false);
            }

            function getUnique() {
                return $this->parent->GetColorshemeEntries(true);
            }
        };
        $scheme->parent = $this;

        return $scheme;
    }

    public function GetColorSchemeEntries($unique = false) {

        if ($this->proxyLayer) {
            if ($unique) {
                return $this->proxyLayer->colorscheme->getAllEntries();
            } else {
                return $this->proxyLayer->colorscheme->getUniqueCriteria();
            }
        }

        if (!$unique) {
            // presumes $this->criteria is an array similar to the $info array
            // built below.
            return $this->criteria;
        }
        // simplification of colorscheme->getUniqueCriteria
        $info = array();
        $size = 0;

        foreach ($this->classification as $entry) {
            // get the enr
            list ($c1, $c2, $c3) = array(
                $entry['criteria1'],
                $entry['criteria2'],
                $entry['criteria3'],
            );
            $c = $c1 . $c2 . $c3;
            if ($c == "") {
                $c1 = $c2 = $c3 = "";
                $c = "default";
            }
            if (isset($info[$c])) {
                if (isset($info[$c]['size'])) {
                    // updates maxsize if size already set
                    $info[$c]['maxSize'] = max($info[$c]['size'], $entry['size']);
                } else {
                    $info[$c]['size'] = $entry->size;
                }
                $info[$c]['description'] = $entry['description'];
                continue;
            }
            $size = $entry->size;
            $cInfo = [
                'c1' => $c1,
                'c2' => $c2,
                'c3' => $c3,
                'description' => $entry['description'],
                'maxSize' => $size
            ];
            $info[$c] = $cInfo;
        }
        return $info;
    }

    private function __get_criteria() {
        return $this->classification;
    }

    private function __set_criteria($criteria) {

        if (!is_array($criteria)) {
            $criteria = \json_decode($criteria, true);
        }
        foreach ($criteria as $criterion) {
            if (isset($criterion['labelStyle'])) {
                if ($criterion['useLabelStyle'] === true) {
                    $labelStyle = \json_encode($criterion['labelStyle']);
                    break;
                }
            }
        }
        $this->scheme = $this->colorscheme;
        $this->classification = $criteria;
    }

    public function GetLabels($defaultPosition = null) {
        if ($this->labelStyle) {
            return $this->labelStyle;
        }
        if ($this->proxyPLayer) {
            return Labels::GetLabelsFromPLayer($this->proxyPLayer, $defaultPosition);
        }
        if ($this->proxyLayer) {
            return Labels::GetLabelsFromLayer($this->proxyPLayer, $defaultPosition);
        }
        return null;
    }

    public function getAttributes() {
        return $this->attributeInfo;
    }

    public function __set_staticLabel($label) {
        $this->staticLabelStyle = $this->labelStyle;

        $fontInfo = new Fonts(Fonts::MODE_PX);
        $fontName = $this->staticLabelStyle['font'];
        $fontSize = $this->staticLabelStyle['size'];
        $offsetX = '' . $this->staticLabelStyle['offsetx'];
        $offsetY = '' . $this->staticLabelStyle['offsety'];

        switch ($this->staticLabelStyle['align']) {
            case 'left':
                $offsetX .= '+1lw';
                break;
            case 'right':
                $pos = 'cl';
                $offsetX .= '-1lw';
                break;
            case 'center':
            default:
                $pos = 'cc';
                break;
        }


        $loffsetY = $offsetY;
        $position = $this->staticLabelStyle['position'];
        switch ($position) {
            case 'ul':
                $rot = -45;
                $lrot = 45;
                $loffsetY .= '1.5lh';
                break;
            case 'uc':
                $rot = 0;
                $lrot = 0;
                $loffsetY .= '1.5lh';
                break;
            case 'ur':
                $rot = 45;
                $lrot = 45;
                $loffsetY .= '1.5lh';
                break;
            case 'cl':
                $rot = 90;
                $loffsetY .= '1.5lh';
                $lrot = 90;
                break;
            default:
            case 'auto':
            case 'cc':
                $rot = 0;
                $lrot = 0;
                break;
            case 'cr':
                $rot = -90;
                $loffsetY .= '1.5lh';
                $lrot = -90;
                break;
            case 'll':
                $rot = -135;
                $loffsetY .= '-1.5lh';
                $lrot = -45;
                break;
            case 'lc':
                $rot = 180;
                $loffsetY .= '-1.5lh';
                $lrot = 0;
                break;
            case 'lr':
                $rot = 135;
                $loffsetY .= '-1.5lh';

                $lrot = 45;
                break;
        }
        if (in_array($this->staticLabelStyle['angle'], ['auto', 'follow', null])) {
            $offsetY = $loffsetY;
        }

        $offsets = $fontInfo->GetOffset(0, 0, $offsetX, $offsetY, $fontName, $fontSize, $label);
        #var_dump($offsetX,$offsetY,$offsets);
        $this->labelStyle['align'] = "center";
        $this->labelStyle['offsetx'] = $offsets['width'];
        $this->labelStyle['offsety'] = $offsets['height'];
        #var_dump($mapper->globalLabelAnchorStyle);

        $anchor = ($isRect === true ) ? Labels::GetRectAnchorPoint($srcQuery, $boundsSQL, $position) : Labels::PositionAnchorPoint($srcQuery, $boundsSQL, $position);

        $mapper->staticLabelStyle['position'] = 'cc'; //$pos;

        if (in_array($this->staticLabelStyle['angle'], ['auto', 'follow', null])) {
            if ($this->staticLabelStyle['angle'] !== 0) {
                $this->staticLabelStyle['angle'] = $lrot;
            }
        }
    }

    public function ParseColorString($colorStr) {
        if($colorStr === '') {
            return;
        }
        $colorInfo = explode(';', $colorStr);
        if (count($colorInfo) > 2) {
            list ($color, $opacity, $glopacity) = $colorInfo;
            $this->opacity = (double) $opacity;
            $this->glopacity = (double) $glopacity;
        }

        if (\count($colorInfo) > 1) {
            list ($color, $opacity) = $colorInfo;
            $this->opacity = abs((double) $opacity);
        } elseif (\count($colorInfo) === 1) {
            $color = $colorInfo[0];
        } else {
            $color = null;
        }
        

        $this->filter_color = urldecode($color);
    }

}
