<?php
use utils\ParamUtil;
/**
 * A OGC WFS server, servicing OGC standard WFS requests.
 *
 * Example URL:
 * https://www.cartograph.com/?do=ogcwfs&project=123&SERVICE=WFS&VERSION=1.0.0&REQUEST=GetCapabilities
 *
 * Example URL:
 * https://www.cartograph.com/?do=ogcwfs&project=123&SERVICE=WFS&REQUEST=DescribeFeatureType&typename=123
 *
 * Example URL:
 * https://www.cartograph.com/?do=ogcwfs&project=123&SERVICE=WFS&REQUEST=GetFeature&typename=123
 *
 * Example URL:
 * https://www.cartograph.com/?do=ogcwfs&project=123&SERVICE=WFS&REQUEST=GetFeature&typename=123&BBOX=-100,40,-90,50
 *
 * {@link http://www.opengeospatial.org/standards/wfs Official WFS specification at OGC}
 *
 * {@link http://mapserver.gis.umn.edu/docs/howto/wfs_server A more practical example of WFS queries}
 *
 * Note that this WFS server is not completely OGC compliant, as its goal
 * is to implement functionality requested by paying clients. Known deviations:
 *
 * The SRS= param is completely ignored and not required.
 * The output will always be in EPSG:4326 which is latlong using NAD83/WGS84 datum.
 *
 * The VERSION= param is completely ignored.
 *
 * For GetFeature requests, only BBOX is supported for filtering. No FEATUREID, CQL, etc.
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

// the URL of this here WFS server, and other commonly-used goodies
$template->assign('onlineresource', BASEURL."/wapi/map/ogcwfs/project:{$project->id}/?&" );

// handle the request
switch(strtolower(@$_GET['request'])) {
    case 'getcapabilities':
        header('Content-type: text/xml');
        $bbox = explode(',',$project->bbox);
        $template->assign('bboxlx', $bbox[0] );
        $template->assign('bboxly', $bbox[1] );
        $template->assign('bboxux', $bbox[2] );
        $template->assign('bboxuy', $bbox[3] );
        $template->assign('title', $project->name );
		
        $template->assign('projectlayers', $project->getLayers() );
        $template->display('ogc/ogcwfs_capabilities.tpl');
        break;
    case 'describefeaturetype':
        // check for missing params
        if (!@$_GET['typename']) return print "Missing required parameter: TYPENAME";

        // fetch the Layer
        $layerid  = str_replace('LAYER_','',strtoupper($_GET['typename']));
        $layer    = $project->getLayerById($layerid)->layer;
        $template->assign('layer',$layer);
        $template->assign('typename', $_GET['typename'] );

        // correct its attributes' data types to fit the standard
        $attributes = array();
        foreach ($layer->getAttributes() as $n=>$t) {
            switch ($t) {
                case 'text':
                    $t = 'string';
                    break;
                case 'float':
                    $t = 'double';
                    break;
                case 'int':
                    $t = 'integer';
                case 'url':
                	$t = 'string';
                    break;
            }
            $attributes[$n] = $t;
        }
        $template->assign('attributes',$attributes);

        header('Content-Type: application/xml');
        $template->display('ogc/ogcwfs_featurexsd.tpl');
        break;
    case 'getfeature':
        // check for missing params
        if (!@$_GET['typename']) return print "Missing required parameter: TYPENAME";

        // fetch the Layer
        $layerid  = str_replace('LAYER_','',strtoupper($_GET['typename']));
        
        $layer    = $project->getLayerById($layerid)->layer;
        $template->assign('layer',$layer);

        // fetch the features matching the BBOX request, if there was one
        if (@$_GET['bbox']) {
            $bbox    = explode(',',$_GET['bbox']);
            $records = $layer->searchFeaturesByBbox($bbox[0],$bbox[1],$bbox[2],$bbox[3],'GML');
        } else {
            $bbox    = $layer->getExtent();
            $records = $layer->getRecords(null,'GML')->getRows();
        }
		$template->assign('bbox',$bbox);
        // show the matching records and attributes
        $template->assign('bboxlx', $bbox[0] );
        $template->assign('bboxly', $bbox[1] );
        $template->assign('bboxux', $bbox[2] );
        $template->assign('bboxuy', $bbox[3] );
        $template->assign('records',$records);
        $template->assign('attributes', array_keys($layer->getAttributes()) );
        $template->assign('typename', $_GET['typename'] );

        header('Content-Type: application/xml');
        $template->display('ogc/ogcwfs_gml2.tpl');
        break;
    default:
        return print "Parameter REQUEST= must be one of the following: GetCapabilities GetFeature DescribeFeatureType";
        break;
}

}?>
