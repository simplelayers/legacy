<?php

use utils\PageUtil;
use auth\Context;
use model\Permissions;

/**
 * The form for editing a projects' permissions.
 * @package Dispatchers
 */
/**
  */
function _config_permissions() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_permissions($template, $args,$org,$pageArgs) {
    $world = $args['world'];
    $user = $args['user'];
    
    // given the list of IDs, make up a list of Project objects
    $project = $world->getProjectById($_REQUEST['id']);
    if (!$project or $project->owner->id != $user->id) {
       print javascriptalert('Maps can only be shared by their owner.');
       return print redirect('project.list');
    }
    if ($user->community) {
    	print javascriptalert('You cannot share maps with a community account.');
    	return print redirect('project.list');
    }
    if(isset($_REQUEST["changes"])){
    	$changes = json_decode($_REQUEST["changes"], true);
    	foreach($changes["people"] as $id => $level) $project->setContactPermissionById($id,$level);
    	foreach($changes["groups"] as $id => $level) $project->setGroupPermissionById($id,$level);
    	if(isset($changes["public"])){
    		if($changes["public"] == 2){
    			$project->allowlpa = true;
    			$project->private = false;
    		}elseif($changes["public"] == 1){
    			$project->allowlpa = true;
    			$project->private = true;
    		}else{
    			$project->allowlpa = false;
    			$project->private = true;
    		}
    	}
    }
    $template->assign('project',$project);
    $template->assign('needRptLvl',false);
    $pageArgs['pageSubnav'] = 'maps';
    $pageArgs['pageTitle'] = 'Maps - Sharing settings for '.$project->name;
    $pageArgs['mapId'] = $project->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    $hasExternalPerm = Permissions::HasPerm($pageArgs['permissions'],':MapsSharing:External:', Permissions::VIEW);
    
    $template->assign('hasExternalPerm',$hasExternalPerm);
    
    // and hand it off for rendering
$template->display('project/permissions.tpl');
}?>
