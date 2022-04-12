<?php
/**
 * The "Search Community" subsystem -- info about a project.
 * @package Dispatchers
 */
/**
  */
function _config_projectinfo() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_projectinfo($template, $args) {
$world = $args['world'];

$project = $world->getProjectById($_REQUEST['id']);
if (!$project or !$project->getPermissionById(null)) {
   print javascriptalert('That Map was not found, or is unlisted.');
   return print redirect('demo.search');
}

$template->assign('taglinks', activate_tags($project->tags,'.?do=demo.searchprojects&search=') );
$template->assign('project',$project);
$template->display('demo/projectinfo.tpl');

}?>
