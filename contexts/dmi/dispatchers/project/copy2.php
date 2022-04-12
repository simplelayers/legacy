<?php
/**
 * Print a list of your projects, with links to edit them.
 * @package Dispatchers
 */
/**
  */
function _config_copy2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_copy2($template, $args) {
	$user = $args['user'];
	$world = $args['world'];
	
	$project = $world->getProjectById($_REQUEST['id']);
	if (!$project or $project->getPermissionById($user->id) < AccessLevels::COPY) {
		print javascriptalert('You do not have permission to copy that Map.');
		return print redirect('project.list');
	}
	
	$project = $world->getProjectById($_REQUEST['id']);
	$newProject = $project->copy($_REQUEST['name'], $user);
	
	print redirect('project.edit1&id='.$newProject->id);
}?>