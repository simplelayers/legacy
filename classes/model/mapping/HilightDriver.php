<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace model\mapping;

use utils\ColorUtil;

/**
 * Description of HilightDriver
 *
 * @author Arthur
 */
class HilightDriver {

    const UNINITIALIZED = -1;
    const UNDERLAY = 0;
    const NATURAL = 1;
    const OVERLAY = 2;
    const COMPLETED = 3;

    public $gids;
    
    private $filterColors = array();
    public $hilighting = false;
    private $hilightOffset = self::UNINITIALIZED;
    private $inverted = false;
    
    public function __construct($gids, $filterColor, $opacity, $glopacity, $hilighting = true) {
        $this->filterColors[self::UNDERLAY] = array('filter_color' => $filterColor, 'opacity' => $opacity, 'glopacity' => $glopacity);
        $this->filterColors[self::NATURAL] = array();
        $this->filterColors[self::OVERLAY] = array('filter_color' => $filterColor, 'opacity' => $opacity, 'glopacity' => 0);
        $this->hilighting = $hilighting;
    }

    public function Invert($inverted = null) {
        if (is_null($inverted)) {
            $this->inverted = !$this->inverted;
        } else {
            $this->inverted = $inverted;
        }
    }

    public function __get($what) {
        $getterName = '__get_' . $what;
        if (method_exists($this, $getterName)) {
            return call_user_func(array($this, $getterName));
        }
    }

    public function SetHilightOffset($i) {
        $this->hilightOffset = $i;
    }

    public function GetHilightStage($layerOffset) {
        if ($this->hilightOffset === self::UNINITIALIZED) {
            return self::UNINITIALIZED;
        }
        $offsetDelta = $layerOffset - $this->hilightOffset;

        if ($offsetDelta >= self::COMPLETED) {
            return self::COMPLETED;
        }
        if ($this->inverted) {
            return self::OVERLAY - $offsetDelta;
        }
        return $offsetDelta;
    }

    public function GetColor($layerOffset, $strokeOrFill = 'stroke') {
        $stage = $this->GetHilightStage($layerOffset);
        switch ($stage) {
            case self::UNDERLAY:
                return $this->underlay['filter_color'];
                break;
            case self::OVERLAY:
                return $this->overlay['filter_color'];
                break;
            default:
                return $this->natural[$strokeOrFill];
                break;
        }
    }

    public function ToggleHilighting($onOrOff = null) {
        if (is_null($onOrOff)) {
            $this->hilighting = !$this->hilighting;
            return $this->hilighting;
        }
        $this->hilighting = $onOrOff;
        return $this->hilighting;
    }

    public function SetNaturalInfo($stroke, $fill, $opacity, $glopacity = null, $gids = null) {
        $this->filterColors[self::NATURAL]['stroke'] = $stroke;
        $this->filterColors[self::NATURAL]['fill'] = $fill;
        $this->filterColors[self::NATURAL]['opacity'] = $this->hilighting ? 0 : $opacity;//$opacity;

        if (!is_null($glopacity)) {
            $this->filterColors[self::NATURAL]['glopacity'] = $glopacity;
        } else {
            $this->filterColors[self::NATURAL]['glopacity'] = $this->filterColors[self::UNDERLAY]['glopacity'];
        }
        if (!is_null($gids)) {
            $this->gids = $gids;
        }
    }

    public function SetStyleVars($target, &$sr, &$sg, &$sb, &$fr, &$fg, &$fb, &$xpacity, &$glopacity = null) {

        switch ($target) {
            case self::UNDERLAY:
                $info = $this->underlay;
                $xpacity = $info['glopacity'] * 100;
                
                break;
            case self::OVERLAY:
                $info = $this->overlay;
                $xpacity = $info['opacity'] * 100;
                
                break;
            case self::NATURAL :
            default:
                if (count($this->natural) >= 3) {
                    $r = $g = $b = -1;
                    ColorUtil::Web2RGB($this->natural['stroke'], $r, $g, $b);
                    $sr = $r;
                    $sg = $g;
                    $sb = $b;
                    ColorUtil::Web2RGB($this->natural['fill'], $r, $g, $b);
                    $fr = $r;
                    $fg = $g;
                    $fb = $b;
                    $xpacity = isset($this->natural['opacity']) ? $this->natural['opacity'] *100: 100;
                    $glopacity = $this->natural['glopacity']* 100;
                }
                return;
                break;
        }
        if (($target > self::UNINITIALIZED) && ($target < self::COMPLETED)) {
            $r = $g = $b = -1;
            ColorUtil::Web2RGB($info['filter_color'], $r, $g, $b);
            $sr = $r;
            $sg = $g;
            $sb = $b;
            ColorUtil::Web2RGB($info['filter_color'], $r, $g, $b);
            $fr = $r;
            $fg = $g;
            $fb = $b;
        }

    }

    private function __get_natural() {
        return $this->filterColors[self::NATURAL];
    }

    private function __get_underlay() {
        if ($this->hilighting === false) {
            return false;
        }
        return $this->filterColors[self::UNDERLAY];
    }

    private function __get_overlay() {
        if ($this->hilighting === false) {
            return false;
        }
        return $this->filterColors[self::OVERLAY];
    }

}
