<?php



use utils\ParamUtil;

/**
 * @arg map id of target map/project
 * @arg projection (optional)
 * @arg noscale (optional)
 * @arg formatOptions (optional);
 * @throws Exception
 */
function _exec() {
	
	$world = System::Get();
	$wapi  = System::GetWapi();
	
	$user = $wapi->RequireToken();
	
	
	$map = RequestUtil::Get('map');
	
	
	$info = $wapi->RequireProject($map);

	$project = $info['project'];
	$permission = $info['permission'];

	
	$bbox = $project->bbox;
	
	$searchLayer =  RequestUtil::Get('searchLayer',false);

	if($searchLayer) {
		
		$layer = $world->getLayerById($searchLayer);
		
		if (!$layer) throw new Exception('No such layer.');
		if (!LayerTypes::IsFeatureSource($layer->type))  throw new Exception("searchLayer must be a vector type layer.");
		
		$criteria = new SearchCriteria($layer->url);
		//function searchFeaturesByBbox($llx,$lly,$urx,$ury,$geom=false) {
		$result = $world->db->GetRow($criteria->GetAreaQuery());
		if($result) {
			$bbox = $result['x1'].','.$result['y1'].','.$result['x2'].','.$result['y2'];			
		} else {
			//die($criteria->GetAreaQuery()."\n".$world->db->ErrorMsg());
		}
						
	}
			
	// load the bounding box and the windowsize, and adjust them to best fit each other
	$viewsize = explode(",",$project->windowsize);
	$width = RequestUtil::Get('width', $viewsize[0]);
	$height = RequestUtil::Get('height',$viewsize[1]);
	
	$projection = RequestUtil::Get('projection', $project->projection);
	$noScale = RequestUtil::HasParam('noscale');
	//$formatter = new ProjectLayerFormatter($world,$user);
	$formatter = new ProjectFormatter($world,$user);

	$lAsPs = $formatter->options[ProjectFormatter::LAYERS_AS_Ps];
	
	
	$layerPreferences = $formatter->max - $lAsPs;
	$formatter->SetProjectContext( $permission, $width, $height, $projection, $noScale, $layerPreferences);
	$formatOption = ParamUtil::Get(WAPI::GetParams(),'formatOptions', $formatter->max);
	switch($wapi->format) {
		case WAPI::FORMAT_JSON:
			$formatter->WriteJSON($project,$formatter->max,$bbox);
			break;
		case WAPI::FORMAT_XML:
			$formatter->WriteXML($project,$formatter->max,$bbox);	
			break;
	}
			
}


?>
