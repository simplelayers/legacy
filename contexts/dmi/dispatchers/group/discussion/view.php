<?php
use utils\PageUtil;
/**
 * Print info about a person.
 * @package Dispatchers
 */
/**
  */
function _config_view() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_view($template, $args, $org, $pageArgs) {
	
$user = $args['user'];
$world = System::Get();
$group = $world->getGroupById($_REQUEST['groupId']);
$pageArgs['pageSubnav'] = 'community';

$pageArgs['groupId'] = $group->id;
$pageArgs['groupName'] = $group->title;
$pageArgs['groupOrg'] = $group->org_id;

$groupActor = 'group_visitor';
if( $group->owner->id == $user->id) $groupActor = 'group_owner';
if(!is_null($group->org_id) && !$groupActor == 'group_owner') {
	if(Organization::GetOrg($group->org_id)->owner == $user->id);
	$groupActor = 'group_owner';
	
}
if($pageArgs['pageActor']=='admin') {
	$groupActor = 'group_owner';
} else {
	if($group->isMember($user->id)) {
		$groupActor = 'group_member';
	}
}
$pageArgs['groupActor'] = $groupActor;

$dis = $group->getDiscussion($user, $_REQUEST['view']);

$pageArgs['pageTitle'] = 'Community - Group '.$group->title."'s discussion RE: ".$dis[0]['name'];
if(isset($_REQUEST["post"])){
	$group->newReply($user, $_REQUEST['view'],$_REQUEST["parent"],$_REQUEST["post"]);
	print redirect("group.discussion.view&id=".$group->id."&view=".$_REQUEST['view']."#last");
}elseif(isset($_REQUEST["del"])){
	$reply = $group->getReply($_REQUEST['view'], $_REQUEST['del']);
	
	
	if(($reply["owner"] == $user->id) || ($groupActor == 'group_owner')){
		if($reply['parent'] == '0'){
			$group->deleteDiscussion($_REQUEST['view']);
			print redirect("group.discussion.list&groupId=".$group->id);
		}else{
			$group->deleteReply($_REQUEST['view'], $_REQUEST['del']);
			print redirect("group.discussion.view&groupId=".$group->id."&view=".$_REQUEST['view']);
		}
	}
}
PageUtil::SetPageArgs($pageArgs, $template);
$template->assign('group',$group);
$template->assign('world',$world);
$template->assign('dis',$dis[0]);
$template->assign('replies',$group->getNestedReplies($dis[0]['id']));

/*
$subnav = new GroupSubnav();
$subnav->makeDefault($group, $user);
$template->assign('subnav',$subnav->fetch());
*/
$template->display('group/discussion/view.tpl');
}?>