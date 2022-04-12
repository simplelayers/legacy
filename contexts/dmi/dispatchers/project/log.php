<?php

use utils\PageUtil;
use auth\Context;

/**
  * Administration: Project log.
  * @package Dispatchers
  */
/**
  */
function _config_log() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_log($template, $args,$org,$pageArgs) {
    $world = $args['world'];
    $user = $args['user'];
    
    $howmany = 100;
    if(isset($_REQUEST['howmany'])) $howmany = $_REQUEST['howmany'];
    
    if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
    	$project = $world->getProjectById($_REQUEST['id']);
    	$pageArgs['pageTitle'] = 'Maps - Tracking log for <span="object_title">'.$project->name.'</span>';
    	
    	if (!$project or !$project->getPermissionById($user->id)) {
    		print javascriptalert('That Map was not found, or is unlisted.');
    		return print redirect('project.list');
    	}
    	$entries = $world->fetchProjectUsage($howmany, $user->id, $project->id);
    }else{
    	$pageArgs['pageTitle'] = 'Maps - Tracking log for all maps';
    	
    	$entries = $world->fetchProjectUsage($howmany, $user->id);
    	$_REQUEST['id'] = 0;
    }
    // fetch the specified number of entries from the logfile
    
    $template->assign('howmany',$howmany);
    $template->assign('entries',$entries);
    
    $template->assign('selectedMap',$_REQUEST['id']);
    $options = Array("names"=>Array("All Maps"),"keys"=>Array("0"));
    foreach ($user->listProjects() as $proj){
    	$options["names"][] = $proj->name;
    	$options["keys"][] = $proj->id;
    }
    
    $pageArgs['pageSubnav'] = 'maps';
    $pageArgs['mapId'] = $project->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    
    $template->assign('mapOptions',$options);
    // the list of choices for how many records to show
    $template->assign('howmany_choices', array(50,100,200,300,500,1000) );
    // and send it off for rendering
    $template->display('project/log.tpl');

}?>
