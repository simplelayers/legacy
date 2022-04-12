<?php

use utils\PageUtil;
use model\Permissions;

/**
 * Show information about a project: tags, owner, etc.
 * @package Dispatchers
 */
/**
  */
function _config_info() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_info($template, $args,$org,$pageArgs) {
    $user = $args['user'];
    $world = $args['world'];
    
    
    if (!Permissions::HasPerm($pageArgs['permissions'],':MapsDetails:',Permissions::VIEW)) {
        print javascriptalert('You do not have permissions to view map details.');
        return print redirect('project.list');
    }
    
    $project = $world->getProjectById($_REQUEST['id']);
    if (!$project or !$project->getPermissionById($user->id)) {
       print javascriptalert('That Map was not found, or is unlisted.');
       return print redirect('project.list');
    }
    $template->assign('project',$project);
    
    // if they're not the layerowner, they get some additional options, e.g. toggling bookmarks and seeing the owner's name
    $owner = $project->owner;
    if ($owner->id == $user->id) {
       $template->assign('ownerlink','');
       $template->assign('bookmarklink','');
    }
    elseif ($user->isProjectBookmarkedById($project->id)) {
       $template->assign('ownerlink',"<a href=\".?do=social.peopleinfo&id={$owner->id}\">owner</a>");
       $template->assign('bookmarklink',"<a href=\".?do=project.removebookmark&id={$project->id}\">remove bookmark</a>");
    }
    else {
       $template->assign('ownerlink',"<a href=\".?do=social.peopleinfo&id={$owner->id}\">owner</a>");
       $template->assign('bookmarklink',"<a href=\".?do=project.addbookmark&id={$project->id}\">add bookmark</a>");
    }
    
    // the hyperlinked tags
    $template->assign('taglinks', activate_tags($project->tags,'.?do=project.search&search=') );
    
    $pageArgs['pageSubnav'] = 'maps';
    $perm = $project->getPermissionById($user->id);
    $perm = AccessLevels::GetLevel($perm);
    $pageArgs['pageTitle'] = 'Information for Map: '.$project->name." owned by <a href=\"".BASEURL.'?do=contact.info&contactId='.$project->owner->id."\">{$project->owner->realname}</a> - <i>My Access: $perm</i>";
    $pageArgs['mapId'] = $project->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    
    // go for it
    $template->display('project/info.tpl');

}?>
