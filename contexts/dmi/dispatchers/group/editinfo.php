<?php
use utils\ParamUtil;
use utils\PageUtil;

/**
 * Process the form generated by details1, saving their updated account info.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_editinfo()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_editinfo($template, $args, $org, $pageArgs)
{
    $user = $args['user'];
    $world = System::Get();
    $params = WAPI::GetParams();
    $group = $world->getGroupById(ParamUtil::RequiresOne($params, 'id'));
    $pageArgs['pageSubnav'] = 'community';
    $pageArgs['groupId'] = $group->id;
    $pageArgs['groupName'] = $group->title;
    PageUtil::SetPageArgs($pageArgs, $template);
    list ($name, $desc, $tags, $invite, $hidden) = ParamUtil::ListValues($params, 'name', 'desc', 'tags', 'invite', 'hidden');
    
    // simply set the info from the form fields, doing some sanitation
    $updates = Array();
    if ($group->title != $name)
        $updates['title'] = $name;
    if ($group->description != $desc)
        $updates['description'] = $desc;
    if ($group->tags != $tags)
        $updates['tags'] = $tags;
    $updates['invite'] = ParamUtil::BoolToTF($invite);
    $updates['hidden'] = ParamUtil::BoolToTF($hidden);
    
    if (ParamUtil::Get($params, 'moderator', '') != '') {
        $group->modById($_REQUEST['moderator'], true);
    }
    
    if (count(array_keys($updates)))
        $group->Update($updates);
        // done
    print redirect('group.info&groupId=' . $group->id);

}?>