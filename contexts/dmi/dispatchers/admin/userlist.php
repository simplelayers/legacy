<?php
use utils\PageUtil;
/**
  * Administration: A list of all users, with links to edit them.
  * @package Dispatchers
  */
/**
  */
function _config_userlist() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_userlist($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs($pageArgs, $template);
$world = $args['world'];

// if they specified search/sort, store it in their session
if (isset($_REQUEST['sort']))   $_SESSION['sort_useradmin']   = $_REQUEST['sort'];
if (isset($_REQUEST['desc']))   $_SESSION['desc_useradmin']   = (bool) ($_REQUEST['desc']);
// fetch default search/sort if none is in their session
if (!isset($_SESSION['sort_useradmin']))   $_SESSION['sort_useradmin']   = 'name';
if (!isset($_SESSION['desc_useradmin']))   $_SESSION['desc_useradmin']   = false;
// excellent -- they're now guaranteed safe for use, so pull them back out again
// these do have to be in $_REQUEST to be visible to the sorter() function's internals.
$_REQUEST['sort']   = $_SESSION['sort_useradmin'];
$_REQUEST['desc']   = $_SESSION['desc_useradmin'];

// the last user login
$lastlogin = $world->fetchRecentLogins(1);
$lastlogin = $lastlogin[0];
$template->assign('lastlogin',$lastlogin);

// fetch the list of all users on the system, except the admin account
$people = $world->getAllPeople();
$people = array_values(array_filter($people , create_function('$a','return !$a->admin;') ));
// now sort the list
function sorter($a,$b) {
   $p = $a->$_REQUEST['sort']; $q = $b->$_REQUEST['sort'];
   if ($_REQUEST['sort'] == 'id') { $p = (int) $p; $q = (int) $q; $x = ($p >= $q) ? 1 : -1; }
   else { $x = strcasecmp($p,$q); }
   return $_REQUEST['desc'] ? -$x : $x;
}
usort($people,'sorter');
// done, go ahead and hand it to the template
$template->assign('people',$people);
$template->assign('sortdesc',!(bool)$_REQUEST['desc']);

// iterate through the people and make up an array of folks who are past expiration
// the template will check against this list to generate an alternate style for those people
// this will be a simple associative array of Person ID#s
$expiredaccounts = array();
foreach ($people as $p) if ($p->daysUntilExpiration() < 0) $expiredaccounts[$p->id] = true;
$template->assign('expiredaccounts',$expiredaccounts);


// the array for mapping accounttype numbers onto human-friendly names
global $ACCOUNTTYPES;
$template->assign('accounttypes',$ACCOUNTTYPES);

/*$subnav = new AdminSubnav();
$subnav->makeDefault($args["user"], "User Accounts Overview",$org,$pageArgs );
$template->assign('subnav',$subnav->fetch());
*/
// and draw it
$template->display('admin/userlist.tpl');

}?>
