<?php

use utils\PageUtil;
/**
 * Process the form from projectiframe1, printing a block of HTML that a person can use to embed a map.
 * @package Dispatchers
 */
/**
  */
function _config_iframe2_classic() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_iframe2_classic($template, $args,$org,$pageArgs) {
$user = $args['user'];
$world = $args['world'];

// are they allowed to be doing this at all?
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to embed your Map in other websites.');
   return print redirect("project.edit1&id={$_REQUEST['id']}");
}*/

// load the project and verify their access
$project = $world->getProjectById($_REQUEST['id']);
if (!$project or $project->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Map.');
   return print redirect('project.list');
}
$template->assign('project',$project);

// the base URL of the Cartograph installation
$template->assign('worldurl',$world->config['url']);

// generate the bit vector for the selected tools and features; this is a simple sum of their options
$toolcode    = 0; foreach ($_REQUEST['toolcode']    as $i) $toolcode    += $i; $template->assign('toolcode',$toolcode);
$featurecode = 0; foreach ($_REQUEST['featurecode'] as $i) $featurecode += $i; $template->assign('featurecode',$featurecode);

// some simple assignments from their submission into the template
$template->assign('width',$_REQUEST['width']);
$template->assign('height',$_REQUEST['height']);

// the 'noresize' flag is used in the URL, if they asked for it explicitly OR if the width!=height
if (isset($_REQUEST['noresize']) or $_REQUEST['width']!=$_REQUEST['height']) {
   $template->assign('noresize','&noresize');
}

// if the RELATADATA tool is being used, add some properties for the other iframe
if ($toolcode & VIEWERTOOL_RELATADATA) {
   true; // get info from Art as to whether any special handling needs to be done
}
$pageArgs['pageSubnav'] = 'maps';
$pageArgs['mapId'] = $project->id;
PageUtil::SetPageArgs($pageArgs, $template);
// all done; show them their HTML
$template->display('project/iframe2_classic.tpl');


} ?>
