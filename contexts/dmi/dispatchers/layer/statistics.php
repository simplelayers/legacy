<?php
use utils\PageUtil;
use auth\Context;

/**
 * View a Layer's statistics: who has it bookmarked, where it's used, etc.
 * @package Dispatchers
 */
/**
  */
function _config_statistics() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_statistics($template, $args,$org,$pageArgs) {
$user = $args['user'];
$world = $args['world'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
$permission = $layer->getPermissionById($user->id);
if (!$layer or $layer->owner->id != $user->id) {
    if(!Context::Get()->IsSysAdmin()) {
        print javascriptalert('Layer statistics can only be viewed by their owner.');
        return print redirect('layer.list');
    }
}
$pageArgs['pageSubnav'] = 'data';
$pageArgs['pageTitle'] = 'Data - Tracking data for layer '.$layer->name;
$pageArgs['layerId'] =$layer->id;
PageUtil::SetPageArgs($pageArgs, $template);

$template->assign('layer',$layer);

// sorting: descending or ascending?
$sortdesc = isset($_REQUEST['desc']) ? intval($_REQUEST['desc']) ? 0 : 1 : 1 ;
$template->assign('sortdesc',$sortdesc);

// people who have bookmarked this Layer
$people = $layer->usersBookmarked();
if (!isset($_REQUEST['sortpeople'])) $_REQUEST['sortpeople'] = 'username';
function people_sorter($a,$b) {
   $p = $a->$_REQUEST['sortpeople'];
   $q = $b->$_REQUEST['sortpeople'];
   $x = strcasecmp($p,$q);
   return $_REQUEST['desc'] ? -$x : $x;
}
usort($people,'people_sorter');
$template->assign('people',$people);

// projects which contain this Layer
$projects = $layer->projectsUsing();
if (!isset($_REQUEST['sortprojects'])) $_REQUEST['sortprojects'] = 'name';
function project_sorter($a,$b) {
   if ($_REQUEST['sortprojects'] == 'owner') {
      $p = $a->owner->username;
      $q = $b->owner->username;
   }
   else {
      $p = $a->$_REQUEST['sortprojects'];
      $q = $b->$_REQUEST['sortprojects'];
   }
   $x = strcasecmp($p,$q);
   return $_REQUEST['desc'] ? -$x : $x;
}
usort($projects,'project_sorter');
$template->assign('projects',$projects);

$db = $world->db;
$years = Array('*');
$months = Array('*');
$days = Array('*');
foreach($db->Execute("SELECT EXTRACT(YEAR FROM date) AS year FROM _transactions GROUP BY year ORDER BY year DESC")->getRows() as $row) $years[] = $row["year"];
for ($i = 1; $i <= 31; $i++) $days[] = $i;
for ($i = 1; $i <= 12; $i++) $months[] = date('M', mktime(0,0,0,$i,1)).'.';
$today = getdate();
$template->assign('years', $years);
$template->assign('months', $months);
$template->assign('days', $days);
$template->assign('selectYear', $today["year"]);
$template->assign('selectMonth', substr($today["month"], 0, 3).'.');
$template->assign('selectDay', $today["mday"]);

// now draw the HTML for it!
$template->display('layer/statistics.tpl');
}?>