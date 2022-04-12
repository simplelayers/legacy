<?php
use utils\ParamUtil;

/**
 * Remove the specified friend from your buddy list; this is called from the peopleinfo page.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_remove()
{
    $config = Array();
    // Start config
    $config["sendWorld"] = false;
    // Stop config
    return $config;
}

function _dispatch_remove($template, $args)
{
    $user = $args['user'];
    $contactId = ParamUtil::GetOne($args, 'contactId', 'id');
    $contactIds = ParamUtil::GetOne($args, 'contactIds', 'ids');
    
    if (! is_null($contactId)) {
        $user->buddylist->removePersonById($contactId);
    } elseif (! is_null($contactIds)) {
        foreach ($contactIds as $id) {
            $user->buddylist->removePersonById($id);
        }
    }
    if(ParamUtil::GetBoolean($args,'noreply')) die();
    print redirect("contact.list");
}
?>
