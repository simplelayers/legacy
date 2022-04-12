<?php
use \AccessLevels;
use \GeomTypes;
use \LayerTypes;
use \RequestUtil;
use \Paging;
use \Projector_MapScript;
use \System;
use \SimpleSession;
use \WAPI;
use utils\ParamUtil;
use utils\Pixospatial;

/**
 *
 *
 * XML representing the result set (and the SQL executed), or XML representing an error.
 * {@example docs/examples/wapigetfeatures.txt}
 * {@example docs/examples/wapi_error.txt}
 *
 * @package WebAPI
 */
/**
 *
 * @param integer layer Id for the layer to search against.
 * @params json criteria array of criteria 
 * @params string format  json | xml | csv | xls output format, csv and xls are more for exporting search results.
 * @param integer limit (OPTIONAL) number of records to show.
 * @param integer first (OPTIONAL) 1-based offset for record to start on.
 * @param integer count (OPTIONAL) total number of records, if provided count will not be recalculated.
 * @param boolean geom (OPTIONAL) if 1 or true will include geometry in result; default false
 * @param integer intersectMode (OPTIONAL) 0=None(default) 1=Intersect -1=NotIntersect 2=Within -2=NotWithin  
 * @param string fields (OPTIONAL) "*"(default) or comma separated field names or string array of field names.
 * @param boolean unpgids (OPTIONAL) true (default) include all ids from the results including those not in the paged range.
 * @params string bbox (OPTIONAL) comma separated extents minx,miny,maxx,maxy in lat/lon coordinates to spatially constrain the search.
 * @params string gids (OPTIONAL) comma separated list of feature ids (used within intersectMode and memoryLayer to perform intersection queries.
 * @params integer memoryLayer (OPTIONAL) id for the layer gids are from.
 * @params boolean with_attributes (OPTIONAL) false(default), true or 1 to include attribute info in the results.
 * @params integer buffer (OPTIONAL) buffer radius in meters
 
 
 * @ignore
 *
 */
