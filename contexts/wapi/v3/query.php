<?php

use utils\ParamUtil;
use model\mapping\PixoSpatial;

function _config_query()
{
    $config = array();
    return WAPI::DecorateConfig($config);
}

function _headers_query()
{
    header('Content-Type: text/plain');
    
    // header ( 'Content-Type: text/html' );
    /*
     * if (! isset ( $_REQUEST ['format'] ))
     * $_REQUEST ['format'] = 'json';
     * switch ($_REQUEST ['format']) {
     * case "json" :
     * case "ajax" :
     * header ( 'Content-Type: application/json' );
     * break;
     * case "xml" :
     * header ( 'Content-Type: text/xml' );
     * break;
     * case "phpserial" :
     * header ( 'Content-Type: text/plain' );
     * break;
     * }
     */
}

function _dispatch_query($template, $args)
{
    $world = System::Get();
    $wapi = $world->wapi;
    $projectInfo = $world->wapi->RequireProject();
    $project = $projectInfo['project'];
    $permission = $projectInfo['permission'];
    unset($projectInfo);
    
    $user = SimpleSession::Get()->GetUser();
    $template->assign('user', $user);
    
    // $_REQUEST,$world,$user,$template,$project,$embedded,$permission) {
    
    if ($permission < AccessLevels::READ)
        denied('You do not have permission to view this project.');
        
        // error_log(var_export($_REQUEST,true));
    $bbox = RequestUtil::Get('bbox', null);
    $template->assign('bbox', $bbox);
    $pbox = RequestUtil::Get('pxrect', null);
    $template->assign('pbox', $pbox);
    list ($x1, $y1, $x2, $y2) = explode(",", $pbox);
    $x1 = (int) $x1;
    $x2 = (int) $x2;
    $y1 = (int) $y1;
    $y2 = (int) $y2;
    
    $pwidth = max($x1, $x2) - min($x1, $x2);
    $pheight = max($y1, $y2) - min($y1, $y2);
    if ($pwidth == 0)
        $x1 -= 1;
    if ($pheight == 0)
        $y1 -= 1;
    
    $projection = RequestUtil::Get('projection', $project->projectionSRID);
    
    // $projection = $world->projecitons->defaultSRID;
    $targetProj4 = $world->projections->getProj4BySRID($projection);
    // $mapper->projection = $project->projection;
    // //error_log("projection:".$mapper->projection);
    
    $projector = new Projector_MapScript();
    $extents = $projector->ProjectExtents($world->projections->defaultProj4, $targetProj4, RequestUtil::Get('width'), RequestUtil::Get('height'), $bbox);
    $projector->ZoomTo($x1, $y1, $x2, $y2);
    $projector->SetViewSize($pwidth, $pheight);
    
    
    
     $pixo = new PixoSpatial($bbox, $_REQUEST['width'], $_REQUEST['height']);
     $ROI = $pixo->GetViewROI($x1, $y1, $x2, $y2);    

    
    // figure up the bbox and whether to add geometries into the output
    $geom = (bool) (int) RequestUtil::Get('geom');
    $template->assign('geom', $geom);
    $paging = new Paging("start", "limit");
    
    // if a list of GIDs was supplied, split it
    $_REQUEST['gids'] = RequestUtil::GetList('gids', ',', null);
    
    // the $results for the template will be a series of 3-tuples:
    // (Layer object, array of fieldnames, array of featurehashes)
    // That is: the layer object, an array of the fieldnames to present for each feature in the output, and an array of features
    $results = array();
    
    $limit = (int) RequestUtil::Get('limit');
    
    $layerSource = RequestUtil::Get('layers', RequestUtil::Get('players'));
    
    $isProjLayers = RequestUtil::HasParam('players');
    $uniqueLayers = array();
    $distance = RequestUtil::Get('distance');
    //System::GetDB()->debug=true;
    // and go for it. keep a count of how many results we have so far, so we can bail when we reach that limit
    foreach (explode('|', $layerSource) as $layerid) {
        // if we've already hit/passed our result limit, then skip out now
        // if ($limit <= 0) break;
        // fetch the ProjectLayer and the underlying Layer item, make sure it's a vector layer
        $fields = "";
        $lastField = "";
        // error_log($layerid);
        if (stripos($layerid, ":"))
            list ($layerid, $fields) = explode(':', $layerid);
        if ($fields !== "") {
            $fields = explode(',', $fields);
            if (sizeof($fields > 0)) {
                $lastField = array_pop($fields);
            }
        }
        if ($lastField !== "") {
            array_push($fields, $lastField);
            $fields = array_unique($fields);
        }
        $projectlayer = null;
        if ($isProjLayers) {
            $projectLayer = new ProjectLayer($world, $project, $layerid);
        } else {
            $projectLayer = null; // $project->getLayerById ( $layerid );
        }
        
        // error_log('projectLayer:'.$projectlayer->layer->id);
        if ($isProjLayers && ! $projectLayer)
            continue;
        $layer = $projectLayer->layer;
        
        if ($fields == "") {
            $fields = array_keys($layer->getAttributes());
        }
     
        if (! in_array('gid', $fields))
            array_unshift($fields, 'gid');
        
        if (! in_array($layer->id, $uniqueLayers)) {
            $uniqueLayers[] = $layer->id;
        } else {
            continue;
        }
        
        if (! $layer)
            continue; // not even a layer, WTF?
                          
        if ($layer->type != LayerTypes::VECTOR and $layer->type != LayerTypes::RELATIONAL and $layer->type != LayerTypes::ODBC)
            continue; // a layer, but not a candidate for searching for features
                          // search the layer by bbox
        
        $these = array();
        $gids = RequestUtil::Get('gids');
        $filterLayer = RequestUtil::Get('filterLayer');
       $distance = (double) $distance;
       
        if ($distance === "0")
            $distance = null;
        if ($distance) {
            $pixo->MoveToROI($ROI);
            $center = $pixo->GetROICenter();
            $lon = $center['lon'];
            $lat = $center['lat'];
            $these = $layer->searchFeaturesByDistance($lon, $lat, $distance, implode(",", $ROI), $geom);
        } else {
            
            //System::GetDB()->debug=true;
            $these = $layer->searchFeaturesByBbox($ROI[0], $ROI[1], $ROI[2], $ROI[3], $geom, $projection);
            //System::GetDB()->debug=false;
        }
        
       
        
        if ($gids && $filterLayer) {
            if ($layerid == $filterLayer) {
                // error_log('layer is filterLayer');
                $these = array_filter($these, create_function('$x', 'return in_array($x["gid"],$_REQUEST["gids"]);'));
            }
        }
        // $these = array_reverse ( $these );
        // $these = array_slice($these,0,$limit);
        
        // $limit -= sizeof($these);
        // and stick the layer and the results onto the output
        if ($these->RecordCount() > 0) {
            array_push($results, array(
                $layer,
                $fields,
                $these,
                $plid = $projectLayer->id
            ));
        }
    }
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
            WAPI::SetWapiHeaders('xml');// header('Content-type: text/xml');
    }
    switch ($_REQUEST['format']) {
        case 'csv':
        case 'xls':
            $attributeExclusions = array(
                'the_geom',
                'box_geom'
            );
            list ($layer, $fields, $features) = $results[0];
            
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
            
           // fputcsv($f, ParamUtil::SubsetAssocArray($firstRecord, $fieldNames));
             foreach ($features as $record) {
                $row = ParamUtil::SubsetAssocArray($record, $fieldNames);
                fputcsv($f, $row);
            }
            
            return;
    }
    $template->assign('project', $project);
    
    $template->assign('results', $results);
    
    // all done!
    $template->display('wapi/query.tpl');
}
?>