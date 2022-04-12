<?php


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
function _config_getdistance() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config['sendWorld'] = true;
	$config['requireToken'] = true;
	// Stop config
	return $config;
}

function _headers_getdistance() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'xml';
	switch($_REQUEST['format'] ) {
		case "json":	
		case "ajax":
			header('Content-Type: application/json');
			return;
		case "xml":
			header('Content-Type: text/xml');
			return;
		
			
	}	

}


/**
  * @ignore
  */
function _dispatch_getdistance($template, $args) {
	$world=$args['world'];
	$user = $args['user'];
	global $connection;
	/*@var $wapi WAPI  */
	$wapi = $world->wapi;
	$wapi->RequireToken($template);
	/*if(isset($_REQUEST['token'])) {
		
		//$connection->LoginToken($_REQUEST['token']);
		$tokenValid =  $connection->userValid;// $world->auth->ValidateAppToken( $_REQUEST['token'] , session_id() );
		//$world->auth->ValidateToken($_REQUEST['token']);
		//$world->auth->ValidateUser();
		$user= $connection->user;
		if(!$tokenValid) {
			
			$template->assign('message',"invalid token");
			$template->assign('query',"");
			$template->display("wapi/error.tpl");
			die();
		}
 		//$user = $world->auth->user;
	} else {*/
	
	$request = $_REQUEST;
	
	
	
	$projection = isset($request['projection']) ? $request['projection'] : $world->projections->defaultSRID;
	$projector = ResetProjector($world,$request);
	$projector->CenterAt((int)$request['x'], (int)$request['y'],1);
	$geom1 = $projector->GetROIExtents('polygon');
	
	$projector = ResetProjector($world,$request);
	$projector->CenterAt((int)$request['x2'],(int)$request['y2'],1);
	$geom2 = $projector->GetROIExtents('polygon');
	
	//$line = "LINESTRING(({$geom1['x']} {$geom1['y']},{$geom1['x2']} {$geom['y2']}))";
	$query = "select ST_AsText(pt1) as start, ST_AsText(pt2) as end from (select ST_Centroid(ST_GeometryFromText('$geom1',4326)) as pt1,ST_Centroid(ST_GeometryFromText('$geom2',4326)) as pt2) as q1";
	//echo($query);
	#$world->db->debug=true;
	$record = $world->db->GetRow($query);
	list($feet,$miles,$meters,$kilometers,$inches) = wkt_distance($world->db,$record['start'],$record['end']);
	
	
	if($_REQUEST['format'] == 'xml') {echo "<distance from='{$request['x']},{$request['y']}' to='{$request['x2']},{$request['y2']}' meters='$meters' miles='$miles' kilometers='$kilometers' feet='$feet' inches='$inches' />";return;}
	$res = array();
	$res['distance'] = array('from'=>"{$request['x']},{$request['y']}",'to'=>"{$request['x2']},{$request['y2']}",'meters'=>$meters,'miles'=>$miles,'kilometers'=>$kilometers,'feet'=>$feet,'inches'=>$inches);
	
	echo json_encode($res);
	
	return;
	
	
	
// go through each point and find the segment distance.
// keep track of the total for each units, and also of each segment's details for later reporting.
$points   = explode('|',$_REQUEST['points']);
$segments = array();
$total   = array('feet'=>0, 'miles'=>0, 'meters'=>0, 'kilometers'=>0);
for ($i=1; $i<sizeof($points); $i++) {
   // fetch the point-pair and split into ordinates. then fetch the distance
   $p1 = $points[$i-1];  list($p1lat,$p1lon) = explode(',',$p1);
   $p2 = $points[$i];    list($p2lat,$p2lon) = explode(',',$p2);
   list($feet,$miles,$meters,$kilometers) = distance($world->db,$p1lat,$p1lon,$p2lat,$p2lon);

   // increment the total
   $total['feet']       += $feet;
   $total['miles']      += $miles;
   $total['meters']     += $meters;
   $total['kilometers'] += $kilometers;

   // log info about this segment
   $segment = array('from'=>$p1, 'to'=>$p2, 'feet'=>$feet, 'miles'=>$miles, 'meters'=>$meters, 'kilometers'=>$kilometers );
   array_push($segments,$segment);
}


// and hand it off for rendering
$template->assign('numsegments', sizeof($segments) );
$template->assign('segments',$segments);
$template->assign('total',$total);
if($_REQUEST['format'] == 'xml') { $template->display('viewerdistance.tpl');return;}

$distance=array();
$distance['total'] = $total;
$distance['segments']=$segments;
$distance['numsegments'] = sizeof($segments);
echo json_encode($distance);
}

function ResetProjector( $world, $request ) {
	
	$defaultProj4 = $world->projections->defaultProj4;
	$projector = new Projector_MapScript();
	$projector->SetViewExtents($request['bbox']);
	$projector->SetViewSize( $request['width'],$request['height']);
	return $projector;
}

?>
