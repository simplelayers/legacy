<?php
use utils\PageUtil;
/**
 * Print the dialog to search for layers, and print a list of results from any search.
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
if (isset($_REQUEST['search'])) $_SESSION['search_layers'] = $_REQUEST['search'];
if (isset($_REQUEST['sort']))   $_SESSION['sort_layers']  = $_REQUEST['sort'];
if (isset($_REQUEST['desc']))   $_SESSION['desc_layers']   = (bool) ($_REQUEST['desc']);
// fetch default search/sort if none is in their session
if (!isset($_SESSION['search_layers'])) $_SESSION['search_layers'] = '';
if (!isset($_SESSION['sort_layers']))   $_SESSION['sort_layers']   = 'name';
if (!isset($_SESSION['desc_layers']))   $_SESSION['desc_layers']   = false;
// excellent -- they're now guaranteed safe for use, so pull them back out again
// these do have to be in $_REQUEST to be visible to the sorter() function's internals.
$_REQUEST['search'] = $_SESSION['search_layers'] == 'all available' ? false : $_SESSION['search_layers'];
$_REQUEST['sort']   = $_SESSION['sort_layers'];
$_REQUEST['desc']   = $_SESSION['desc_layers'];

// fetch the matches, or lack thereof...
$filtered = array();
foreach ($world->searchLayers($_REQUEST['search']) as $m) {
   if (! $m->getPermissionById($user->id) ) continue; // no permission to see it
   if ( $m->owner->id == $user->id ) continue;  // it's ourself
   array_push($filtered,$m);
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
usort($filtered,'sorter');

// hand it off to the template for rendering
$template->assign('sortdesc',!$_REQUEST['desc']);
$template->assign('searchterm',$_REQUEST['search']);
$template->assign('matches',$filtered);
$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] =$layer->id;
PageUtil::SetPageArgs($pageArgs, $template);
$template->display('layer/search.tpl');

}?>
