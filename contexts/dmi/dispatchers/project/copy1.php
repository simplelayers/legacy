<?php
use utils\PageUtil;
/**
 * Print a list of your projects, with links to edit them.
 * @package Dispatchers
 */
/**
  */
function _config_copy1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_copy1($template, $args,$org,$pageArgs) {
	$user = $args['user'];
	$world = $args['world'];
	
	$project = $world->getProjectById($_REQUEST['id']);
	if (!$project or $project->owner->id != $user->id) {
	   print javascriptalert('Maps can only be copied by their owner.');
	   return print redirect('project.list');
	}

	$template->assign('project',$project);
	$pageArgs['pageSubnav'] = 'maps';
	$pageArgs['pageTitle'] = 'Maps - Copy '.$project->name.' to new map';
	$pageArgs['mapId'] = $project->id;
	PageUtil::SetPageArgs($pageArgs, $template);
	PageUtil::MixinMapArgs($template);
	
	$template->assign('isowner', ($project->owner->id  == $user->id));
	$template->display('project/copy1.tpl');
}?>