<?php
use utils\PageUtil;
/**
 * Print the dialog to search for projects, and print a list of results from any search.
 * @package Dispatchers
 */
/**
  */
function _config_search() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_search($template, $args,$org,$pageArgs) {
    $world = $args['world'];
    $user = $args['user'];
    
    // if they specified search/sort, store it in their session
    if (isset($_REQUEST['search'])) $_SESSION['search_projects'] = $_REQUEST['search'];
    if (isset($_REQUEST['sort']))   $_SESSION['sort_projects']   = $_REQUEST['sort'];
    if (isset($_REQUEST['desc']))   $_SESSION['desc_projects']   = (bool) ($_REQUEST['desc']);
    // fetch default search/sort if none is in their session
    if (!isset($_SESSION['search_projects'])) $_SESSION['search_projects'] = '';
    if (!isset($_SESSION['sort_projects']))   $_SESSION['sort_projects']   = 'name';
    if (!isset($_SESSION['desc_projects']))   $_SESSION['desc_projects']   = false;
    // excellent -- they're now guaranteed safe for use, so pull them back out again
    // these do have to be in $_REQUEST to be visible to the sorter() function's internals.
    $_REQUEST['search'] = $_SESSION['search_projects'] == 'all available' ? false : $_SESSION['search_projects'];
    $_REQUEST['sort']   = $_SESSION['sort_projects'];
    $_REQUEST['desc']   = $_SESSION['desc_projects'];
    
    
    
    // fetch the lisst of matches
    $matches = array();
    $searchstring = $_REQUEST['search'];
    foreach ($world->searchProjects($searchstring) as $project) {
       if (!$project->getPermissionById($user->id)) continue; // no permission
       if ($project->owner->id == $user->id) continue; // we own it, so don't show it in the search
       array_push($matches,$project);
    }
    
    
    
    // sort them. can't use a simple database sort, sadly
    function sorter($a,$b) {
       if ($_REQUEST['sort']=='owner') {
          $p = $a->owner->username; $q = $b->owner->username; }
       else {
          $p = $a->$_REQUEST['sort']; $q = $b->$_REQUEST['sort']; }
       $x = strcasecmp($p,$q);
       return $_REQUEST['desc'] ? -$x : $x;
    }
    usort($matches,'sorter');
    
    $pageArgs['subnav'] = 'maps';
    $pageArgs['mapId'] = $project->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    
    // hand it off to the template for rendering
    $template->assign('sortdesc',!$_REQUEST['desc']);
    $template->assign('searchterm',$_REQUEST['search']);
    $template->assign('matches',$matches);
    $template->display('project/search.tpl');

}?>
