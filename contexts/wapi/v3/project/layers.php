<?php

function _config_layers() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = !isset($_REQUEST['token']);
	$config['sendWorld'] = true;
	$config['authUser'] = 0;
	// Stop config
	return $config;
}

function _headers_layers() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'json';
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

function _dispatch_layers($template, $args) {
	$world=System::Get();
	$user = SimpleSession::Get()->GetUser();

	$wapi = System::GetWapi();
	$project = $wapi->RequireProject();
	$project = $args['world']->getProjectById((int)$_REQUEST['project']);
	$array = Array();
	foreach ($project->getLayers(true) as $projectlayer) $array[] = addLayerToArray($projectlayer, $project);
	print(json_encode($array));
	
}

function addLayerToArray($pLayer, $project){
	$layer = $pLayer->layer;
	$layerData = Array();
	$layerData["id"] = $layer->id;
	$layerData['plid'] = $pLayer->id;
	$layerData["owner_name"] = $layer->owner->realname;
	$layerData["geom"] = $layer->geomtypestring;
	$layerData["name"] = $layer->name;
	$layerData["description"] = $layer->description;
	$layerData["children"] = Array();
	if($layer->type == LayerTypes::COLLECTION){
		$subs = $project->getSubLayers($pLayer->id);
		foreach($subs as $sub) $layerData["children"][] = addLayerToArray($sub, $project);
	}
	return $layerData;
}

?>