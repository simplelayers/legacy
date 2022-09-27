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
class VirtualPoint extends VirtualLayer {
    
    //put your code here

    public function __construct($params) {
        parent::__construct($params);
        $buffer = ParamUtil::Get($params, 'buffer', 0);
        if ($buffer !== 0) {
            $this->geom_type = GeomTypes::POLYGON;
        } else {
            $this->geom_type = GeomTypes::POINT;
        }

        $data = ParamUtil::Get($params,'data', null);
        if (!is_null($data)) {
            $this->data = $data;
            return;
        }
        $wkt = ParamUtil::Get($params, 'wkt');
        $geoj = ParamUtil::Get($params, 'geoj_geom');
        if (!$wkt && !geoj) {
            $pt = ParamUtil::GetOne($params, 'point', 'pt','wkt','geoj_geom');
            if (is_null($pt)) {
                $lng = ParamUtil::RequiresOne($params, 'centerLon', 'centerlon', 'lng', 'lon', 'longitude');
                $lat = ParamUtil::RequiresOne($params, 'centerLat', 'centerlat', 'lat', 'latitude');
            } else {
                if (substr($params, 0, 1) === '{') {
                    $pt = json_decode($pt, true);
                    $lng = ParamUtil::RequiresOne($pt, 'lng', 'lon', 'longitude', 'x');
                    $lat = ParamUtil::RequiresOne($pt, 'lat', 'latitude', 'y');
                } else {
                    if (stripos($pt, ',')) {
                        list($lng, $lat) = explode(',', $pt);
                    } else {
                        if (stripos(trim($pt), ' ')) {
                            list($lng, $lat) = explode(' ', $pt);
                        }
                    }
                }
            }

            $geom = "ST_Point($lng $lat)";
            
        }
        if ($wkt) {
            $geom = "ST_GeometryFromText('$wkt')";
        }
        if ($geoj) {
            $geoj = json_encode(json_parse($geoj, true));
            $geom = "ST_GeomFromGeoJSON('$geoj')";
        }
        if ($geom) {
            if ($buffer !== 0) {
                $buffer = (double) $buffer;
                $geom = "ST_BUFFER($geom::geography,$buffer)::geometry";
            }
            $query = "the_geom from (select $labelQuery 1 as gid, $geom as the_geom) as q1 using unique gid using srid=4326";
            $this->data = $query;
        }
        //$maplayer->getextent()->setextent(floatval($ext[0]), floatval($ext[1]), floatval($ext[2]), floatval($ext[3]));
    }

}
