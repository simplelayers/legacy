<?php
use utils\Pixospatial;
use utils\ParamUtil;

/**
 */
/**
 *
 * @ignore
 *
 */
function _config_navigate()
{
    $config = Array();
    // Start config
    $config["header"] = false;
    $config["footer"] = false;
    $config["customHeaders"] = true;
    $config['authUser'] = 0;
    $config['sendUser'] = true;
    $config['sendWorld'] = true;
    // Stop config
    return $config;
}

function _headers_navigate()
{
    if (! isset($_REQUEST['format']))
        $_REQUEST['format'] = 'xml';
    switch ($_REQUEST['format']) {
        case "json":
        case "ajax":
            header('Content-Type: application/json');
            break;
        case "xml":
            header('Content-Type: text/xml');
            break;
        case "phpserial":
            header('Content-Type: text/plain');
            break;
    }
}

function _dispatch_navigate($template, $args)
{
    $world = $args['world'];
    $user = $args['user'];
    $request = $_REQUEST;
    
    /*
     * if(isset($_REQUEST['token'])) {
     * $tokenValid = $world->auth->ValidateAppToken( $_REQUEST['token'] , session_id() );
     *
     * //$world->auth->ValidateToken($_REQUEST['token']);
     * $world->auth->ValidateUser();
     * $user = $world->auth->user;
     * } else {
     * $user = $args['user'];
     * }
     */
    switch (isset($_REQUEST['project'])) {
        case false:
            throw new Exception("Could not load project: project {$_REQUEST['project']} not found");
        case true:
            $project = $args['world']->getProjectById((int) $_REQUEST['project']);
            
            list ($embeddable, $bpermission) = $project->checkBrowserPermission($user, $_REQUEST, $_SERVER);
            
            // fetch the permissions and embed status being used for this call
            // if ($bpermission < AccessLevels::READ) {denied('You do not have permission to view this project.');return;}
            $permission = max($embeddable, $bpermission);
            
            if ($permission < AccessLevels::READ) {
                throw new Exception("Permission denied: attempting to view project with permission $permission");
            }
            break;
    }
    
    $bbox = isset($request['bbox']) ? $request['bbox'] : "";
    $navType = $request['type'];
    $projectionSRID = isset($request['projection']) ? $request['projection'] : $project->projectionSRID;
    
    // $projectionSRID = $world->projections->defaultSRID;
    $projection = $world->projections->getProj4BySRID($projectionSRID);
    $default = $world->projections->defaultProj4;
    $projector = new Projector_MapScript();
    $_SESSION[$project->id . "lastNav"] = 0;
    $_SESSION[$project->id . "lastNavTime"] = time();
    $ROI = null;
    if ($navType == 'bboxzoom') {
        $navType = "zoomto";
        $request['x1'] = 0;
        $request['y1'] = 0;
        $request['x2'] = $request['width'];
        $request['y2'] = $request['height'];
    }
    switch ($navType) {
        case "center":
            $_SESSION[$project->id . "lastNav"] = 1;
            $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $bbox);
            $projector->SetMapCenter($request['lon'], $request['lat']);
            break;
        case "pan":
            $_SESSION[$project->id . "lastNav"] = 2;
            $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $bbox);
            $projector->CenterAt($request['posx'], $request['posy']);
            break;
        case "panzoom":
            $_SESSION[$project->id . "lastNav"] = 3;
            $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $bbox);
            $projector->CenterAt($request['posx'], $request['posy'], $request['zoomfactor']);
            $projector->ZoomBy(1);
            $lonLat = array($projector->centerLon,$projector->centerLat);
            $projector->NextScale();
            //$projector->SetMapCenter($lonLat[0],$lonLat[1]);
            
            break;
        case "zoomby":
            $_SESSION[$project->id . "lastNav"] = 4;
            $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $bbox);
            if(intval($request['zoomfactor'])>0) {
                $projector->NextScale();
            } else {
                $projector->PrevScale();
            }
            
            //$projector->ZoomBy($request['zoomfactor']);
            break;
        case "zoomto":
            $_SESSION[$project->id . "lastNav"] = 5;
            $pixo = Pixospatial::Get($bbox, $request['width'], $request['height']);
            
            $ROI = $pixo->GetROI($request['x1'], $request['y1'], $request['x2'], $request['y2']);
            $pixo = new Pixospatial($ROI[0], $ROI[1], $ROI[2], $ROI[3], $request['width'], $request['height']);
            $pixo->FitToView();
            $ROI = $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']);
            $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']));
            $projector->FitToScale();
            $ROI = implode(',', $projector->GetROIExtents());
            
            $template->assign($pixo->GetViewSize('node'));
            $template->assign('view', $pixo->GetViewSize("node"));
            $template->assign('extentsLL', $ROI);
            $template->assign('extentsProj', $ROI);
            $template->display('wapi/navigate.tpl');
            return;
            /*
             * $extents = $projector->ProjectExtents( $default , $projection , $request['width'],$request['height'] , $bbox);
             * $projector->ZoomTo( $request['x1'] , $request['y1'] , $request['x2'] , $request['y2'] );
             */
            break;
        case "featurezoom":
            $_SESSION[$project->id . "lastNav"] = 6;
            $vbbox = ParamUtil::Get($_REQUEST, 'bbox');
            
            /* @var $layer Layer */
            $layer = $world->getLayerById($request['layer']);
            $features = explode(',', $request['feature']);
            $feature = $features[0];
            $feature = $layer->getRecordById($feature);
            if (is_null($feature['wkt_geom'])) {
                break;
            }
            
            list ($x1, $y1, $x2, $y2) = explode(',', $vbbox);
            $xdelta = $x2 - $x1;
            $ydelta = $y2 - $y1;
            
            if ($xdelta > $ydelta) {
                $degPerPx = $ydelta / $request['height'];
            } else {
                $degPerPx = $xdelta / $request['width'];
            }
            $delta = min($xdelta, $ydelta);
            
            $buffer = 0.5; // five pixel buffer
            
            $zoomDelta = $delta / $buffer . 
            
            // $buffer= ($layer->geomtypestring == 'point') ? $degPerPx*(.5*$request['width']) : $buffer;
            
            $newBBOX = $layer->GetBounds($features, $buffer);
            
            $newBBOX = array_values($newBBOX); // (',',$newBBOX);
            
            $pixo = Pixospatial::Get($newBBOX, $request['width'], $request['height']);
            $pixo->FitToView();
            $ROI = $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']);
            //$ROI = implode(',', $pixo->PaddROI($ROI));
            $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']));
            $projector->FitToScale();
            
            $ROI = implode(',', $projector->GetROIExtents());
            
            /*
             * $pixo = Pixospatial::Get($ROI, $request['width'], $request['height']);
             * $pixo->FitToView();
             * $ROI = $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']);
             */
            // $ROI = $pixo->ROI_to_BBOX(0,-10,$request['width']+20,$request['height']+20);
            
            // }
            
            $template->assign('view', $pixo->GetViewSize("node"));
            $template->assign('extentsLL', $ROI);
            $template->assign('extentsProj', $ROI);
            $template->display('wapi/navigate.tpl');
            return;
            
            break;
        case "layerzoom":
            $_SESSION[$project->id . "lastNav"] = 7;
            // $projectLayer = $project->getLayerById($request['layer']);
            // ();
            // $layer = $projectLayer->layer;
            $layer = $world->getLayerById($request['layer']);
            
            $bbox = $layer->getCommaExtent();
            $newBBOX = $bbox;
            $newBBOX = array_values(explode(',', $newBBOX));
            
            $pixo = Pixospatial::Get($newBBOX, $request['width'], $request['height']);
            $pixo->FitToView();
            $ROI = $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']);
            $ROI = implode(',', $pixo->PaddROI($ROI));
            $pixo = Pixospatial::Get($ROI, $request['width'], $request['height']);
            $pixo->FitToView();
            $ROI = $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']);
            $template->assign($pixo->GetViewSize('node'));
            
            $template->assign('view', $pixo->GetViewSize("node"));
            $template->assign('extentsLL', $ROI);
            $template->assign('extentsProj', $ROI);
            $template->display('wapi/navigate.tpl');
            return;
            // $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $bbox);
            // $projector->ZoomBy(- 1);
            
            break;
        case "zoomlevel":
            $_SESSION[$project->id . "lastNav"] = 8;
            $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $bbox);
            $projector->SetZoomLevel($request['level']);
            break;
        case "resize":
            $_SESSION[$project->id . "lastNav"] = 9;
            $pixo = Pixospatial::Get($bbox, $request['oldwidth'], $request['oldheight']);
            $pixo->Resize($request['width'], $request['height']);
            $ROI = $pixo->ROI_to_BBOX(0, 0, $request['width'], $request['height']);
            
            $template->assign($pixo->GetViewSize('node'));
            $template->assign('view', $pixo->GetViewSize("node"));
            $template->assign('extentsLL', $ROI);
            $template->assign('extentsProj', $ROI);
            $template->display('wapi/navigate.tpl');
            return;
        
        // $startsize = explode(",",$project->windowsize);
        // $project = $world->getProjectById($request['project']);
        // $extents = $projector->ProjectExtents($default, $projection, $request['oldwidth'], $request['oldheight'], $request['bbox']);
        // $projector->CropToView( $request['oldwidth'],$request['oldheight'],$request['width'],$request['height'],0,0);
        // $projector->CenterAt($request['width']/2,$request['height']/2);
        case "zoom_level":
            $_SESSION[$project->id . "lastNav"] = 10;
            $extents = $projector->ProjectExtents($default, $projection, $request['width'], $request['height'], $bbox);
            $level = ParamUtil::Requires($_SESSION,'level',17);
            $projector->ZoomToLevel($level);
            
            break;
    }
    if (is_null($ROI)) {
       // $projector->FitToScale();
        $ROI = $projector->getROIExtents("string");
        $extentsAfter = $projector->ProjectExtents($projection, $default, $request['width'], $request['height'], $ROI);
        $template->assign('view', $projector->GetROISize("node"));
        $template->assign('extentsLL', $extentsAfter['to']);
        $template->assign('extentsProj', $extentsAfter['from']);
    }
    
    // all done!
    $template->display('wapi/navigate.tpl');
}
?>