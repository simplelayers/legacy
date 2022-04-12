<?php
/**
 * Add a project to your project bookmarks list; this is called from the projectinfo page.
 * @package Dispatchers
 */
/**
  */
function _config_addbookmark() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_addbookmark($template, $args) {
$world = $args['world'];
$user = $args['user'];

// adding the bookmark is simple...
$user->addProjectBookmarkById($_REQUEST['id']);

// where to go from here? depends on their access level; maybe the edit view, maybe the info, ...
$project = $world->getProjectByid($_REQUEST['id']);
$permission = $project->getPermissionById($user->id);
if ($permission <= AccessLevels::NONE) return print redirect("project.bookmarks");
if ($permission >= AccessLevels::EDIT) return print redirect("project.edit1&id={$_REQUEST['id']}");
print redirect("project.info&id={$_REQUEST['id']}");

}?>
