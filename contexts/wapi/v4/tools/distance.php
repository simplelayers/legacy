<?php
use utils\ParamUtil;
use utils\SQLUtil;
/**
 * Purpose: Return the distance between two points.
* Since this doesn't use a project or layer, there is no authentication done at all.
*
* Parameters:
*
* points -- A pie-joined list of comma-joined coordinates: lat1,lon1|lat2,lon2|lat3,lon3|...
*           Coordinates should be in latlong using WGS84/NAD83.
*
* Return:
*
* A block of XML showing the set of points, the length of each segment, and the total distance. All distances are expressed in a variety of measures.
* {@example docs/examples/viewerdistance.xml}
*
* @package ViewerDispatchers
*/

/**
 * @ignore
 */
function _exec() {
    $sys = System::Get();
    $args = WAPI::GetParams();
    $user = SimpleSession::Get()->GetUser();
    $wapi = System::GetWapi();
    
    $points = ParamUtil::RequiresOne($args,'points');
    
    $points = trim($points,(' ,'));
    $points = explode(',',$points);
    if(count($points) < 2) throw new Exception('Too feew points');
    
    // go through each point and find the segment distance.
    // keep track of the total for each units, and also of each segment's details for later reporting.
    $segments = array();
    $total   = array('feet'=>0, 'miles'=>0, 'meters'=>0, 'kilometers'=>0);
    $info = SQLUtil::GetDistances($points,ParamUtil::Get($args,'pt_order','latlon'));
    
    WAPI::SendSimpleResponse(array('distances'=>$info));
}

/*function ResetProjector( $world, $request ) {

    $defaultProj4 = $world->projections->defaultProj4;
    $projector = new Projector_MapScript();
    $projector->SetViewExtents($request['bbox']);
    $projector->SetViewSize( $request['width'],$request['height']);
    return $projector;
}*/

?>
