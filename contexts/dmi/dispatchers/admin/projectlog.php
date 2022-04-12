<?php

use utils\PageUtil;
/**
 * Administration: Project log.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_projectlog() {
	$config = Array ();
	// Start config
	$config ["admin"] = true;
	// Stop config
	return $config;
}
function _dispatch_projectlog($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	$pageArgs['pageTitle'] = 'Adming - Map Usage';
	PageUtil::SetPageArgs($pageArgs, $template);
	$world = $args ['world'];
	
	// fetch the specified number of entries from the logfile
	$howmany = RequestUtil::Get ( 'howmany', 100 );
	$entries = $world->fetchProjectUsage ( $howmany );
	$template->assign ( 'howmany', $howmany );
	$template->assign ( 'entries', $entries );
	
	// the list of choices for how many records to show
	$template->assign ( 'howmany_choices', array (
			50,
			100,
			200,
			300,
			500,
			1000 
	) );
	
	/*$subnav = new AdminSubnav ();
	$subnav->makeDefault ( $args ["user"], "Project Usage Log",$org,$pageArgs  );
	$template->assign ( 'subnav', $subnav->fetch () );
	*/
	// and send it off for rendering
	$template->display ( 'admin/projectlog.tpl' );
}
?>
