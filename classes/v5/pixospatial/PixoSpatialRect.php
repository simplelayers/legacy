<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5\pixospatial;

use v5\pixospatial\SpatialPt;
use ArrayAccess;

/**
 * Description of PixoPt
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class PixoSpatialRect {

    public $projection;

    /**
     *
     * @var SpatialPt
     */
    public $sw;

    /**
     *
     * @var SpatialPt
     */
    public $ne;

    /**
     * @var SpatialViewPort
     */
    public $viewPort;

    public function __construct(SimpleViewPort $viewPort) {
        $this->viewPort = $viewPort;
    }

    public static function New($viewPort) {
        return new PixoSpatialRect($viewPort);
    }

    public function SetBBox($bbox, $sep = ',') {
        list($west, $south, $east, $north) = explode($sep, $bbox);
        $sw = SpatialPt::New()->SetWorldPt($west, $south);
        $ne = SpatialPt::New()->SetWorldPt($east, $north);
        $this->SetSpatialExts($sw, $ne);
        return $this;
    }

    public function SetSpatialExts(SpatialPt $sw, SpatialPt$ne) {
        $this->sw = $this->viewPort->PixoSpatialFromSpatial($sw);
        $this->ne = $this->viewPort->PixoSpatialFromSpatial($ne);
        return $this;
    }

    public function ToPGSQL($buffer = null, $env = 'world') {
        switch ($env) {
            case 'local':
                $projInfo = explode(':', $this->local);
                $srid = array_pop($projInfo);
                $rect = "ST_Envelope(ST_Collect(array[" . $this->sw['spatial']->ToPGSQL() . ',' . $this->ne['spatial']->ToPGSQL() . ']))';
//$rect = "ST_Polygon('LINESTRING($west $south,$west $north,$east $north,$east $south,$west $south)'),$srid)";
                break;
            default:
            case 'world':
                $projInfo = explode(':', $this->world);
                $srid = array_pop($projInfo);
                $rect = "ST_Envelope(ST_Collect(array[" . $this->sw['spatial']->ToPGSQL() . ',' . $this->ne['spatial']->ToPGSQL() . ']))';
// $rect =  "ST_Polygon('LINESTRING($west $south,$west $north,$east $north,$east $south,$west $south)'),$srid)";
                break;
        }
        if (!is_null($buffer)) {
            return "ST_Buffer({$rect}::geography,$buffer)::geometry";
        } else {
            return $rect;
        }
    }

    function GetPointAt($position) {
        $west = $this->sw['spatial']->lng;
        $south = $this->sw['spatial']->lat;
        $east = $this->ne['spatial']->lng;
        $north = $this->ne['spatial']->lat;
        $midEast = $west + (($east - $west / 2.0));
        $midNorth = $south + (($north - $south) / 2.0);
        switch ($position) {
            case 'ul':
                $pt = SpatialPt::New()->SetWorldPt($west, $north);
                break;
            case 'uc':
                $pt = SpatialPt::New()->SetWorldPt($midEast, $north);
                break;
            case 'ur':
                $pt = SpatialPt::New()->SetWorldPt($east, $north);
                break;
            case 'cl':
                $pt = SpatialPt::New()->SetWorldPt($west, $midNorth);
                break;
            case 'cc':
                $pt = SpatialPt::New()->SetWorldPt($midEast, $midNorth);
                break;
            case 'cr':
                $pt = SpatialPt::New()->SetWorldPt($east, $midNorth);
                break;
            case 'll':
                $pt = SpatialPt::New()->SetWorldPt($west, $south);
                break;
            case 'lc':
                $pt = SpatialPt::New()->SetWorldPt($midEast, $south);
                break;
            case 'lr';
                $pt = SpatialPt::New()->SetWorldPt($east, $south);
                break;
        }
        return $pt;
        
    }

}
