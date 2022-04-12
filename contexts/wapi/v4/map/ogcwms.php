<?php
use utils\ParamUtil;
/**
 * A WMS server, using OGC standard WMS requests.
 *
 * Example URL:
 * https://www.cartograph.com/?do=ogcwms&project=123&SERVICE=WMS&REQUEST=GetCapabilities
 *
 * {@link http://www.opengeospatial.org/standards/wms Official WMS specification at OGC}
 *
 * {@link http://mapserver.gis.umn.edu/docs/howto/wms_server A more practical example of WMS queries}
 *
 * Note that this WMS server is not completely OGC compliant, as its goal
 * is to implement functionality requested by paying clients. Known deviations:
 *
 * The SRS= param is completely ignored and not required.
 * The output image will always be in EPSG:4326 which is latlong using NAD83/WGS84 datum.
 *
 * The VERSION= param is completely ignored.
 *
 * @package Dispatchers
 */
#error_log($_REQUEST['LAYERS']);


/**
 * @ignore
 */


function _exec($template, $args) {
$user = SimpleSession::Get()->GetUser();
$world = System::Get();

$params = WAPI::GetParams();


// convert the mixed-case request params to all-lowercase because WMS does not
// specify capitalization for params, e.g. REQUEST= or request=
$get = array(); foreach ($_REQUEST as $k=>$v) $get[strtolower($k)] = $v; $_GET = $get;

// fetch the project and verify their permission to it
$project = $world->getProjectById(ParamUtil::Get($params,'project'));

if (!$project) return print "missing required paramter: project";
$permission = $project->allowlpa ? AccessLevels::READ : $project->getPermissionById(@$user->id);
if ($permission < AccessLevels::READ) return print DENIED_NEEDREAD;
$template->assign('project',$project);

// handle the request
switch(strtolower(ParamUtil::Get($params,'request'))) {
    case 'getmap':
        // check for required parameters
        if (!@$_GET['width'])  return print "Missing required parameter: WIDTH";
        if (!@$_GET['height']) return print "Missing required parameter: HEIGHT";
        if (!@$_GET['bbox'])   return print "Missing required parameter: BBOX";
        if (!@$_GET['layers']) return print "Missing required parameter: LAYERS";
        if (!@$_GET['format']) return print "Missing required parameter: FORMAT";

        // split apart the list of layers and the list of styles
        $targetLayers = explode(',',$_GET['layers']);
        
        $layerids = array();
        $layerList =  $project->getLayers(true,'ASC');
        /* @var $projectLayer ProjectLayer */
        /*
        foreach($layerList as $projectLayer) {
        	$id = $projectLayer->layer->id;
        	if( in_array($id,$targetLayers) ) $layerids[] = $id;
        	if(count($targetLayers)==0) $layerids[]=$id;
        }*/
        $styles   = @$_GET['styles'] ? explode(',',$_GET['styles']) : array();
        if (sizeof($styles) and sizeof($styles)!=sizeof($layerids)) return print "If STYLES= is given, must be same length as LAYERS=";

        // call the Mapper and add layers
        $mapper = $world->getMapper();
       
        $mapper->width  = (integer) $_GET['width'];
        $mapper->height = (integer) $_GET['height'];
        $mapper->extent = explode(",",$_GET['bbox']);
        ;
        $mapper->lowquality = true;
         $mapper->init();
         $i=-1;
         
        foreach ($targetLayers as $plid) {
        	$i++;
        	
        	$projectlayer =  ProjectLayer::Get($plid);//$project->getLayerById($layerids[$i]);
           if (! $projectlayer) continue;
            // should we enable labels?
            $labels = $projectlayer->labels_on;
            if(isset($styles[$i])) {
            if (preg_match('/\blabels\b/', $styles[$i]) and $projectlayer->labelitem) $labels = true;
            if (preg_match('/\bnolabels\b/', $styles[$i]) or ! $projectlayer->labelitem) $labels = false;
            // should we force high-quality or low-quality rendering? This really isn't per layer, but that's what we have to work with
            if (preg_match('/\bhighquality\b/', $styles[$i])) $mapper->lowquality = false;
            if (preg_match('/\blowquality\b/', $styles[$i])) $mapper->lowquality = true;
            }
            // ready!
            if ($projectlayer) {
            	$mapper->addLayer($projectlayer,1.00,$labels);
            }
            
            
        }
        // set the image format, and spit it out
       switch ($_GET['format']) {
            case 'image/png';
            default:
                header('Content-type: image/png',true);
                break;
            case 'image/jpeg';
                header('Content-type: image/jpeg',true);
                $mapper->screenshot = true;
                break;
            
        }
        print $mapper->renderStream(false);
        break;
    case 'getcapabilities':
        $bbox = explode(',',$project->bbox);
        $minx = min($bbox[0],$bbox[2]);
        $miny = min($bbox[1],$bbox[3]);
        $maxx = max($bbox[0],$bbox[2]);
        $maxy = max($bbox[1],$bbox[3]);
        
        $template->assign('title', $project->name );
        $url =  $_SERVER['REQUEST_URI'];
        
        $resource = BASEURL . 'wapi/map/ogcwms/application:wms/project:'.$project->id.'/';
        $template->assign('onlineresource', $resource );
        $template->assign('bboxlx', $minx );
        $template->assign('bboxly', $miny );
        $template->assign('bboxux', $maxx );
        $template->assign('bboxuy', $maxy );
        $template->assign('projectlayers', $project->getLayers(true,'DESC') );
       	header('Content-Type: application/xml');
        $template->display('ogc/ogcwms_capabilities.tpl');
        break;
    default:
        return print "Parameter REQUEST= must be one of the following: GetCapabilities GetMap";
        break;
}

}?>
