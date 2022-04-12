<?php
use utils\PageUtil;
/**
 * Print info about a person.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_list() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_list($template, $args, $org, $pageArgs) {
	$user = $args ['user'];
	$world = System::Get();
	$group = $world->getGroupById ( $_REQUEST ['groupId'] );
	$pageArgs ['pageSubnav'] = 'community';
	$pageArgs ['pageTitle'] = 'Community - '.$group->title."'s Discussion Forum";
	$pageArgs ['groupId'] = $group->id;
	$pageArgs ['groupName'] = $group->title;
	$pageargs ['groupOrg'] = $group->org_id;
	PageUtil::SetPageArgs ( $pageArgs, $template );
	
	if (isset ( $_REQUEST ["name"] )) {
		$new = $group->newDiscussion ( $user, $_REQUEST ["name"], $_REQUEST ["post"] );
		print redirect ( "group.discussion.view&groupId=" . $group->id . "&view=" . $new );
		
	} elseif (isset ( $_REQUEST ["markall"] )) {
		foreach ( $group->getDiscussion ( $user ) as $dis ) {
			$world->db->Execute ( "SELECT upsert_discussions_views(?, ?)", Array (
					$user->id,
					$dis ["id"] 
			) );
		}
		print redirect ( "group.discussion.list&id=" . $group->id );
	}
	$template->assign ( 'group', $group );
	/*
	 * $subnav = new GroupSubnav(); $subnav->makeDefault($group, $user); $template->assign('subnav',$subnav->fetch());
	 */
	$template->display ( 'group/discussion/list.tpl' );
}
?>