function _exec()
{
    // General Setup
    $sys = System::Get();
    $wapi = System::GetWapi();
    $args = WAPI::GetParams();
    $user = SimpleSession::Get()->GetUser();
    $db = System::GetDB(System::DB_ACCOUNT_SU);
    
    // Get a layer (not a project layer)
    //      Note: RequireLayer will pull layerId from $_REQUEST.
    $layer = $wapi->RequireLayer(array(
        LayerTypes::VECTOR,
        LayerTypes::RELATIONAL,
        LayerTypes::ODBC
    ));
    
    // Confirm Read Permission For Layer
    $permissions = $layer->getPermissionById($user->id);
    $canSearch = $permissions >= AccessLevels::READ;
    
    if (! $canSearch)
        throw new Exception('Insufficinet permission to search map');
    
    // Get request params or set them to defaults.
    $getGeom = ParamUtil::GetBoolean($args, 'geom');
    if(ParamUtil::GetBoolean($args,'wkt')) $getGeom = true;
    $intersectionMode = ParamUtil::Get($args, 'intersectMode');
    $fields = ParamUtil::Get($args, 'fields', "*");
    $includeUnpagedIds = ParamUtil::Get($args, 'unpgids', true);
    $bbox = ParamUtil::Get($args, 'bbox', null);
    $gids = ParamUtil::Get($args, 'gids', null);
    $memoryLayer = ParamUtil::get($args, 'memoryLayer', null);
    $withAttributes = ParamUtil::Get($args, 'with_attributes', false);
    $buffer = ParamUtil::Get($args, 'buffer', null);
    $format = ParamUtil::Get($args, 'format');
    
    // $db->debug=true;
    // Initiate attribute exclusions
   
    
    // Initialize paging; will look for count, limit, first if present.
    $paging = new Paging();
    
   
    //$method = ParamUtil::Get($args, 'method', 'OR');
    //$orderBy = ParamUtil::Get($args, 'sort');
    //$idField = ParamUtil::Get($args, 'idfield', 'gid');
    
    //$attributes = $layer->getAttributesVerbose(true, false, true, true);
    


    
    // Initialize internal arrays to hold results
    $queries = array();
    $resultSets = array();
    $resultAtts = array();
    
    // If fields is '*' determine the list of visible fields.
    // this will set args['fields'] with an array of fields.
    $attributeExclusions = array(
        'the_geom',
        'box_geom'
    );
    
    //Expand fields if wildcard (i.e. *)
    if ($fields == '*') {
        $args['fields'] = SearchCriteria::GetWildcardFields($layer, $attributeExclusions);        
    }
    
    // Initialize a SearchCriteria object which will take care of detecting the
    // criteria from args and provide functionality for building queries..
    $criteria = new SearchCriteria($layer->url, $args, true);
    
    
    // Get paging info based on initial criteria settings.
    $pagingInfo = array();
    $criteria->paging->mergeData($pagingInfo);
    
    // get the initial paging limit.
    $limit = $pagingInfo['limit'];
    
    
    // unset criteria paging.
    $criteria->paging->limit = - 1;
    $criteria->paging->count = null;
    $criteria->paging->count = null;
    
    // Get unpagedIds if requested
    $unpagedIds = null;
    if (! is_null($includeUnpagedIds)) {
        $query = $criteria->GetIdsQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
        $results = $db->Execute($query);
        $unpagedIds = ParamUtil::GetUnique($results, 'gid');
    }

    // Determine the count query
    $query = $criteria->GetCountQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
    
    $criteria->paging->limit = $limit;
    // Determine the count by either returning known count or by executing the count query.
    $count = ($criteria->paging->count > - 1) ? $criteria->paging->count : $db->GetOne($query);
    $paging->count = $count;
    
    // Tell criteria whether to return geometry
    $criteria->includeGeometry = $getGeom;
    
    $attributes = $layer->getAttributesVerbose(true, false, true, true);
    $attInfo = array();
    if($withAttributes) {
        foreach ($attributes as $attribute => $value) {
            $entry = array(
                'name' => $attribute,
                'requirement' => $value['requires'],
                'type' => $value['type'],
                'maxlength' => $value['maxlength'],
                'display' => $value['display'],
                'z' => $value['z'],
                'meta_info' => $value['meta_info']
            );
            
            $entry['searchable'] = $value['searchable']; // ? 'true' : 'false';
            $entry['visible'] = $value['visible']; // ? 'true' : 'false';
            
            $attInfo[] = $entry;
        }
    }
    
    $resultAtts[] = array(
        'layerid' => $layer->id,
        'count' => $count
    );
    
    $resultsSets[] = array(
        'layer' => $layer,
        'criteria' => $criteria
    );
    
    
    
    // layerid="<!--{$layerid}-->" plid="<!--{$plid}-->" geom="<!--{$layerType}-->" <!--{$paging->toAttString()}--> ><!--{foreach from=$features item=feature}-->
    switch ($format) {
        case 'xml':
            WAPI::SetWapiHeaders($format);
            
            $resultSet = $resultsSets[0];
            $resultAtts = $resultAtts[0];
            
            $query = $resultSet['criteria']->GetQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
            
            // die();
            $results = $db->Execute($query);
            
            $resultSet['criteria']->paging->setResults($results, $resultAtts['count']);
            $resultSet['criteria']->paging->mergeData($resultAtts);
            // WAPI::SetWapiHeaders('xml');
            WAPI::SendSimpleResults($results, 'xml', false, 'ok', array(
                $resultAtts
            ), array(
                'docName' => 'features',
                'itemName' => 'feature'
            ));
            
            break;
        case 'json':
            
            $resultSet = $resultsSets[0];
            $resultSet['criteria']->includeGeometry = $getGeom;
            
            $query = $resultSet['criteria']->GetQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
            
            $results = $db->Execute($query);
            
            WAPI::SetWapiHeaders('json');
            echo "{\"paging\":" . $paging->toJSON() . ',"layer":' . $layer->id;
            echo ",\"layer_name\":\"".$layer->name."\"";
            $unpagedBBOX = "";
            $unpaged = "";
            
            if($includeUnpagedIds) {
                if (! is_null($unpagedIds)) {
                    $unpaged = implode($unpagedIds, ',');
                    echo ',"unpaged_ids":[' . $unpaged . ']';
                    $unpagedBBOX = $db->GetOne("with gq as (select ST_AsText(the_geom) as the_geom from {$layer->url} where gid in ($unpaged)) select ST_EXTENT(the_geom) from gq");
                }
            }
            
            if (is_null($gids)) {
                $gids = array();
                foreach ($results as $record) {
                    $gids[] = $record['gid'];
                }
            }
            if (! is_string($gids)) {
                $gids = implode(',', $gids);
            }
            //$unpaged = implode(',', $unpagedIds);
            echo ",\"layer_bbox\":[" . $layer->getCommaExtent() . ']';
            $pagedBBOX = $db->GetOne("with gq as (select ST_AsText(the_geom) as the_geom from {$layer->url} where gid in ($gids)) select ST_EXTENT(the_geom) from gq");
            $pagedBBOX = str_replace("BOX(", "", $pagedBBOX);
            $pagedBBOX = str_replace(")", "", $pagedBBOX);
            $pagedBBOX = str_replace(", ", ",", $pagedBBOX);
            $pagedBBOX = str_replace(" ", ",", $pagedBBOX);
            
            echo ",\"paged_bbox\":[" . $pagedBBOX . "]";
            
            $unpagedBBOX = str_replace("BOX(", "", $unpagedBBOX);
            $unpagedBBOX = str_replace(")", "", $unpagedBBOX);
            $unpagedBBOX = str_replace(", ", ",", $unpagedBBOX);
            $unpagedBBOX = str_replace(" ", ",", $unpagedBBOX);
            
            echo ",\"unpaged_bbox\":[" . $unpagedBBOX . "]";
            if ($withAttributes !== false) {
                echo ",\"attributes\":" . json_encode($attInfo);
            }
            
            echo ',"resultset":';
            WAPI::SendSimpleResults($results, 'json', false, 'ok');
            echo "}";
            break;
        case 'csv':
        case 'xls':
            
            $resultSet = $resultsSets[0];
            $resultAtts = $resultAtts[0];
            $layer = $resultSet['player']->layer;
            
            $query = $resultSet['criteria']->GetQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
            
            // echo "query";
            $features = $db->Execute($query);
            if ($getGeom)
                array_shift($attributeExclusions);
            $resultSet['criteria']->paging->setResults($features, $resultAtts['count']);
            $resultSet['criteria']->paging->mergeData($resultAtts);
            
            $fields = array();
            
            $firstRecord = array();
            if ($features) {
                
                if ($features->RecordCount() > 0) {
                    
                    $features->MoveFirst();
                    $firstRecord = $features->FetchRow();
                    
                    $firstRecord = (! is_null($features)) ? $firstRecord : array();
                    $attributes = (! is_null($features)) ? $layer->field_info : array();
                    
                    foreach ($firstRecord as $key => $val) {
                        if ($key == 'gid') {
                            $fields[] = 'Result Id';
                            continue;
                        }
                        if (! in_array($key, $attributeExclusions)) {
                            if ($attributes) {
                                $hasAttInfo = false;
                                foreach ($attributes as $attInfo) {
                                    if ($attInfo['name'] == $key) {
                                        $fields[] = $attInfo['display'];
                                        $hasAttInfo = true;
                                    }
                                }
                                if (! $hasAttInfo)
                                    $fields[] = $key;
                            } else {
                                $fields[] = $key;
                            }
                        }
                    }
                    $features->MoveFirst();
                }
            }
            
            $f = fopen("php://output", 'w');
            $format = RequestUtil::Get('format');
            
            WAPI::SetWapiHeaders($format, 'results.' . $format);
            fputcsv($f, $fields);
            
            if ($features) {
                foreach ($features as $row) {
                    
                    foreach ($attributeExclusions as $attr) {
                        unset($row[$attr]);
                    }
                    foreach ($row as $key => $val) {
                        if (is_null($val))
                            $row[$key] = "";
                    }
                    fputcsv($f, $row);
                }
            }
            break;
            fclose($f);
    }
}

?>