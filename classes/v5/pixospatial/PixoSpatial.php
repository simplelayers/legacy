<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5;

use utils\ParamUtil;
use v5\pixospatial\SimpleViewPort;

/**
 * Description of PixoSpatial
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class PixoSpatial {

    /**
     *
     * @var SimpleViewPort 
     */
    public $viewPort;

    public function __constructor(SimpleViewPort $view) {
        $this->viewPort = $view;
    }

    public function GetPixoSpatialPt($args) {
        list($x, $y, $northing, $easting, $lng, $lat) = ParamUtil::ListValues($args, 'x', 'y', 'northing', 'easting', 'lng', 'lat');
        if (!is_null($x)) {
            $pixo = ['x' => $x, 'y' => 'y', 'type' => SimpleViewPort::TYPE_PIXO];
            $world = $this->viewPort->GetSpatialPt($pixo, SimpleViewPort::TYPE_WORLD);
            $local = $this->viewPort->GetSpatialPt($pixo, SimpleViewPort::TYPE_LOCAL);
            return ['pixo' => $pixo, 'world' => $world, 'local' => $local];
        }
    }

    public function GetDeltas($psPt1, $psPt2) {
        $deltas = [
            'pixo'=>$this->GetPtDeltas($psPt1,$psPt2,'pixo'),
            'world'=>$this->GetPtDeltas($psPt1,$psPt2,'world'),
            'local'=>$this->GetPtDeltas($psPt1,$psPt2,'local')
        ];        
        return $deltas;
    }
    public function GetDeltaDistances($deltas) {
        $distances = [
            'pixo'=>sqrt( pow($deltas['pixo']['y'],2) + pow($deltas['pixo']['x'],2)),
            'world'=>sqrt( pow($deltas['world']['northing'],2) + pow($deltas['world']['easting'],2)),
            'local'=>sqrt( pow($deltas['local']['lat'],2) + pow($deltas['local']['lon'],2))
        ];
        return $distances;
    }
    

    private function GetPtDeltas($psPt, $psPt2, $type) {
        list($target,$x,$y) = ParamUtils::ListValues($this->TypeToParams($type),'target','x','y');
        return [
            $x => $psPt2[$target][$x] - $psPt[$target][$x],
            $y => $psPt2[$target][$y] - $psPt[$target][$y]
        ];
    }
    private function TypeToParams($type) {
        switch ($type) {
            case 'pixo':
            case SimpleViewPort::TYPE_PIXO:
                $target = 'pixo';
                $x= 'x';
                $y= 'y';
                break;
            case 'world':
            case SimpleViewPort::TYPE_WORLD:
                $target= 'world';
                $x= 'lng';
                $y= 'lat';
                break;
            case 'local':
            case SimpleViewPort::TYPE_LOCAL:
                $target = 'local';
                $x='northing';
                $y='easting';
        }
        return ['x'=>$x,'y'=>$y,'target'=>$target];
    }
    
}
