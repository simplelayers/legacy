<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5\pixospatial;

use ArrayAccess;

/**
 * Description of PixoPt
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class SpatialPt  {

    public $lng;
    public $lat;
    public $northing;
    public $easting;

    
    public $local;
    public $world = 'EPSG:4326';
    
    public function __construct($world = 'EPSG:4326', $local = 'EPSG:3857') {
        $this->world = $world;
        $this->local = $local;
        return $this;        
    }

    public function SetWorldPt($lng, $lat, $autoConvert = true) {
        $this->lng = $lng;
        $this->lat = $lat;
        while($this->lng > 180) {
            if($this->lng > 180) {
                $this->lng -= 360;
            }
        }
        while($this->lng < -180) {
            if($this->lng < -180) {
                $this->lng += 360;
            }
        }
        
        if ($autoConvert === true) {
            $this->WorldToLocal();
        }
        return $this;
    }

    public function SetLocalPt($easting, $northing, $autoConvert = true) {
        $this->northing = $northing;
        $this->easting = $easting;
        if ($autoConvert === true) {
            $this->LocalToWorld();
        }
        return $this;
    }

    public function WorldToLocal() {
        $cmd = <<<CMD
echo "{$this->lng} {$this->lat}" | cs2cs +init={$this->world} +to +init={$this->local} -f "%.6f"
CMD;

        $result = shell_exec($cmd);
        $matches = [];
        preg_match_all('/[0-9\.-]+/',$result,$matches);
        $match = array_shift($matches);
        $easting = (double)(array_shift($match));
        $northing = (double)(array_shift($match));
        
        $this->northing = $northing;
        $this->easting = $easting;
        return $this;
    }

    public function LocalToWorld() {
        $cmd = <<<CMD
echo "{$this->easting} {$this->northing}" | cs2cs +init={$this->local} +to +init={$this->world} -f "%.6f"
CMD;
        $result = shell_exec($cmd);
        
        $matches = [];
        preg_match_all('/[0-9\.-]+/',$result,$matches);
        $match = array_shift($matches);
        $lng = (double)(array_shift($match));
        $lat = (double)(array_shift($match));
        $this->lng = $lng;
        $this->lat = $lat;
        return $this;
    }
    public function ToPGSQL($buffer=null,$env='world') {
        $ptQuery = '';
        switch ($env) {
            case 'local':
                $projInfo = explode(':', $this->local);
                $srid = array_pop($projInfo);
                $ptQuery = "ST_SetSRID(ST_Point({$this->northing},{$this->easting}),$srid)";
                
                break;
            default:
            case 'world':
                $projInfo = explode(':', $this->world);
                $srid = array_pop($projInfo);
                $ptQuery = "ST_SetSRID(ST_Point({$this->lng},{$this->lat}),$srid)";
                break;
        }
        if(!is_null($buffer)) {
            return "ST_Buffer({$ptQuery}::geography,$buffer)::geometry";
        }
        return $ptQuery;
    }
    
    public function WorldParams($withKeys=false) {
        if($withKeys) {
            return ['lng'=>$this->lng,'lat'=>$this->lat];
        }
        return [$this->lng,$this->lat];
    }
    public function LocalParams($withKeys=false) {
        if($withKeys) {
            return ['easting'=>$this->easting,'northing'=>$this->northing];
        }
        return [$this->easting,$this->northing];
    }
    public static function New() {
        return new SpatialPt();
    }

}
