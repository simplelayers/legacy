<?php

use utils\PageUtil;
/**
 * Administration: Set some default settings for new users: Bookmarked layers and projects.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_usersetupbookmarks1() {
	$config = Array ();
	// Start config
	$config ["admin"] = true;
	// Stop config
	return $config;
}
function _dispatch_usersetupbookmarks1($template, $args, $org, $pageArgs) {
	$pageArgs ['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs ( $pageArgs, $template );
	$world = $args ['world'];
	
	$projects = array ();
	$already = explode ( ',', $world->config ['autobookmark_projects'] );
	foreach ( $world->searchProjects () as $p ) {
		if ($p->private)
			continue;
		$owner = $p->owner->username;
		$label = sprintf ( "%s%s%s", $owner, str_repeat ( '&nbsp;', 21 - strlen ( $owner ) ), $p->name );
		$selected = in_array ( $p->id, $already ) ? 'selected="true"' : '';
		$projects [$p->id] = array (
				'label' => $label,
				'selected' => $selected 
		);
	}
	$template->assign ( 'bookmarkprojects', $projects );
	
	$layers = array ();
	$already = explode ( ',', $world->config ['autobookmark_layers'] );
	foreach ( $world->searchLayers () as $p ) {
		if ($p->sharelevel < AccessLevels::READ)
			continue;
		$owner = $p->owner->username;
		$label = sprintf ( "%s%s%s", $owner, str_repeat ( '&nbsp;', 21 - strlen ( $owner ) ), $p->name );
		$selected = in_array ( $p->id, $already ) ? 'selected="true"' : '';
		$layers [$p->id] = array (
				'label' => $label,
				'selected' => $selected 
		);
	}
	$template->assign ( 'bookmarklayers', $layers );
	
	/*
	 * $subnav = new AdminSubnav(); $subnav->makeDefault($args["user"], "Default Bookmarked Layers",$org,$pageArgs ); $template->assign('subnav',$subnav->fetch());
	 */
	
	// and send them to the editor
	$template->display ( 'admin/usersetupbookmarks1.tpl' );
}
?>
