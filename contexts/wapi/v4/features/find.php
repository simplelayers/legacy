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
 * @ignore
 *
 */
function _exec($termplate, $args) {
    $sys = System::Get();
    $wapi = System::GetWapi();
    $user = SimpleSession::Get()->GetUser();
    $db = System::GetDB(System::DB_ACCOUNT_SU);
    
    //$db->debug=true;
    $attributeExclusions = array(
        'the_geom',
        'box_geom'
    );
    
    $paging = new Paging();
    $getGeom = ParamUtil::GetBoolean($args, 'geom');
    $method = ParamUtil::Get($args, 'method', 'OR');
    $orderBy = ParamUtil::Get($args, 'orderby', 'gid');
    $idField = ParamUtil::Get($args, 'idfield', 'gid');
    $bbox = ParamUtil::Get($args, 'bbox');
    $pbox = ParamUtil::Get($args, 'pxrect');
    
    $gids = ParamUtil::Get($args, 'gids');
    $buffer = ParamUtil::Get($args, 'buffer');
    $searchMode = Paramutil::Get($args, 'mode', 'search');
    
    $memoryLayer = ParamUtil::Get($args, 'memoryLayer');
    
    $info = $wapi->RequireProject();
    
    
    
    $intersectionMode = ParamUtil::Get($args, 'intersectMode');
    $project = $info['project'];
    $permission = $info['permission'];
    $canSearch = $permission >= AccessLevels::READ;
    if (! $canSearch)
        throw new Exception('Insufficinet permission to search map');
    
    $layers = $wapi->RequireProjectLayers(array(
        LayerTypes::VECTOR,
        LayerTypes::RELATIONAL,
        LayerTypes::ODBC
    ));
    
    $queries = array();
    
    $resultSets = array();
    $resultAtts = array();
    
    foreach ($layers as $pLayer) {
        
        
        $layer = $pLayer->layer;
        $fields = ParamUtil::Get($args, 'fields', "*");
        if ($fields == '*') {
            $fields = array();
            $atts = $layer->field_info;
            $atts = !is_null($atts) ? $atts : $layer->getAttributesVerbose();
            if(!is_null($atts)) {
                foreach ($atts as $att) {
                    if ($att['visible']) {
                        $fields[] = array(
                            'field' => $att['name']
                        ); // ,'as'=>$att['display']);
                    } else {
                        $attributeExclusions[] = $att['name'];
                    }
                }
            }
            
            if(!isset($fields['gid'])) array_unshift($fields, array('field'=>'gid','type'=>'numeric'));
            $args['fields'] = $fields;
            
        }
        
        $criteria = new SearchCriteria($layer->url, $args, true);
        
        if (! is_null($pbox)) {
            if (! is_array($pbox))
                $pbox = explode(',', $pbox);
            if (count($pbox) == 4) {
                list ($x1, $y1, $x2, $y2) = $pbox;
                
                $pwidth = (int) $x2 - (int) x1;
                $pheight = (int) $y2 - (int) $y1;
                $pixo = Pixospatial::Get($bbox, $_REQUEST['width'], $_REQUEST['height']);
                $ROI = $pixo->ROI_to_BBOX($x1, $y1, $x2, $y2);
                $bbox = $ROI;
            }
        }
        
        $pagingInfo = array();
        $criteria->paging->mergeData($pagingInfo);
        
        $limit = $pagingInfo['limit'];
        
        $criteria->paging->limit=-1;
        $criteria->paging->count = null;
        $criteria->paging->count = null;
        
        $query = $criteria->GetCountQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
        
        $criteria->paging->limit = $limit;
        $layer = $pLayer->layer;
        $count = ($criteria->paging->count > - 1) ? $criteria->paging->count : $db->GetOne($query);
        $paging->count = $count;
        $resultAtts[] = array(
            'layerid' => $layer->id,
            'plid' => $pLayer->id,
            'count' => $count
        );
        
        $resultsSets[] = array(
            'player' => $pLayer,
            'criteria' => $criteria
        );
    }
    
    $format = ParamUtil::Get($args, 'format');
    
    // layerid="<!--{$layerid}-->" plid="<!--{$plid}-->" geom="<!--{$layerType}-->" <!--{$paging->toAttString()}--> ><!--{foreach from=$features item=feature}-->
    switch ($format) {
        case 'xml':          
            switch ($searchMode) {
                case 'query':
                    
                    $i = 0;
                    $results = array();
                    foreach ($resultsSets as $resultSet) {
                        
                        $results[] = array();
                        $i ++;
                    }
                    WAPI::SendSimpleResults($results, 'xml', false, 'ok', $resultAtts);
                    break;
                case 'search':
                    WAPI::SetWapiHeaders($format);
                    
                    $resultSet = $resultsSets[0];
                    $resultAtts = $resultAtts[0];
                    
                    $query = $resultSet['criteria']->GetQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
                    
                    //die();
                    $results = $db->Execute($query);
                    
                    $resultSet['criteria']->paging->setResults($results,$resultAtts['count']);
                    $resultSet['criteria']->paging->mergeData($resultAtts);
                    // WAPI::SetWapiHeaders('xml');
                    WAPI::SendSimpleResults($results, 'xml', false, 'ok', array($resultAtts),array('docName'=>'features','itemName'=>'feature'));
                    break;
            }
            
            break;
        case 'json':
            $resultSet = $resultsSets[0];
            $query = $resultSet['criteria']->GetQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
            $results = $db->Execute($query);
            WAPI::SetWapiHeaders('json');
            WAPI::SendSimpleResults($results, 'json', false, 'ok');
            break;
        case 'csv':
        case 'xls':
            $resultSet = $resultsSets[0];
            $resultAtts = $resultAtts[0];
            $layer = $resultSet['player']->layer;
            
            $query = $resultSet['criteria']->GetQuery(false, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer);
            
            //echo "query";
            $features = $db->Execute($query);
            if($getGeom) array_shift($attributeExclusions);
            $resultSet['criteria']->paging->setResults($features,$resultAtts['count']);
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
                        if($key == 'gid') {
                            $fields[] = 'Result Id';
                            continue;
                        }
                        if (! in_array($key, $attributeExclusions)) {
                            if ($attributes) {
                                $hasAttInfo = false;
                                foreach( $attributes as $attInfo) {
                                  if($attInfo['name'] == $key) {
                                      $fields[]=$attInfo['display'];
                                      $hasAttInfo = true;
                                  }
                                  
                                }
                                if(!$hasAttInfo) $fields[] = $key;
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
        
            if($features) {
                foreach ($features as $row) {
                   
                    foreach ($attributeExclusions as $attr) {
                        unset($row[$attr]);
                    }
                    foreach($row as $key=>$val) {
                        if(is_null($val)) $row[$key]="";
                    }
                  fputcsv($f, $row);
                }
            }
            break;
            fclose($f);
            
    }
    
}

function _execOld($template, $args)
{
    $world = System::Get();
    $wapi = System::GetWapi();
    
    $user = SimpleSession::Get()->GetUser();
    $paging = new Paging();
    $getGeom = RequestUtil::Get('geom', false);
    $method = RequestUtil::Get('method', 'OR');
    $orderBy = RequestUtil::Get('orderby', 'gid');
    $idField = RequestUtil::Get('idfield', 'gid');
    $bbox = RequestUtil::Get('bbox');
    $pbox = RequestUtil::Get('pxrect');
    
    $userId = $user->id;
    
    $info = $wapi->RequireProject();
    
    $project = $info['project'];
    $permission = $info['permission'];
    
    $canSearch = $permission >= AccessLevels::READ;
    
    // Step 2: Verify that the layer parameter has been set
    $projectLayers = $wapi->RequireProjectLayers(array(
        LayerTypes::VECTOR,
        LayerTypes::RELATIONAL,
        LayerTypes::ODBC
    ));
    
    // Step 5: Turn array of layer ids into an array of Layer objects.
    $layers = array();
    $layerIds = array();
    
    foreach ($projectLayers as $projectLayer) {
        $layerid = $projectLayer->id;
        
        if (in_array($layerid, $layerIds))
            continue;
        $permission = $projectLayer->layer->getPermissionById($userId);
        if ($permission < AccessLevels::READ)
            continue;
        if ($projectLayer->searchable) {
            $layer = $projectLayer->layer;
            $layers[] = $layer;
            $layerIds[] = $layerid;
        }
    }
    
    if (! is_null(RequestUtil::Get('pxrect'))) {
        $template->assign('pbox', $pbox);
        list ($x1, $y1, $x2, $y2) = explode(",", $pbox);
        $pwidth = (int) $x2 - (int) $x1;
        $pheight = (int) $y2 - (int) $y1;
        $projection = RequestUtil::Get('projection', $project->projectionSRID);
        // $projection = $world->projecitons->defaultSRID;
        $targetProj4 = $world->projections->getProj4BySRID($projection);
        // $mapper->projection = $project->projection;
        // //error_log("projection:".$mapper->projection);
        $projector = new Projector_MapScript();
        $extents = $projector->ProjectExtents($world->projections->defaultProj4, $targetProj4, RequestUtil::Get('width'), RequestUtil::Get('height'), $bbox);
        $projector->ZoomTo($x1, $y1, $x2, $y2);
        $projector->SetViewSize($pwidth, $pheight);
        // $extentsAfter = $projector->ProjectExtents( $targetProj4 , $world->projections->defaultProj4 , $pwidth, $pheight, $extents['to'] );
        // $projector->ProjectExtents( $targetProj4, $world->projections->defaultProj4, $_REQUEST['width'], $_REQUEST['height'],$bbox );
        $bbox = $projector->GetROIExtents("string");
        $ROI = $projector->GetROIExtents('array');
    }
    
    $features = array();
    // Step 6: Either process criteria or do a general feature query on the layer
    if (! is_null(RequestUtil::Get('criteria'))) {
        
        
        $searchTerms = array();
        
        $criteria = RequestUtil::GetList('criteria', ';');
        $i = 0;
        foreach ($criteria as &$criterion) {
            $options = explode('|', $criterion);
            if (count($options) > 1) {
                list ($criterion, $logic) = $options;
                if ($i == 0) {
                    $logic = (substr($logic, 0, 1) == '!') ? 'not' : '';
                }
            } else {
                $logic = '';
            }
            
            $items = explode(",", $criterion);
            $field = array_shift($items);
            $compare = array_shift($items);
            $value = implode(",", $items);
            
            $criterion = array(
                $field,
                $compare,
                $value,
                $logic
            );
            $i += 1;
        }
        
        if (sizeof($layers) > 1) {
            $features = $world->unionFeatureSearch($layers, $criteria, $paging, $method, $orderBy, $idField);
        } elseif (isset($bbox)) {
            $features = $layers[0]->searchFeaturesWithinBBox($bbox, $project->projection, $criteria, $paging, $getGeom, $method, $orderBy);
        } else {
            
            $features = $layers[0]->searchFeatures($criteria, $paging, $getGeom, $method, $orderBy);
        }
    } else {
        
        if (sizeof($layers) > 1) {
            
            $features = $world->unionFeatureSearch($layers, array(), $paging, $method, $orderBy, $idField);
        } elseif (! is_null($pbox)) {
            
            if (! is_null(RequestUtil::Get('distance'))) {
                $distance = RequestUtil::Get('distance');
                $lon = $projector->centerLon;
                $lat = $projector->centerLat;
                $features = $layers[0]->searchFeaturesByDistance($lon, $lat, $distance, implode(",", $ROI), false);
            } else {
                $features = $layers[0]->searchFeaturesByBbox($ROI[0], $ROI[1], $ROI[2], $ROI[3], false, $projection);
            }
        } elseif (! is_null($bbox)) {
            $features = $layers[0]->searchFeaturesWithinBBox($bbox, $project->projection, array(), $paging, $method, $getGeom, $orderBy);
        } else {
            $features = $layers[0]->getRecords($orderBy, null, $paging);
        }
    }
    
    // $paging->setResults($features);
    $attributeExclusions = array(
        'the_geom',
        'box_geom'
    );
    $needLabel = true;
    $firstRecord = array();
    if ($features) {
        
        if ($features->RecordCount() > 0) {
            
            $features->MoveFirst();
            $firstRecord = $features->FetchRow();
            $needLabel = (array_key_exists('label', $firstRecord) == false);
            $features->MoveFirst();
        }
    }
    // $template->assign('needLabel',$needLabel);
    if (! $getGeom)
        array_push($attributeExclusions, 'wkt_geom');
    $template->assign('exclusions', $attributeExclusions);
    // $template->assign('project', $project);
    $template->assign('layerid', $projectLayer->layer->id);
    $template->assign('plid', $projectLayer->id);
    $template->assign('features', $features);
    $template->assign('paging', $paging);
    $geomTypes = GeomTypes::GetEnum();
    $template->assign('layerType', $geomTypes[$layer->geom_type]);
    // and now print the list of ProjectLayers
    
    $error = isset($error) ? $error : null;
    if ($error) {
        return print($error);
    }
    
    // show it, depending on the format
    if (! RequestUtil::HasParam('format'))
        RequestUtil::Set('format', WAPI::FORMAT_JSON);
  
    switch (RequestUtil::Get('format')) {
        case 'csv':
        case 'xls':
            $fields = array();
            
            if (count($features) > 0) {
                $firstRecord = (! is_null($features)) ? $firstRecord : array();
                $attributes = (! is_null($features)) ? $layers[0]->getAttributesVerbose() : array();
                foreach ($firstRecord as $key => $val) {
                    if (! in_array($key, $attributeExclusions)) {
                        if ($attributes) {
                            $fields[] = ($key == 'gid') ? "Result Id" : $attributes[$key]['display'];
                        } else {
                            $fields[] = $key;
                        }
                    }
                }
            }
            $f = fopen("php://output", 'w');
            $format = RequestUtil::Get('format');
            WAPI::SetWapiHeaders($format, 'results' . $format);
            fputcsv($f, $fields);
            foreach ($features as $row) {
                foreach ($attributeExclusions as $attr) {
                    unset($row[$attr]);
                }
               
                fputcsv($f, $row);
            }
            break;
        case 'xml':
            WAPI::SetWapiHeaders('xml');
            WAPI::WriteXMLStart();
            $fields = array();
            if (count($features) > 0) {
                $firstRecord = (! is_null($features)) ? $firstRecord : array();
                $attributes = (! is_null($features)) ? $layers[0]->getAttributesVerbose() : array();
                foreach ($firstRecord as $key => $val) {
                    if (! in_array($key, $attributeExclusions)) {
                        if ($attributes) {
                            $fields[] = ($key == 'gid') ? "Result Id" : $attributes[$key]['display'];
                        } else {
                            $fields[] = $key;
                        }
                    }
                }
            }
            $template->assign('fields', $fields);
            $template->assign('results', $features);
            $template->display('wapi/listfeatures.tpl');
            break;
        case 'php':
            print serialize($features);
            break;
        default:
            return;
    }
}

?>