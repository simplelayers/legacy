<?php
use reporting\Transaction;
System::RequireReporting();
function _config_render() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config['sendWorld'] = true;
	
	// Stop config
	return $config;
}

function _headers_render() {
}
/**
  */
/**
  * @ignore
  */
function _dispatch_render($template, $args) {
//function _dispatch_renderlayer($request,$world,$user,$template,$project,$embedded,$permission) {
	$request = $_REQUEST;
	
	/* @var $world World */
	$world = System::Get();
	/* @var $wapi WAPI */
	$wapi= $world->wapi;
	$info = $wapi->RequireProject();
	$project = $info['project'];
	$permission = $info['permission'];
	
	$user = $wapi->RequireToken($template);
	$layer = null;
	
	if(isset($_REQUEST['plid'])) {
		
		$layer = new ProjectLayer($world,$project,$_REQUEST['plid']);
		$layer = $layer->layer;
		$baseLayer = new ProjectLayer($world,$project,$_REQUEST['plid']);
		$resultSetLayer = new ProjectLayer($world,$project,$_REQUEST['plid']);
		$unResultSetLayer = new ProjectLayer($world,$project,$_REQUEST['plid']);
	} else {
		$baseLayer = $world->getLayerById($request['layer']);   
		if (!$baseLayer) return denied(DENIED_NOLAYER);
		$layer = $baseLayer;
		$baseLayer = $world->getLayerById($request['layer']);   
		$resultSet = $baseLayer;
		$baseLayer = $world->getLayerById($request['layer']);
		$unResultSetLayer = $baseLayer;
	}
	
	if(empty($_SESSION[$project->id."lastNav"]) || $_SESSION[$project->id."lastNavTime"] <= time()-30) $_SESSION[$project->id."lastNav"] = 0;
	$defaultProj4 = $world->projections->defaultProj4;
	
	
	/* @var $mapper Mapper */
	$mapper = $world->getMapper();
	
	$mapper->interlace = isset($request['interlace'] );
	$mapper->quantize = false;//isset($request['quantize'] );

	$projector = new Projector_MapScript();
	// set some defaults, to get rid of spurious warnings in the error_log
	if (!isset($request['opacity'])) $request['opacity'] = 1.00;
	if (!isset($request['labels']))  $request['labels']  = 0;
	
	
	
	
	$mapper->init(true, $projector->mapObj);
	
	if(@$request['gids']=='') unset($request['gids']);
	if( (!isset($request['uncolor'] ) && !isset($request['gids'])) || isset($request['unnormal']) ) {
		$labelField =  $request['labels']=='1' ? $request['labelfield'] : null;
		$mapper->addLayer($baseLayer, $request['opacity'], $request['labels'],$request['labelfield']);
		Transaction::add($world, $layer, $project, $user, $_SESSION[$project->id."lastNav"]);
	}	
	
	if(isset($request['gids'])) {
	    ;
	  /* if($layer->geomType == GeomTypes::LINE) {
	        $uncolor = isset($request['uncolor'])? $request['uncolor'] : 'FF0000';
    		if(isset($request['uncolor'])) {
    			$unColorInfo = explode(';',$request['uncolor']);
    			if(count($unColorInfo) > 0) {
    			    list($unColor,$unOpacity) = $unColorInfo;
    			} else {
    			    $unColor = "trans";//$unColorInfo[0];
    			     $unOpacity = (isset($request['opacity']))? $request['opacity'] : 0;
    			}
    			$unResultSetLayer->filter_color =  $unColor;
    			$unResultSetLayer->filter_gids = $request['gids'];
    			$mapper->addLayer($unResultSetLayer, $unOpacity, 0);
    			
    		}
	    }*/
	    $colorInfo = explode(';',$request['color']);
	    
		if(count($colorInfo) > 0) {
		    list($color,$opacity) = $colorInfo;
		} else {
		    $color = $colorInfo[0];
		    $opacity = (isset($request['opacity']))? $request['opacity'] : 1;
		}
		
		if($request['uncolor']=='trans') {
		  $opacity = ($layer->geomtype==GeomTypes::LINE) ? 0.1 : 1;
		} else {
		    $opacity=1;
		}
		
		$resultSetLayer->filter_gids = $request['gids'];
		$resultSetLayer->filter_color = $color;
		
		$mapper->addLayer($resultSetLayer,$opacity, $request['labels']);
			
		Transaction::add($world, $resultSetLayer, $project, $user, $_SESSION[$project->id."lastNav"]);
	}
	
	// add the filter
	if(isset($request['field'])) {
		
		$mapper->filter_field = $request['field'];
	
		if (@$request['field']) $mapper->filter_field = $request['field'];
		if (@$request['color']) $mapper->filter_color = $request['color'];
		if (@$request['gids'])  $mapper->filter_gids  = $request['gids'];
	}

	// draw it!
	if(!isset($request['suppress_header']) ) { 
	    #header('Content-type: image/png',true);
	}else {
		#header('Content-Type:text/plain');
	}
	
	print $mapper->renderStream(true);

}	