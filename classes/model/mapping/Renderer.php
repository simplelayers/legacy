<?php

namespace model\mapping;

use utils\ParamUtil;
use \Mapper;
use model\mapping\PixoSpatial;
use reporting\Transaction;

class Renderer implements \ArrayAccess, \Iterator, \Countable {

    protected $_mapObj;
    /* @ Pixospatial|null */
    protected $_pixo;
    /* @var Mapper|null $_mapper */
    protected $_mapper;
    protected $layers = array();
    protected $debug = false;

    public function __construct(PixoSpatial $pixo) {
        $this->_mapper = \System::Get()->getMapper();
        // $this->_mapObj = $this->_mapper->init(false);
        $this->_mapper->SetRenderer($this);
        $this->_pixo = $pixo;
        // $this->_mapObj = ms_newMapObj(null);
    }

    public function count() {
        return count($this->layers);
    }

    public function AddLayer($layer, $opacity = 1.0, $labels = false, $labelField = null, $baseLayer = null, $ignoreScale = true, $glopacity = null, $glowColor = null, $at = null) {
        if (is_null($opacity))
            $opacity = 1.0;
        if (is_null($labelField))
            $labelField = (isset($layer->labelitem)) ? $layer->labelitem : null;

        $entry = array(
            'layer' => $layer,
            'opacity' => $opacity,
            'labels' => $labels,
            'labelField' => $labelField,
            'baseLayer' => $baseLayer,
            'ignoreScale' => $ignoreScale,
            'glopacity' => $glopacity,
            'glowColor' => $glowColor,
            'mapLayer' => null
        );
        if (!is_null($at)) {
            array_splice($this->layers, $at, 0, $entry);
        } else {
            $this[] = $entry;
        }
    }

    public function RenderWeb($layer, $options, $baseLayer = null, $resultSetLayer = null, $unresultSetLayer = null) {
        #$this->_mapper->debugMapFile = true;
        $this->_mapper->SetMode(Mapper::$MODE_WEB);
        $this->Render($layer, $options, $baseLayer, $resultSetLayer, $unresultSetLayer);
    }

    public function RenderLatLon($layer, $options, $baseLayer = null, $resultSetLayer = null, $unresultSetLayer = null) {
        ##$this->_mapper->debugMapFile = true;
        $this->_mapper->SetMode(Mapper::$MODE_LATLON);

        $this->Render($layer, $options, $baseLayer, $resultSetLayer, $unresultSetLayer);
    }

