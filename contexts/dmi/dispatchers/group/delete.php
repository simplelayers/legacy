<?php
/**
 * Delete the specified SocialGroup
 * @package Dispatchers
 */
/**
  */
function _config_delete() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_delete($template, $args,$org,$pageArgs) {
$world = System::Get();
$user = SimpleSession::Get()->GetUser();

// fetch the group and make sure they're the mod
$group = $world->getGroupById($_REQUEST['groupId']);
$name = $group->title;
$isModerator = false;
if($pageArgs['pageActor'] == 'admin') $isModerator = true;
if(!is_null($group->moderator) ) {
	if($group->moderator->id == $pageArgs['userId']) $isModerator = true; 
}
if($group->org_id) {
    echo javascriptalert('This group is part of an organization and may not be deleted directly');
    return print redirect('group.info&groupId='.$group->id);
}
	
if (!$group || !$isModerator) {
	echo javascriptalert('A group may only be deleted by a moderator');
	return print redirect('group.info&groupId='.$group->id);
} elseif(!is_null($group->org_id)) {
	echo javascriptalert("An organization's group may not be deleted until the organization is deleted.\n\nOperation canceled");
	return print redirect('group.info&groupId='.$group->id);
	
} else {
	
	$group->Delete();
	
}
echo javascriptalert('Group '.$name.' has been deleted');
print redirect('group.list');
			
}?>