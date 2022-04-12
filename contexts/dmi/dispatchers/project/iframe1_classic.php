<?php

use utils\PageUtil;
/**
 * The HTML page for a utility to generate HTML iframe paragraphs.
 * @package Dispatchers
 */
/**
  */
function _config_iframe1_classic() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_iframe1_classic($template, $args,$org,$pageArgs) {
$user = $args['user'];
$world = $args['world'];


// are they allowed to be doing this at all?
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to embed your Map in other websites.');
   return print redirect("project.edit1&id={$_REQUEST['id']}");
}*/

// load the project and verify their access
$project = $world->getProjectById($_REQUEST['id']);
if (!$project or $project->getPermissionById($user->id) < AccessLevels::READ) {
   print javascriptalert('You do not have permission to view that Map.');
   return print redirect('project.list');
}

$pageArgs['pageSubnav'] = 'maps';
$pageArgs['mapId'] = $project->id;
PageUtil::SetPageArgs($pageArgs, $template);

$template->assign('project',$project);


// add the tool list and feature list to the template...
global $VIEWERFEATURES;
$template->assign('featurecodes',$VIEWERFEATURES);
global $VIEWERTOOLS;
$template->assign('toolcodes',$VIEWERTOOLS);

// convert the VIEWERFEATURE_DEFAULT and VIEWERTOOL_DEFAULT sums into arrays of values
$default_features = array();
foreach ($VIEWERFEATURES as $i=>$n) if (VIEWERFEATURE_DEFAULT & $i) array_push($default_features,$i);
$template->assign('featureselected',$default_features);
$default_tools = array();
foreach ($VIEWERTOOLS as $i=>$n) if (VIEWERTOOL_DEFAULT & $i) array_push($default_tools,$i);
$template->assign('toolselected',$default_tools);

// and draw it...
$template->display('project/iframe1_classic.tpl');

} ?>