    public function Render($layer, $options, $baseLayer = null, $resultSetLayer = null, $unresultSetLayer = null) {
        $mapper = $this->_mapper;

        $extent = ParamUtil::Get($options, 'bbox', "0,0,0,0");
        $extent = $mapper->GetProjectedExtents($extent);
        $extent = is_array($extent) ? $extent : explode(',', $extent);

        list ($x1, $y1, $x2, $y2) = $extent;

        $mapper->extent = array(
            $x1,
            $y1,
            $x2,
            $y2
        );

        // Get parameters
        ParamUtil::ForceOne($options, 'opacity', 1.00);
        ParamUtil::ForceOne($options, 'labels', 0);

        list ($interlace, $quantize, $gids, $uncolor, $unnormal, $labels, $labelField, $opacity, $projection) = ParamUtil::ListValues($options, 'interlace', 'quantize', 'gids', 'uncolor', 'unnormal', 'labels', 'label_field', 'opacity', 'projection');
        $this->_mapObj = $this->_mapper->init(false);
        if ($unnormal == 0) {
            $unnormal = null;
        }

        // Setup mapper
#        $mapper->debugMapFile = true;
        $mapper->width = (int) ParamUtil::Get($options, 'width');
        $mapper->height = (int) ParamUtil::Get($options, 'height');

        // $mapper->extent = explode(",",$extent);

        $mapper->SetPixospatialMapObj($this->_pixo);

        $mapper->interlace = is_null($interlace) ? false : true;
        $mapper->quantize = is_null($quantize) ? false : true;

        // $this->AddLayer($layer, $opacity, $labels, $labelField);
        // add a base layer if appropriae.
        if (!is_null($baseLayer)) {
            $labelField = ($labels == '1') ? $labelField : null;

            if ((is_null($uncolor) && is_null($gids)) || !is_null($unnormal)) {

                $this->AddLayer($baseLayer, $opacity, $labels, $labelField, null, false, null, null);
                if ($layer->type == \LayerTypes::WMS) {
                    $base64 = ParamUtil::Get($options, 'base64') !== null;
                    if (!$this->debug) {
                        if ($base64) {
                            header('Content-type: application/json', true);
                        } else {
                            header('Content-type: image/png', true);
                        }
                    }
                    if (!$base64) {
                        $mapper->_renderWebRemoteWMS(0);
                    }

                    if ($base64) {
                        echo "{\"image\":{\"width\":\"" . $mapper->width . "\",\"height\":\"" . $mapper->height . "\"";

                        $extent = $this->_mapper->GetProjectedExtents($extent, false);
                        if (is_array($extent))
                            $extent = implode(',', $extent);
                        echo ",\"bbox\":[" . $extent . "],\"content\":\"data:image/png;base64,";
                        #$data = $mapper->_renderStreamRemoteWMS(0);
                        #print base64_encode($mapper->_renderStreamRemoteWMS(0));
                        // print $mapper->renderStream(true, null, true, $base64);
                        print $mapper->renderStream(true, null, true, $base64);
                        echo "\"}}";
                    }

                    die();
                }
            }
        }

        if (isset($gids)) {
            // array_shift($this->layers);
            $glopacity = null;
            $colorInfo = ParamUtil::GetList($options, ';', 'color');

            if (count($colorInfo) > 2) {
                list ($color, $opacity, $glopacity) = $colorInfo;
            }

            if (count($colorInfo) > 1) {
                list ($color, $opacity) = $colorInfo;
            } else {
                $color = $colorInfo[0];
            }

            $color = urldecode($color);

            $resultSetLayer->filter_gids = $gids;
            $resultSetLayer->filter_color = $color;
            $mapper->filter_field = 'gid';
            $mapper->filter_color = $color;

            if (ParamUtil::Has($options, 'uncolor')) {

                $colorInfo = ParamUtil::GetList($options, ';', 'uncolor');

                $uncolorGlopacity = null;
                if (count($colorInfo) > 2) {
                    list ($uncolor, $uncolorOpacity, $uncolorGlopacity) = $colorInfo;
                } elseif (count($colorInfo) > 1) {
                    list ($uncolor, $uncolorOpacity) = $colorInfo;
                } elseif (count($colorInfo) > 0) {
                    $uncolor = $colorInfo[0];
                }
                $uncolor = urldecode($uncolor);
            } else {
                $uncolor = null;
            }

            $glowColor = ($unnormal) ? $color : $uncolor;
            if ($opacity) {
                
            } else {
                if ($uncolor) {
                    $opacity = ($layer->geomtype == \GeomTypes::LINE) ? 0.1 : $opacity;
                    $uncolorOpacity = ($layer->geomtype == \GeomTypes::LINE) ? 0.1 : $uncolorOpacity;
                    $uncolorGlopacity = ($layer->geomtype == \GeomTypes::LINE) ? 0.1 : $uncolorGlopacity;
                } else {
                    $opacity = 1.0;
                }
            }

            if (!is_null($uncolor)) {
                if (!is_null($unresultSetLayer)) {
                    $this->AddLayer($unresultSetLayer, $opacity, true, $labelField, false);
                }
            }

            $this->AddLayer($resultSetLayer, $opacity, $labels, $labelField, null, false, $glopacity, $glowColor);

            // $this->AddLayer($baseLayer, $opacity, $labels, $labelField);
            // add the filter
        }

        /*
         * list ($field, $color) = ParamUtil::ListValues($options, 'field', 'uncolor');
         * if (! is_null($field)) {
         * $mapper->filter_field = $field;
         * if (! is_null($color))
         * $mapper->filter_color = $color;
         * if (! is_null($gids))
         * $mapper->filter_gids = $gids;
         * }
         */

        $debugging = ParamUtil::Get($options, 'debug', $this->debug);
        $debugging = true;
        list ($base64) = ParamUtil::GetBoolean($options, 'base64');

        if (!$debugging) {
            if ($base64) {
                header('Content-type: application/json', true);
            } else {
                header('Content-type: image/png', true);
            }
        }

        if ($base64) {
            echo "{\"image\":{\"width\":\"" . $mapper->width . "\",\"height\":\"" . $mapper->height . "\"";
            $extent = $this->_mapper->GetProjectedExtents($extent, false);
            if (is_array($extent))
                $extent = implode(',', $extent);
            echo ",\"bbox\":[" . $extent . "],\"content\":\"data:image/png;base64,";
        }

        print $mapper->renderStream(true, null, true, $base64);
        if ($base64) {
            echo "\"}}";
        }
    }

    public function RenderBlank() {
        list ($base64) = ParamUtil::GetBoolean(\WAPI::GetParams(), 'base64');
        $extent = ParamUtil::Get(\WAPI::GetParams(), 'bbox', "0,0,0,0");
        $mapper = $this->_mapper;

        if ($base64) {
            header('Content-type: application/json', true);
        } else {
            header('Content-type: image/png', true);
            readfile(BASEURL . 'media/images/empty.png');
            return;
        }

        if ($base64) {
            
        } else {
            
        }
    }

    public function RenderTile($layer, $x, $y, $z) {
        $mapper = System::Get()->getMapper();

        $this->mapObj->setProjection("+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +units=m +k=1.0 +nadgrids=@null");
        $mapper->init(false, $this->_mapObj);

        if (!isset($request['suppress_header'])) {
            // header('Content-type: image/png',true);
        } else {
            // header('Content-Type:text/plain');
        }

        print $mapper->renderStream(true);
    }

    /**
     *
     * @param
     *            offset
     */
    public function offsetExists($offset) {
        return isset($this->layers[$offset]);
    }

    /**
     *
     * @param
     *            offset
     */
    public function offsetGet($offset) {
        if (count($this->layers) == 0)
            return null;
        return $this->layers[$offset];
    }

    /**
     *
     * @param
     *            offset
     * @param
     *            value
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->layers[] = $value;
            return;
        }
        $this->layers[$offset] = $value;
    }

    /**
     *
     * @param
     *            offset
     */
    public function offsetUnset($offset) {
        if ($this->offsetSet($offset)) {
            array_splice($this->layers, $offset, 1);
        }
    }

    private $currentIndex = 0;

    public function current() {
        return array_key_exists($this->layers, $this->currentIndex);
    }

    public function next() {
        $this->currentIndex ++;
    }

    public function key() {
        return $this->currentIndex;
    }

    public function valid() {
        return isset($this[$this->currentIndex]);
    }

    public function rewind() {
        $this->currentIndex = 0;
    }

}

?>