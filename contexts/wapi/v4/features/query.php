<?php

function _exec($template, $args) {
	
	$world = System::Get();
	$wapi = System::GetWapi();
	
	$projectInfo = $wapi->RequireProject ();
	$project = $projectInfo ['project'];
	$permission = $projectInfo ['permission'];
	unset ( $projectInfo );
	
	//$_REQUEST,$world,$user,$template,$project,$embedded,$permission) {
	

	if ($permission < AccessLevels::READ)
		denied ( 'You do not have permission to view this project.' );
	
		//error_log(var_export($_REQUEST,true));
	$bbox = RequestUtil::Get ( 'bbox', null );
	$template->assign ( 'bbox', $bbox );
	$pbox = RequestUtil::Get ( 'pxrect', null );
	$template->assign ( 'pbox', $pbox );
	list ( $x1, $y1, $x2, $y2 ) = explode ( ",", $pbox );
	$x1 = (int)$x1; $x2 = (int)$x2; $y1=(int)$y1;$y2=(int)$y2;
	
	$pwidth = max($x1,$x2) - min($x1,$x2);
	$pheight = max($y1,$y2) - min($y1,$y2);
	
	
	$projection = RequestUtil::Get ( 'projection', $project->projectionSRID );
	
	//$projection = $world->projecitons->defaultSRID;
	$targetProj4 = $world->projections->getProj4BySRID ( $projection );
	//$mapper->projection = $project->projection;
	////error_log("projection:".$mapper->projection);
	$projector = new Projector_MapScript();
	
	$extents = $projector->ProjectExtents ( $world->projections->defaultProj4, $targetProj4, RequestUtil::Get ( 'width' ), RequestUtil::Get ( 'height' ), $bbox );
	$projector->ZoomTo ( $x1, $y1, $x2, $y2 );
	$projector->SetViewSize ( $pwidth, $pheight );
	
	$ROI = $projector->GetROIExtents ( "array" );
	
	// figure up the bbox and whether to add geometries into the output
	$geom = ( bool ) ( int ) RequestUtil::Get ( 'geom' ,false );
	$template->assign ( 'geom', $geom );
	$paging = new Paging ( "start", "limit" );
	
	// if a list of GIDs was supplied, split it
	$_REQUEST ['gids'] = RequestUtil::GetList ( 'gids', ',', null );
	
	// the $results for the template will be a series of 3-tuples:
	// (Layer object, array of fieldnames, array of featurehashes)
	// That is: the layer object, an array of the fieldnames to present for each feature in the output, and an array of features
	$results = array();
	

	$limit = ( int ) RequestUtil::Get ( 'limit' );
	
	$layerSource = RequestUtil::Get ( 'layers', RequestUtil::Get ( 'players' ) );
	
	
	$isProjLayers = RequestUtil::HasParam ( 'players' );
	
	$uniqueLayers = array ();
	$distance = RequestUtil::Get('distance');
	$delim = (strpos($layerSource,'|') !==false) ? '|' : ',';
	$featureCount = 0;
	
	// and go for it. keep a count of how many results we have so far, so we can bail when we reach that limit
	foreach ( explode ( $delim, $layerSource ) as $layerid ) {
		// if we've already hit/passed our result limit, then skip out now
		//if ($limit <= 0) break;
		// fetch the ProjectLayer and the underlying Layer item, make sure it's a vector layer
		$fields = "";
		$lastField = "";
		
		//error_log($layerid);
		if (stripos ( $layerid, ":" ))
			list ( $layerid, $fields ) = explode ( ':', $layerid );
		if ($fields !== "") {
			$fields = explode ( ',', $fields );
			if (sizeof ( $fields > 0 )) {
				$lastField = array_pop ( $fields );
			}
		} 
		if ($lastField !== "") {
			array_push ( $fields, $lastField );
			$fields = array_unique ( $fields );
		}
		$projectlayer = null;
		if ($isProjLayers) {
			$projectLayer = new ProjectLayer ( $world, $project, $layerid );
		} else {
			$projectLayer = null; #$project->getLayerById ( $layerid );
		}
		
		if(!is_null($projectLayer)) $plid =$projectLayer->id;
		
		//error_log('projectLayer:'.$projectlayer->layer->id);
		if ($isProjLayers && !$projectLayer)
			continue;
		/* @var $layer \Layer */
		$layer = $projectLayer->layer;
		
		if($fields=="") {
			$fields = array_keys($layer->getAttributes());
			
		}
		if(!in_array('gid',$fields)) array_unshift( $fields,'gid');
		
		if (! in_array ( $layer->id, $uniqueLayers )) {
			$uniqueLayers [] = $layer->id;
		} else {
			continue;
		}
		
		
		
		if (! $layer)
			continue; // not even a layer, WTF?
		

		

		if ($layer->type != LayerTypes::VECTOR and $layer->type != LayerTypes::RELATIONAL and $layer->type != LayerTypes::ODBC)
			continue; // a layer, but not a candidate for searching for features
		// search the layer by bbox
		
		$these = array ();
		$gids = RequestUtil::Get('gids');
		$filterLayer = RequestUtil::Get('filterLayer');

		if ($distance ) {
			$lon = $projector->centerLon;
			$lat = $projector->centerLat;
			$these = $layer->searchFeaturesByDistance( $lon, $lat, $distance, implode( ",", $ROI ), $geom );
		} else {
			$these = $layer->searchFeaturesByBbox ( $ROI [0], $ROI [1], $ROI [2], $ROI [3], $geom, $projection );
			
			
		}
		
		if ($gids && $filterLayer ) {
			if ($layerid == $filterLayer) {
				//error_log('layer is filterLayer');
				$these = array_filter ( $these, create_function ( '$x', 'return in_array($x["gid"],$_REQUEST["gids"]);' ) );
			}
		}
		
		#$these = array_reverse ( $these );
		//$these = array_slice($these,0,$limit);
		
		
		
		// $limit -= sizeof($these);
		// and stick the layer and the results onto the output
		
		if($these->RecordCount()>0) {
			/*@var $these ADORecordSet */
			$features = array();
			foreach($these as $result); {
				if(!geom) if(isset($result['the_geom'])) unset($result['the_geom']);
				$features[] = $result;
			}
			$featureCount+= count($features);
			array_push ( $results, array ('layer'=>$layer->id, 'fields'=>$fields, 'features'=>$features, 'plid'=>$plid ) );
		}
	}
	

	return WAPI::SendSimpleResponse( array('featureCount'=>$featureCount,'results'=>$results));
	
}
?>