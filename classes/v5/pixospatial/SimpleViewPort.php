<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5\pixospatial;

use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;
use System;
use utils\ParamUtil;
use Yaf_Registry;

/**
 * Description of SimpleViewPort
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class SimpleViewPort {

    public const TYPE_PIXO = 1;
    public const TYPE_WORLD = 2;
    public const TYPE_LOCAL = 3;

    private $width;
    private $height;
    private $bbox;
    private $north;
    private $south;
    private $east;
    private $west;
    private $projection;
    private $mapObj;
    private $ptObj;
    private $localHeight;
    private $localWidth;
    private $worldWidth;
    private $worldHeight;
    private $localTop;
    private $localBottom;
    private $localLeft;
    private $localRight;
    private $left;
    private $right;
    private $top;
    private $bottom;
    private $localProjection;
    private $worldProjection;
    private $worldScaleX;
    private $worldScaleY;
    private $localScaleX;
    private $localScaleY;

    public function __construct($args) {
        $this->localProjection = 'EPSG:3857';
        $this->worldProjection = 'EPSG:4326';

        list($this->bbox, $this->width, $this->height) = ParamUtil::Requires($args, 'bbox', 'width', 'height');
        if($this->bbox === 'full') {
            $this->bbox = '-180.0,-90.0,180.0,90.0';
        }
        list($this->west, $this->south, $this->east, $this->north) = (is_array($this->bbox) ? $this->bbox : explode(',', $this->bbox));

        

        $this->west = (double) ($this->west);
        $this->north = (double) ($this->north);
        $this->east = (double) ($this->east);
        $this->south = (double) ($this->south);
        
        $this->west = $this->CleanEW($this->west);
        $this->east = $this->CleanEW($this->east);

        $this->top = 0;
        $this->right = (double) ($this->width);
        $this->left = 0;
        $this->bottom = (double) ($this->height);

        $sw = (new SpatialPt())->SetWorldPt((double) ($this->west), (double) ($this->south));
        $ne = (new SpatialPt())->SetWorldPt((double) ($this->east), (double) ($this->north));
        $this->UpdateLocalSides($sw, $ne);
    }

    private function UpdateWorldSides(SpatialPt $sw, SpatialPt $ne) {
        $this->west = $sw->lng;
        $this->south = $sw->lat;
        $this->east = $ne->lng;
        $this->north = $ne->lat;
        $this->west = $this->CleanEW($this->west);
        $this->east = $this->CleanEW($this->east);
    }

    private function UpdateLocalSides(SpatialPt $sw, SpatialPt $ne) {
        $this->localLeft = $sw->easting;
        $this->localBottom = $sw->northing;
        $this->localRight = $ne->easting;
        $this->localTop = $ne->northing;
        $this->localHeight = $this->localTop - $this->localBottom;
        $this->localWidth = $this->localRight - $this->localLeft;
        $this->worldWidth = $this->west - $this->east;
        $this->worldHeight = $this->north - $this->south;
        $this->worldScaleX = $this->width / $this->worldWidth;
        $this->worldScaleY = $this->height / $this->worldHeight;
        $this->localScaleX = $this->width / $this->localWidth;
        $this->localScaleY = $this->height / $this->localHeight;
    }

    public function PixoSpatialPoint($args) {
        list($x, $y, $lng, $lat, $northing, $easting) = ParamUtil::ListValues($args, 'x', 'y', 'lng', 'lat', 'northing', 'easting');
        if (!is_null($x)) {
            return [
                'pixo' => ['x' => $x, 'y' => $y, 'type' => self::TYPE_PIXO],
                'spatial' => $this->GetSpatialPt($args)
            ];
        }
        if (!is_null($northing)) {
            $pt = (new SpatialPt())->SetLocalPt($easting, $northing);
            $pixo = $this->PtPixoFromLocal($pt);
            return [
                'pixo' => $pixo,
                'spatial' => $pt,
            ];
        }
        if (!is_null($lng)) {
            $pt = (new SpatialPt())->SetWorldPt($lng, $lat);
            $pixo = $this->PtPixoFromWorld($pt);
            return [
                'pixo' => $pixo,
                'spatial' => $$pt
            ];
        }

        return null;
    }

    public function PixoSpatialFromSpatial(SpatialPt $pt) {
        return [
            'pixo' => $this->PixoFromSpatial($pt),
            'spatial' => $pt
        ];
    }

    public function PixoFromSpatial(SpatialPt $pt) {
        list($easting, $northing) = $pt->LocalParams();
        $rightDelta = $easting - $this->localLeft;
        $topDelta = $northing - $this->localBottom;
        
        $relRight = $rightDelta * $this->localScaleX;
        $relDown = $topDelta * $this->localScaleY;
        
        return ['x' => $this->left + $relRight, 'y' => $this->top + $relDown, 'type' => self::TYPE_PIXO];
    }

    public function GetSpatialPt($args, $type = self::TYPE_WORLD) {
        list($x, $y) = ParamUtil::Requires($args, 'x', 'y');
        $rightDelta = $x;
        $topDelta = $y;
        $localX = $this->localLeft + ($x * $this->localScaleX);
        $localY = $this->localBottom + ($y * $this->localScaleY);


        return (new SpatialPt())->SetLocalPt($localX, $localY);
    }

    public function GetExtPolySQL($type = self::TYPE_WORLD, bool $with = false, $as = null, $geomAs = 'the_bounds') {
        $prefix = is_null($as) ? '' : (($with === false) ? ", $as as(SELECT " : "with $as as(SELECT ");
        $postFix = is_null($as) ? " as $geomAs" : " as $geomAs)";
        switch ($type) {
            case self::TYPE_WORLD:
                return $prefix . "ST_Polygon('LINESTRING({$this->west} {$this->south},{$this->west} {$this->north},{$this->east} {$this->north},{$this->east} {$this->south},{$this->west} {$this->south})',4326)::geography::geometry" . $postFix;
            case self::TYPE_LOCAL:
                return $prefix . "ST_Polygon('LINESTRING({$this->localLeft} $this->localBottom},{$this->localLeft} {$this->localTop},{$this->localRight} {$this->localTop},{$this->localRight} {$this->localBottom},{$this->localLeft} {$this->localBottom})',3857)" . $postFix;
        }
    }

    public function GetViewExtents($which = null, $as = 'assoc') {
        $extents = [
            'pixo' => [
                'left' => $this->left, 'bottom' => $this->bottom, 'right' => $this->right, 'top' => $this->top,
            ],
            'world' => [
                'west' => $this->west, 'south' => $this->south, 'east' => $this->east, 'north' => $this->north,
            ],
            'local' => [
                'west' => $this->localLeft, 'south' => $this->localBottom, 'east' => $this->localRight, 'north' => $this->localTop
            ]
        ];
        if (is_null($which)) {
            return $extents;
        }
        if ($as === 'assoc') {
            return $extents[$which];
        }
        return array_values($extents[$which]);
    }

    public function GetViewBBOX() {
        $exts = $this->GetViewExtents('world', 'vals');
        return implode(',', $exts);
    }

    public function GetViewDims($as = 'assoc') {
        $dims = ['width' => $this->width, 'height' => $this->height];
        if ($as === 'assoc') {
            return $dims;
        }
        return array_values($dims);
    }

    public function GetProjection($which) {
        if ($which === 'local') {
            return $this->localProjection;
        }
        return $this->worldProjection;
    }

    public function CleanEW($val) {
        $fVal = (double) ($val);
        while ($fVal > 180) {
            $fVal -= 360;
        }
        while ($fVal < -180) {
            $fVal += 360;
        }

        return $fVal;
    }

    public function PixoSpatialRect($box) {
        list($bbox) = ParamUtil::Requires('bbox');
        list($west, $south, $east, $north) = explode(',', $this->bbox);
        $sw = (new SpatialPt())->SetWorldPt((double) ($west), (double) ($south));
        $ne = (new SpatialPt())->SetWorldPt((double) ($east), (double) ($north));
    }

    public function FitExtents() {

        $pixoCenterX = (double) $this->width / 2.0;
        $pixoCenterY = (double) $this->height / 2.0;
        $localCenterX = (double) $this->left + $this->localHeight / 2.0;
        $localCenterY = (double) $this->bottom + $this->localHeight / 2.0;
        $newLW = $this->localLeft;
        $newLS = $this->localBottom;
        $newLN = $this->localTop;
        $newLE = $this->localRight;

        if ($width > $height) {
            $newLocalHeight = ($this->height * $this->localWidth) / $this->width;
            $newLocalMidheight = $newLocalHeight / 2.0;
            $newLN = $localCenterY + $newLocalMidHeight;
            $newLS = $localCenterY - $newLocalMidHeight;
        } else {
            $newLocalWidth = ($this->width * $this->localHeight) / $this->height;
            $newLocalMidWidth = $newLocalWidth / 2.0;
            $newLE = $localCenterY + $newLocalMidWidth;
            $newLW = $localCenterY - $newLocalMidWidth;
        }
        $sw = (new SpatialPt())->SetLocalPt($newLW, $newLS);
        $ne = (new SpatialPt())->SetLocalPt($newLE, $newLN);
        $this->UpdateLocalSides($sw, $ne);
        $this->UpdateWorldSides($sw, $ne);
    }

}
