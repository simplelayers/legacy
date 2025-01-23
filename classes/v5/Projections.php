<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5;

use LayerTypes;
use ProjectLayer;
use System;
use utils\ParamUtil;
use utils\SQLUtil;

/**
 * Description of Projections
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class Projections
{

    /**
     * TypeaheadArgs: {terms:string, crs:'projcs'|'geoccs', auth:'epsg'|'esri'}
     */
    public static function TypeAhead($args)
    {
        list($terms, $crs, $auth) = ParamUtil::Requires($args, 'terms', 'crs', 'auth');
        $with = ParamUtil::Get($args, 'with', '');
        $options = explode(',', $with);

        $deprecated = "and not projname ilike '%(deprecated)'";
        $cols = 'projname as projection,crstype as crs,auth_name as auth,auth_srid as srid';
        $info = self::ProcessWith($options);
        $deprecated = is_null($Info['deprecated']) ? $deprecated : $info['deprecated'];
        $crses = explode(',', $crs);
        $crsOptions = ($crs === '*') ? "'geogcs','projcs'" :  SQLUtil::StringifyValues($crses, ',', '\'');
        $crsWhere = "crstype ilike any(array[$crsOptions])";
        $cols .= $info['cols'];
        $auths = explode(',', $auths);
        $authOptions = ($auth === '*') ? "'epsg','esri'" : SQLUtil::StringifyValues($auths, ',', '\'');
        $authWhere = 'auth_name ilike any(array[' . $authOptions . '])';
        $processedTerms = self::ProcessTerms($terms);

        $query = <<<QUERY
with info as (select * from sl_spatial_ref_sys_extra where $authWhere)
select $cols from info where $crsWhere and projname ilike all(array[$processedTerms])
$deprecated
QUERY;

        $db = System::GetDB();
        #$db->debug = true;

        $result = $db->Execute($query);

        return $result;
    }

    public static function ListRelaventAggregated($args)
    {
        $wapi = System::GetWapi();
        $terms = ParamUtil::Get($args, 'terms', '');
        $crs = ParamUtil::Get($args, 'crs', 'projcs');
        $crses = explode(',', $crs);
        $crsOptions = ($crs === '*') ? "'geogcs','projcs'" :  SQLUtil::StringifyValues($crses, ',', '\'');
        $crsWhere = "crstype ilike any(array[$crsOptions])";

        $with = ParamUtil::Get($args, 'with', '*');

        $options = explode(',', $with);
        $deprecated = " name not ilike '%(deprecated)'";
        $cols = 'projname as projection,crstype as crs,auth_name as auth,auth_srid';
        $info = self::ProcessWith($options);
        $deprecated = is_null($Info['deprecated']) ? $deprecated : $info['deprecated'];
        $cols = ($info['cols'] !== '') ? $cols : $info['cols'];
        $whereTerms = ($terms === '') ? ' where $crsWhere' : ' where $crsWhere and name ilike all(array[' . self::ProcessTerms($terms) . '])';

        $deprecated = ($info['deprecated'] === '') ? '' : ' and ' . $deprecated;

        $layer = $wapi->RequireALayer(LayerTypes::VECTOR, $args);
        if (is_a($layer, \ProjectLayer::class)) {
            $layer = $layer->layer;
        }
        $whereCRS = ' and crstype ilike ';
        $tableName = $layer->url;
        $query = <<<QUERY
with ext as (SELECT ST_SetSRID(st_envelope(st_extent(the_geom))::geometry,4326) as geom from $tableName)
,relevant as (select * from sl_spatialized_ref_sys where ST_WITHIN((select geom from ext),the_geom) OR ST_OVERLAPS((select geom from ext),the_geom))
,subset as(select srid,name from relevant $whereTerms$deprecated)
,grouped as (select trim(split_part(name,'/',1)) as group_name, trim(split_part(name,'/',2)) as subName from subset)
,agg1 as (SELECT  group_name,json_agg(subName) as entries from grouped group by group_name)
,agg2 as(select json_object_agg(group_name,entries) as groups from agg1 group by group_name)
,agg3 as (select json_build_object(agg1.group_name, agg1.entries) as data from agg1)
select sl_jsonb_merge_agg(agg3.data::jsonb) from agg3
QUERY;

        $db = System::GetDB();
        $result = $db->getOne($query, [$crs]);
        if ($result) {
            return \json_decode($result, true);
        }
        return $result;
    }

    public static function GetByID($args)
    {
        list($auth, $id) = ParamUtil::Requires($args, 'auth', 'id');
        $crs = ParamUtil::Get($args, 'crs', '*');
        $with = ParamUtil::Get($args, 'with', '');
        $crses = explode(',', $crs);
        $crsOptions = ($crs === '*') ? "'geogcs','projcs'" :  SQLUtil::StringifyValues($crses, ',', '\'');
        $crsWhere = "crstype ilike any(array[$crsOptions])";

        $cols = 'projname as name, crstype, auth_name as auth, auth_srid as id';
        $options = explode(',', $with);
        $info = self::ProcessWith($options);
        if ($info['cols'] !== '') {
            $cols .= $info['cols'];
        }
        $query = <<<QUERY
SELECT $cols from sl_spatial_ref_sys_extra where auth_name ilike ? and auth_srid = ? and $crsWhere
QUERY;
        $db = System::GetDB();
        //      $db->debug = true;
        $params = [$auth, $id];
        $results = $db->Execute($query, $params);
        return $results;
    }
    public static function GetByNamePair($args)
    {
        list($group, $name, $auth) = ParamUtil::Requires($args, 'group', 'name', 'auth');
        $cols = 'projname as name, crstype, auth_name as auth, auth_srid as id';
        $with = ParamUtil::Get($args, 'with', '');
        $options = explode(',', $with);
        $info = self::ProcessWith($options);
        if ($info['cols'] !== '') {
            $cols .= $info['cols'];
        }

        $whereCRS = ($crs !== '') ? "and crstype ilike ?" : '';
        $projectionName = ($name === '') ? $group : $group . ' / ' . $name;

        $query = <<<QUERY
SELECT $cols from sl_spatial_ref_sys_extra where auth_name ilike ? and projname ilike ? and crstype ilike any(array['projcs','geogcs'])
QUERY;
        $db = System::GetDB();
        return $db->Execute($query, [$auth, $projectionName]);
    }
    public static function GetByName($args)
    {
        list($name, $auth) = ParamUtil::Requires($args, 'name', 'auth');
        $cols = 'projname as name, crstype, auth_name as auth, auth_srid as id';
        $with = ParamUtil::Get($args, 'with', '');
        $options = explode(',', $with);
        $info = self::ProcessWith($options);
        if ($info['cols'] !== '') {
            $cols .= $info['cols'];
        }

        $whereCRS = ($crs !== '') ? "and crstype ilike ?" : '';
        $projectionName = $name;

        $query = <<<QUERY
SELECT $cols from sl_spatial_ref_sys_extra where auth_name ilike ? and projname ilike ? and crstype ilike any(array['projcs','geogcs'])
QUERY;
        $db = System::GetDB();
        return $db->Execute($query, [$auth, $projectionName]);
    }

    private static function ProcessTerms($terms)
    {
        $processdTerms = [];
        $termsCleaned = preg_replace('/[^\w\d]/', ' ', $terms);
        $termList = \explode(' ', $terms);
        foreach ($termList as $term) {
            $processedTerms[] = "'%$term%'";
        }
        return \implode(',', $processedTerms);
    }

    private static function ProcessWith(array $options)
    {
        $cols = '';
        $deprecated = null;
        foreach ($options as $option) {
            switch (strtolower($option)) {
                case 'sr':
                    $cols .= ',srtext as sr';
                    break;
                case 'proj4':
                    $cols .= ',proj4text as proj4';
                    break;
                case 'deps':
                    $deprecated = '';
                    break;
                case 'units':
                    $cols .= ',units';
            }
        }
        return ['cols' => $cols, 'deprectated' => $deprecated];
    }

    public static function GetSRS($authCode)
    {
        list($auth, $code) = explode(':', $authCode);

        $db = System::GetDB();

        $srs = $db->GetOne('select srtext from spatial_ref_sys where auth_name ilike ? and auth_srid = ?', [$auth, $code]);
        return $srs;
    }

    public static function GetLatLonSRS()
    {
        return self::GetSRS('EPSG:4326');
    }
    public static function GetDefaultSRS()
    {

        return self::GetLatLonSRS();
    }
    public static function GetWebSRS()
    {
        return self::GetSRS('EPSG:3857');
    }
    public static function GetLayerSRS($layerId)
    {

        $db = System::GetDB();
        # $db->debug = true;
        $infoStr = $db->GetOne('select import_info from layers where id=?', [$layerId]);
        $importInfo = \json_decode($infoStr, true);

        if ($importInfo) {
            if ($importInfo['info']) {
                if ($importInfo['info']['srs']) {
                    return $importInfo['info']['srs'];
                }
            }
        }
        return self::GetDefaultSRS();
    }
    public static function SRID2SRS($id)
    {
        $db = System::GetDB();
        $srs = $db->GetOne('select srtext from spatial_ref_sys where id=?', [$id]);
    }

    public static function GetSRSFromPrjObj($obj)
    {
        // Recursive function to locate the top-level AUTHORITY code


        // Look for the main AUTHORITY code in the top component (e.g., GEOGCS or PROJCS)
        foreach ($obj as $l1) {
            foreach ($l1['value'] as $component) {
                $epsgCode = self::FindAuthority($component);
                if ($epsgCode !== null) {
                    return $epsgCode;
                }
            }
        }
     
        return null;
    }
    public static function FindAuthority($node)
    {
        if (!isset($node['name'])) return null;
        if ($node["name"] === "AUTHORITY" && is_array($node["value"]) && count($node["value"]) === 2) {
            return implode(':', $node["value"]);
            #return $node["value"][1];  // EPSG code is usually the second item
        }
        return null;
    }
    public static function ParseWktToObj($wkt)
    {
        // Clean up whitespace
        $wkt = trim($wkt);
        // Regular expressions for parsing WKT components
        $pattern = '/([A-Z_]+)\s*\[(.*)\]/';
        $components = [];

        // Start parsing the top-level WKT
        $components[] = self::ParseSRSComponent($wkt);
     
        return $components;
    }
    // Recursive function to parse WKT components
    static function ParseSRSComponent($text)
    {
        $result = [];
        $pattern = '/([A-Z_]+)\s*\[(.*)\]/';

        if (preg_match($pattern, $text, $matches)) {
            $name = $matches[1];
            $valueText = $matches[2];

            // Split at commas, respecting nested brackets
            $values = [];
            $bracketLevel = 0;
            $currentPart = '';

            for ($i = 0; $i < strlen($valueText); $i++) {
                $char = $valueText[$i];

                if ($char === '[') {
                    $bracketLevel++;
                } elseif ($char === ']') {
                    $bracketLevel--;
                }

                if ($char === ',' && $bracketLevel === 0) {
                    $values[] = trim($currentPart);
                    $currentPart = '';
                } else {
                    $currentPart .= $char;
                }
            }
            if ($currentPart !== '') {
                $values[] = trim($currentPart);
            }

            // Parse values recursively
            $parsedValues = [];
            foreach ($values as $value) {
                if (preg_match($pattern, $value)) {
                    $parsedValues[] = self::ParseSRSComponent($value);
                } else {
                    $parsedValues[] = trim($value, '"');
                }
            }

            // Create the result as a structured object
            $result = [
                "name" => $name,
                "value" => $parsedValues
            ];
        }

        return $result;
    }

    public static function GetAuthorityFromWkt($wkt)
    {
        $wktObj = self::ParseWktToObj($wkt);
        
        // Check if the content resembles WKT format (starts with GEOGCS, PROJCS, or GEOGCRS, etc.)

        $epsgId = self::GetSRSFromPrjObj($wktObj);
        if(is_null($epsgId)) {
            return $wkt;
        }
        return $epsgId;
    }
    public static function GetSRSFromPrj($filePath)
    {
        // Check if file exists
        if (!file_exists($filePath)) {
            return false;
        }

        // Read file content
        $wktContent = file_get_contents($filePath);
        return self::GetAuthorityFromWkt($wktContent);
    }
}
