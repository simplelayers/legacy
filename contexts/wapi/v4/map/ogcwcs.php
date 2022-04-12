<?php
use utils\ParamUtil;
/**
 * A OGC WCS server, serving OGC standard WCS requests.
 *
 * Example URL:
 * https://www.cartograph.com/?do=ogcwcs&project=123&SERVICE=WCS&REQUEST=GetCapabilities
 *
 * Example Request:
 * https://www.cartograph.com/?do=ogcwcs&project=123&SERVICE=WCS&REQUEST=GetCoverage&project=123&bbox=-90,30,-80,40&width=1000&height=1000&Coverage=12,3456,789
 *
 * {@link http://www.opengeospatial.org/standards/wcs Official WCS specification at OGC}
 *
 * {@link http://mapserver.gis.umn.edu/docs/howto/wcs_server A more practical example of WCS queries}
 *
 * Note that this WCS server is not completely OGC compliant, as its goal
 * is to implement functionality requested by paying clients. Known deviations:
 *
 * The CRS= param is completely ignored and not required. The output image will always be in
 * urn:ogc:def:crs:OGC:1.3:CRS84 which is the same as EPSG:4326, latlong using NAD83/WGS84 datum.
 *
 * The VERSION= param is completely ignored.
 *
 * The FORMAT= param is completely ignored. Output is always GeoTIFF (aka GTiff).
 *
 * The TIME= param is completely ignored. It will never be supported as we do not store snapshots of data over time.
 *
 * The COVERAGE= param may be a comma-joined list of layers, and a GeoTIFF will be created of the layers merged together.
 *
 * The DescribeCoverage request is not supported.
 *
 * @package Dispatchers
 */
/**
 * @ignore
 */

function _exec($template, $args) {
$user = SimpleSession::Get()->GetUser();
$world = System::Get();

// convert the mixed-case request params to all-lowercase because WMS does not
// specify capitalization for params, e.g. REQUEST= or request=
$get = array(); foreach ($_GET as $k=>$v) $get[strtolower($k)] = $v; $_GET = $get;

// fetch the project and verify their permission to it
$project = $world->getProjectById(ParamUtil::Get(WAPI::GetParams(),'project'));
if (!$project) return print DENIED_NOPROJECT;
$permission = $project->allowlpa ? AccessLevels::READ : $project->getPermissionById(@$user->id);
if ($permission < AccessLevels::READ) return print DENIED_NEEDREAD;
$template->assign('project',$project);

// handle the request
switch(strtolower($_GET['request'])) {
    case 'getcoverage':
        // make sure required params are present
        if (!@$_GET['width'])      return print "Missing required parameter: WIDTH";
        if (!@$_GET['height'])     return print "Missing required parameter: HEIGHT";
        if (!@$_GET['bbox'])       return print "Missing required parameter: BBOX";
        if (!@$_GET['coverage'])   return print "Missing required parameter: COVERAGE";

        // call the Mapper and have it generate an image for us
        $mapper = $world->getMapper();
        $mapper->geotiff = false;
        $mapper->width   = (integer) $_GET['width'];
        $mapper->height  = (integer) $_GET['height'];
        $mapper->extent  = explode(",",$_GET['bbox']);
        $mapper->lowquality = true;
        $mapper->init();
        
        foreach (explode(',',$_GET['coverage']) as $layerid) {
        	$projectlayer =  ProjectLayer::Get($layerid);//$project->getLayerById($layerid);
            
            if (! $projectlayer) continue;
            $labels = (bool) (int) $projectlayer->labels_on;
            if ($projectlayer) $mapper->addLayer($projectlayer,1.00,$labels);
        }
        
        // render it and spit it out
       header("Content-disposition: attachment; filename=WCS_{$_GET['coverage']}.png");
       header('Content-type: image/png',true);
        print $mapper->renderStream();
        break;
    case 'getcapabilities':
        $bbox = explode(',',$project->bbox);
        $template->assign('title', $project->name );
        $template->assign('onlineresource', BASEURL."wapi/map/ogcwcs/project:{$project->id}/?&" );
        $template->assign('bboxlx', $bbox[0] );
        $template->assign('bboxly', $bbox[1] );
        $template->assign('bboxux', $bbox[2] );
        $template->assign('bboxuy', $bbox[3] );
        $template->assign('projectlayers', $project->getLayers() );
        header('Content-type: text/xml');
        $template->display('ogc/ogcwcs_capabilities.tpl');
        break;
    default:
        return print "Parameter REQUEST= must be one of the following: GetCapabilities GetCoverage";
        break;
}

}?>
