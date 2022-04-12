<?php
use utils\Pixospatial;
use utils\ParamUtil;

/**
 * Submit a SQL-style query against a vector data layer, returning a set of results.
 *
 * Parameters:
 *
 * layer -- The layer-ID to query, e.g. 1234.
 *
 * fields -- A comma-joined list of fields to retrieve, e.g. analytecode,result,qualifier
 *
 * where -- The SQL-style WHERE clause, e.g. address like '1234%'
 *
 * limit -- The limit, max number of records to retrieve. Generates the LIMIT clause. Optional.
 *
 * offset -- The offset, max number of records to retrieve. Generates the OFFSET clause. Optional.
 *
 * distinct -- If set to 1, a DISTINCT tag will be added so only unique rows are returned.
 *
 * sort -- The sorting field, or comma joined list of sorts. Used to generate the ORDER BY clause. Optional.
 *
 * format -- String, one of: xml, php. This specifies te output format for the records. Only xml is well supported.
 *
 * Return:
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
function _config_getfeatures()
{
    $config = Array();
    // Start config
    $config["header"] = false;
    $config["footer"] = false;
    $config["customHeaders"] = true;
    // Stop config
    return $config;
}

function _headers_getfeatures()
{}

function _dispatch_getfeatures($template, $args)
{
    switch (RequestUtil::Get('format', 'xml')) {
        case ('csv'):
            header('Content-Encoding: UTF-8');
            header('Content-type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename=SearchResults.csv');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            break;
        case ('xls'):
            header('Content-Encoding: UTF-8');
            header('Content-type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename=SearchResults.xls');
            break;
        default:
            header('Content-type: text/xml');
    }
    
    $world = System::Get();
    $user = SimpleSession::Get()->GetUser();
    
    $paging = new Paging();
    
    $getGeom = isset($_REQUEST['geom']) ? true : false;
    $method = isset($_REQUEST['method']) ? $_REQUEST['method'] : 'OR';
    $orderBy = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'gid';
    $idField = isset($_REQUEST['idfield']) ? $_REQUEST['idfield'] : 'gid';
    $bbox = isset($_REQUEST['bbox']) ? $_REQUEST['bbox'] : null;
    $pbox = RequestUtil::Get('pxrect');
    
    $userId = is_null($user) ? 0 : $user->id;
    
    if (isset($_REQUEST['featureId'])) {
        $projectLayer = ProjectLayer::Get($_REQUEST['playerid']);
        $featureId = $_REQUEST['featureId'];
        $layerId = $_REQUEST['layerid'];
        /* @var  $targetLayer  Layer */
        $project = $projectLayer->project;
        $canSearch = $project->getPermissionById($userId) >= AccessLevels::READ;
        $features = $projectLayer->layer->searchByFeature($featureId, $layerId);
        $layerType = $projectLayer->layer->type;
        $layers[] = $projectLayer;
    } else {
        $project = $world->getProjectById($_REQUEST['project']);
        $canSearch = $project->getPermissionById($userId) >= AccessLevels::READ;
        // Step 2: Verify that the layer parameter has been set
        if (! isset($_REQUEST["layer"])) {
            denied('layer not specified');
            return;
        }
        $projectLayer = (isset($_REQUEST['layer'])) ? $_REQUEST['layer'] : false;
        
        if (! $projectLayer) {
            denied('Invalid layer');
            return;
        }
        $projectLayers = array();
        if (stripos($projectLayer, ",")) {
            $projectLayers = explode(",", $projectLayer);
        } else {
            $projectLayers[] = $projectLayer;
        }
        
        // Step 5: Turn array of layer ids into an array of Layer objects.
        $layers = array();
        
        foreach ($projectLayers as &$projectLayer) {
            $layerid = $projectLayer;
            
            $projectLayer = new ProjectLayer($world, $project, $projectLayer);
            
            if (! $projectLayer) {
                
                denied('invalid layer:' . $layerid);
                return;
            }
            $layerType = $projectLayer->layer->type;
            
            if ($layerType != LayerTypes::VECTOR and $layerType != LayerTypes::RELATIONAL and $layerType != LayerTypes::ODBC) {
                denied('incorrect layer type:' . $layerid);
                return;
            }
            
            if (! $canSearch) {
                if (! $projectLayer->searchable) {} else {
                    $layers[] = $projectLayer->layer;
                }
            } else {
                $layers[] = $projectLayer->layer;
            }
        }
        
        if (isset($_REQUEST['pxrect'])) {
            $template->assign('pbox', $pbox);
            list ($x1, $y1, $x2, $y2) = explode(",", $pbox);
            $pwidth = (int) $x2 - (int) $x1;
            $pheight = (int) $y2 - (int) $y1;
            $projection = isset($_REQUEST['projection']) ? $_REQUEST['projection'] : $project->projectionSRID;
            // $projection = $world->projecitons->defaultSRID;
            $targetProj4 = $world->projections->getProj4BySRID($projection);
            // $mapper->projection = $project->projection;
            // //error_log("projection:".$mapper->projection);
            // $projector = new Projector_MapScript ();
            // $extents = $projector->ProjectExtents ( $world->projections->defaultProj4, $targetProj4, $_REQUEST ['width'], $_REQUEST ['height'], $bbox );
            // $projector->ZoomTo ( $x1, $y1, $x2, $y2 );
            // $projector->SetViewSize ( $pwidth, $pheight );
            // $extentsAfter = $projector->ProjectExtents( $targetProj4 , $world->projections->defaultProj4 , $pwidth, $pheight, $extents['to'] );
            // $projector->ProjectExtents( $targetProj4, $world->projections->defaultProj4, $_REQUEST['width'], $_REQUEST['height'],$bbox );
            $pixo = Pixospatial::Get($bbox, $_REQUEST['width'], $_REQUEST['height']);
            $ROI = $pixo->ROI_to_BBOX($x1, $y1, $x2, $y2);
            
            // $ROI = $projector->GetROIExtents ( 'array' );
        }
        
        $features = array();
        // Step 6: Either process criteria or do a general feature query on the layer
        if (! is_null(RequestUtil::Get('criteria'))) {
            
            $searchTerms = array();
            $_REQUEST['criteria'] = trim($_REQUEST['criteria']);
            $criteria = explode(";", $_REQUEST['criteria']);
            
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
                if($logic == '') array_pop($criterion);
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
                
                if (isset($_REQUEST['distance'])) {
                    $distance = $_REQUEST['distance'];
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
    if ($projectLayer) {
        $template->assign('layerid', $projectLayer->layer->id);
        $template->assign('plid', $projectLayer->id);
    }
    
    $template->assign('features', $features);
    $template->assign('paging', $paging);
    $geomTypes = GeomTypes::GetEnum();
    $template->assign('layerType', $geomTypes[$layerType]);
    // and now print the list of ProjectLayers
    
    $error = isset($error) ? $error : null;
    if ($error)
        return print $error;

    
        // show it, depending on the format
    if (! isset($_REQUEST['format']))
        $_REQUEST['format'] = 'xml';
    
    switch ($_REQUEST['format']) {
        case 'csv':
        case 'xls' :
            $attributeExclusions = array(
                'the_geom',
                'box_geom'
            );
           
            $layer = array_pop($layers);
            if(is_a($layer,'ProjectLayer')) $layer = $layer->layer;
            
            $attributes = ($layer->field_info) ?  $layer->field_info : $layer->getAttributesVerbose();
            
            $fields = array();
            
            $fieldNames = array();
            if ($features->RecordCount() > 0) {
                $firstRecord = $features->FetchRow();
                $resultFields = array_keys($firstRecord);
                
                
                $attributes = $layer->field_info;
                if($attributes) {
                    foreach ($attributes as $attInfo) {
                        $att = $attInfo['name'];
                        if (! $attInfo['visible'])
                            continue;
                        if (in_array($att, $attributeExclusions))
                            continue;
                        $label = $attInfo['display'];
                        if ($att == 'gid')
                            $label = 'Result Id';
                        $fields[$att] = $label;
                    }
                } else {
                    foreach($resultFields as $field) {
                        $label = $field;
                        if($label=='gid') $label='Result Id';
                        if(!in_array($field,$attributeExclusions)) $fields[$field] = $label;
                    }
                }
                //$features->MoveFirst();
            }
            $fieldNames = array_keys($fields);
            $f = fopen("php://output", 'w');
            fputcsv($f, array_values($fields));
       
            //fputcsv($f, ParamUtil::SubsetAssocArray($firstRecord, $fieldNames));
             foreach ($features as $row) {
                $row = ParamUtil::SubsetAssocArray($row, $fieldNames);
                
                fputcsv($f, $row);
            }
            /*
			$fields = array();
            if (count($features) > 0) {
                $firstRecord = (! is_null($features)) ? $firstRecord : array();
                
                $layer = $layers[0];
                $layer = is_a($layer, 'ProjectLayer') ? $layer->layer : $layer;
                
                $attributes = (! is_null($features)) ? $layer->getAttributesVerbose() : array();
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
            fputcsv($f, $fields);
            foreach ($features as $row) {
                foreach ($attributeExclusions as $attr) {
                    unset($row[$attr]);
                }
                fputcsv($f, $row);
            }*/
            break;
        case 'xml':
            $fields = array();
            if (count($features) > 0) {
                $firstRecord = (! is_null($features)) ? $firstRecord : array();
                
                $attributes = array();
                if (! is_null($features)) {
                    $attributes = is_a($layers[0], 'ProjectLayer') ? $layers[0]->layer->getAttributesVerbose() : $layers[0]->getAttributesVerbose();
                }
                
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