<?php

use utils\PageUtil;
use auth\Context;

/**
 * A page of information about accessing a Project's OGC WxS services.
 * @package Dispatchers
 */
/**
  */
function _config_ogc() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_ogc($template, $args,$org,$pageArgs) {
    $world = $args['world'];
    $user = $args['user'];
    
    $project = $world->getProjectById($_REQUEST['id']);
    if (!$project or !$project->getPermissionById($user->id)) {
       print javascriptalert('That Map was not found, or is unlisted.');
       return print redirect('project.search');
    }
    if ($user->community) {
    	print javascriptalert('You cannot share maps with a community account.');
    	return print redirect('layer.list');
    }
    
    $pageArgs['pageSubnav'] = 'maps';
    $pageArgs['pageTitle'] = 'Maps - OGC Webservices for '.$project->name;
    $pageArgs['mapId'] = $project->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    
    $template->assign('project',$project);
    
    // go for it
    $template->display('project/ogc.tpl');

}?>
