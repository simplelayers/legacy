<?php
/**
 * Remove the specified project-bookmark; this is called from the projectinfo page.
 * @package Dispatchers
 */
/**
  */
function _config_removebookmark() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_removebookmark($template, $args) {
    $world = $args['world'];
    $user = $args['user'];
    
    // if we're doing a single one, we may have come from projectedit or projectinfo
    // use our permission level to decide which one it probably was
    if (isset($_REQUEST['id'])) {
       $user->removeProjectBookmarkById($_REQUEST['id']);
       $project = $world->getProjectByid($_REQUEST['id']);
       $permission = $project->getPermissionById($user->id);
    
       if ($permission <= AccessLevels::NONE) return print redirect("project.bookmarks");
       if ($permission >= AccessLevels::EDIT) return print redirect("project.edit1&id={$_REQUEST['id']}");
       return print redirect("project.info&id={$_REQUEST['id']}");
    }
    
    // otherwise, we're doing a list of bookmarks, and that's only from our bookmarks page
    elseif (isset($_REQUEST['ids'])) {
       foreach ($_REQUEST['ids'] as $id) {
          $user->removeProjectBookmarkById($id);
       }
       print redirect('project.bookmarks');
    }

}?>
