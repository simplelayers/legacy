<?php

/**
 * Process the projectedit1 form, saving the project information.
 * @package Dispatchers
 */
/**
 */
function _config_edit2() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_edit2($template, $args) {
    $layerList = urldecode($_REQUEST['layerlist']);
  
	$user = SimpleSession::Get ()->GetUser ();
	$world = System::Get();
	$ini = System::GetIni();
	
	// load the project and verify their access
	
	$project = $world->getProjectById ( $_REQUEST ['id'] );
	
	if (! $project or $project->getPermissionById ( $user->id ) < AccessLevels::EDIT) {
		print javascriptalert ( 'You do not have permission to edit this Map.' );
		return print redirect ( 'project.list' );
	}
	
	// are they allowed to be doing this at all?
	/*if ($project->owner->id != $user->id and $user->accounttype < AccountTypes::GOLD) {
		print javascriptalert ( 'You must upgrade your account to edit others\' Maps.' );
		return print redirect ( "layer.list" );
	}*/
	
	// handle the simple attributes
	$project->name = $name = $_REQUEST['name'];
	$project->description = $_REQUEST ['description'];
	$project->tags = $_REQUEST['tags'];
	$project->bbox = sprintf( '%f,%f,%f,%f', $_REQUEST['bbox0'], $_REQUEST['bbox1'], $_REQUEST['bbox2'], $_REQUEST['bbox3'] );
	$layerList = json_decode( $layerList);

	
	if(!$layerList) throw new Exception('could not decode layer list:');

	// now for the layer list, which is handled in 2 parts: adding layers, and removing layers
	//$layerList = array_slice ( $layerList, 0, $ini->max_project_layers );
    
	$z = 0;
	$uid = $user->id;
	
	// now go through the layers in the project, and delete any that are NOT on the requested list
	foreach ( $project->getLayers (true ) as $projectlayer ) {
		$inProject = false;
		
		foreach ( $layerList as $layer ) {
		 
			if(isset($layer->data)) {
				if($projectlayer->id == $layer->data->plid) {
					$inProject = true;
					break;
				}
			}
		}
		if(!$inProject) $projectlayer->delete();
	}
	
	foreach ( $layerList as $topLayer ) {
		
		
		if ($topLayer === null || $topLayer === "")
		{
			continue;	
		}
		if($z > $ini->max_project_layers) break;
		if (isset ( $topLayer->data->plid )) {
			// already a project layer.
			$player = $project->getLayerById( $topLayer->data->plid );
			$z = $player->SaveZ( $z );
							
		} else {
			addLayerToProject( $world, $uid, $topLayer, $project, $z );
			$z--;
		}
		
		#$z--;		
	}
	
	// done -- send them to either their project list or their layerbookmark list, depending
	// on whether they own the project they just edited
	$project->owner->notify($user->id, "edited project:", $project->name, $project->id, "./?do=project.info&id=".$project->id, 4);
	print redirect('project.edit1&id='.$_REQUEST['id']);
	
	
}

// go through the submitted list and add any layers that aren't already there
// if it's indeed being added, check that the $user has at least AccessLevels::READ on the layer
// so we know that they even had permission to share it
function copyColorScheme($layer, $projectlayer) {
	if ($layer->type != LayerTypes::VECTOR and $layer->type != LayerTypes::ODBC and $layer->type != LayerTypes::RELATIONAL)
		return;
	foreach ( $layer->colorscheme->getAllEntries () as $oldcolorschemeentry ) {
		$newcolorschemeentry = $projectlayer->colorscheme->addEntry ();
		// $newcolorschemeentry->priority = $oldcolorschemeentry->priority;
		$newcolorschemeentry->criteria1 = $oldcolorschemeentry->criteria1;
		$newcolorschemeentry->criteria2 = $oldcolorschemeentry->criteria2;
		$newcolorschemeentry->criteria3 = $oldcolorschemeentry->criteria3;
		$newcolorschemeentry->fill_color = $oldcolorschemeentry->fill_color;
		$newcolorschemeentry->stroke_color = $oldcolorschemeentry->stroke_color;
		$newcolorschemeentry->description = $oldcolorschemeentry->description;
		$newcolorschemeentry->symbol = $oldcolorschemeentry->symbol;
		$newcolorschemeentry->symbol_size = $oldcolorschemeentry->symbol_size;
	}
}

function addLayerToProject($world, $userid, $layerdata, $project, &$z, $parent = null) {
	$layerid = $layerdata->id;
	
	$layer = $world->getLayerById ( $layerid );
	
	if(!$layer || ($layer->getPermissionById( $userid ) < AccessLevels::READ) ) {
		return $z;
	}
	
	$projectlayer = $project->addLayerById( $layerid, $parent,$userid ,$z);

	$projectlayer->whoadded = $userid;
	$projectlayer->opacity = $layer->type == LayerTypes::VECTOR ? 1.0 : 0.5;

	$projectlayer->colorschemetype = "custom";
	$projectlayer->labelitem = $layer->labelitem;

	copyColorScheme( $layer, $projectlayer );
	
	return $z;
}

?>
