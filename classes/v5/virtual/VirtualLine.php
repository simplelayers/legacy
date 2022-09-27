<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5\virtual;

use GeomTypes;
use utils\ParamUtil;
use v5\pixospatial\SimpleViewPort;

/**
 * Description of VirtualPoint
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class VirtualLine extends VirtualLayer {

    //put your code here

    public function __construct($params) {
         parent::__construct($params);
        $this->geom_type = GeomTypes::LINE;

        $buffer = ParamUtil::Get($params, 'buffer', 0);
        if ($buffer !== 0) {
            $this->geom_type = GeomTypes::POLYGON;
        }

        $data = ParamUtil::Get($params,'data', null);
        if (!is_null($data)) {
            $this->data = $data;
            return;
        }

        $wkt = ParamUtil::Get($params,'wkt');
        $geoj = ParamUtil::Get($params,'geoj_geom');
        
        $inputLine = ParamUtil::RequiresOne($params, 'inputLine', 'line','wkt','geoj_geom');
        if(!$wkt && !$geoj) {
            $pts = explode(',', $inputLine);
            $queryPts = [];
            foreach ($pts as $pt) {
                list($lng, $lat) = explode(' ', $pt);
                $queryPts[] = 'ST_Point($lng,$lat)';
            }
            $geom = 'ST_MakeLine(ST_collect(' . implode(',' . $queryPts) . '))';
        }
        if($wkt) {
            $geom = "ST_GeometryFromText('$wkt')";
        }
        if($geoj) {
            $geoj = json_encode(json_parse($geoj,true));
            $geom = "ST_GeomFromGeoJSON('$geoj')";
        }
        
        if ($buffer !== 0) {
            $buffer = (double) $buffer;
            $geom = "ST_BUFFER($geom::geography,$buffer)::geometry";
        }
        $query = "the_geom from (select 1 as gid, $geom as the_geom) as q1 using unique gid using srid=4326";
        $this->data = $query;
    }

    //$maplayer->getextent()->setextent(floatval($ext[0]), floatval($ext[1]), floatval($ext[2]), floatval($ext[3]));
}
