<?php
use utils\Pixospatial;
require_once(WEBROOT."classes/SearchCriteria.php");
require_once(WEBROOT."classes/RequestUtil.class.php");
function _config_load() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config['sendWorld'] = true;
	$config['requireToken'] = true;
	$config['authUser'] = true;
	// Stop config
	return $config;
}

function _headers_load() {
	switch(RequestUtil::Get('format','xml') ) {
		case "json":	
		case "ajax":
			header('Content-Type: application/json');
			return;
		case "xml":
			header('Content-Type: text/xml');
			return;
		default:
			header('Content-Type:text/html');
			echo "<html><body><pre>";
			return;
			
	}	

}

function _dispatch_load($template, $args) {
	
	_headers_load();
	$world=System::Get();
	$wapi= $world->wapi;
	
	$user = $wapi->RequireToken($template);
	
	
	$request = $_REQUEST;
	$info = $wapi->RequireProject();
	
	$project = $info['project'];
	$permission = $info['permission'];
    
	$bbox = $project->bbox;
	$searchLayer =  RequestUtil::Get('searchLayer',false);

	
	/*if($searchLayer) {
		
		$layer = $world->getLayerById($searchLayer);
		
		if (!$layer) { $template->assign('message','No such layer.'); return $template->display('wapi/error.tpl'); }
		if (!LayerTypes::IsFeatureSource($layer->type)) { $template->assign('message','searchLayer must be a vector type layer.'); return $template->display('wapi/error.tpl'); }
		
		$criteria = new SearchCriteria($layer->url);
		//function searchFeaturesByBbox($llx,$lly,$urx,$ury,$geom=false) {
		$result = $world->db->Execute($criteria->GetAreaQuery());
		if($result) {
			$result = $result->GetRows();
			//error_log(var_export($result,true));
			if(!array_search(NULL,$result) ) {
				if( count($result)) {
					$result = $result[0];
					$bbox = $result['x1'].','.$result['y1'].','.$result['x2'].','.$result['y2'];
					//error_log("retrieved bbox: ".$bbox);
				}
			}
		} else {
			//die($criteria->GetAreaQuery()."\n".$world->db->ErrorMsg());
		}
						
	}*/
			
	// load the bounding box and the windowsize, and adjust them to best fit each other
	$viewsize = explode(",",$project->windowsize);
	$width = (isset($request['width']) )? $request['width'] : $viewsize[0];
	$height = (isset($request['height']) )? $request['height'] : $viewsize[1];
	
	
	
	$projection = (isset($request['projection']) ) ? $request['projection'] : $project->projection;
	$noScale = isset($request['noscale']);
	//$formatter = new ProjectLayerFormatter($world,$user);
	$formatter = new ProjectFormatter($args['world'],$user);
	$layerPreferences = $formatter->max;
	
	$formatter->SetProjectContext( $permission, $width, $height, $projection, $noScale, $layerPreferences);

	$pixo = Pixospatial::Get($bbox,$width,$height);
	$pixo->FitToView();
	
	
	$bbox = $pixo->ROI_to_BBOX(0,0,$width,$height);
	

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
