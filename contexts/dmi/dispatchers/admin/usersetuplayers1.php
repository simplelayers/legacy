<?php

use utils\PageUtil;
/**
  * Administration: Set some default settings for new users: what data layers to copy into their account.
  * @package Dispatchers
  */
/**
  */
function _config_usersetuplayers1() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_usersetuplayers1($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs($pageArgs, $template);
$world = $args['world'];

$layers = array();
$already = explode(',',$world->config['autocopy_layers']);
foreach ($world->searchLayers() as $p) {
   if ($p->sharelevel < AccessLevels::READ) continue;
   $owner = $p->owner->username;
   $label     = sprintf("%s%s%s", $owner, str_repeat('&nbsp;',21-strlen($owner)), $p->name );
   $selected  = in_array($p->id,$already) ? 'selected="true"' : '';
   $layers[$p->id] = array('label'=>$label,'selected'=>$selected);
}
$template->assign('copylayers',$layers);

$template->assign('project_name',$world->config['defaultproject_name']);
$template->assign('project_desc',$world->config['defaultproject_desc']);

	/*$subnav = new AdminSubnav();
	$subnav->makeDefault( $args["user"] , "Default Layers",$org,$pageArgs );
	$template->assign('subnav',$subnav->fetch());
	*/
// and send them to the editor
$template->display('admin/usersetuplayers1.tpl');
}?>