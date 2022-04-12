<?php
use utils\PageUtil;

/**
 * Print info about a person.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_info()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_info($template, $args, $org, $pageArgs)
{
    $pageArgs = PageUtil::MixinGroupArgs($template);
    $user = SimpleSession::Get()->GetUser();
    $sys = System::Get();
    $group = $sys->getGroupById($_REQUEST['groupId']);
    $groupActor = $pageArgs['groupActor'];
    
    
    
    $isModerator = $groupActor=='group_owner';  
    
    $template->assign('isModerator', $isModerator);
    
    
    $pageArgs['pageTitle'] = 'Group - Details for ' . $group->title;
    if ($group)
        $pageArgs['groupId'] = $group->id;
    $pageArgs['pageSubnav'] = 'community';
    $pageArgs['groupId'] = $group->id;
    $pageArgs['groupName'] = $group->title;
    $pageArgs['groupOrg'] = $group->org_id;
    PageUtil::SetPageArgs($pageArgs, $template);
    
    $template->assign('taglinks', activate_tags($group->tags, './?do=group.list&tag='));
    $template->assign('group', $group);
    $template->assign('filter', false);
    $template->assign('invite', ($group->invite != 'f' ? true : false));
    $template->assign('hidden', ($group->hidden != 'f' ? true : false));
    $moderators = array(
        '' => '(leave as yourself)'
    );
    foreach ($group->getMembers() as $p)
        $moderators[$p->id] = "{$p->realname} ({$p->username})";
    $template->assign('moderators', $moderators);
    /*
     * $subnav = new GroupSubnav();
     * $subnav->makeDefault($group, $user);
     * $template->assign('subnav',$subnav->fetch());
     */
    PageUtil::SetPageArgs($pageArgs, $template);
    $template->display('group/info.tpl');
}
?>