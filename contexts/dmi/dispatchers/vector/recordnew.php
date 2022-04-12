<?php

/**
 * Insert a new record into the specified vector layer; called from vectorrecords.
 * @package Dispatchers
 */
/**
 */
function _config_recordnew()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_recordnew($template, $args)
{
    
    $world = System::Get();
    $user = SimpleSession::Get()->GetUser();
    
    // load the layer and verify their access
    $layer = $world->getLayerById($_REQUEST['id']);
  
    if (! $layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
        print javascriptalert('You do not have permission to edit that Layer.');
        print redirect('layer.list');
        return;
    }
    
    // create the new record, and then pass them over to it
    $record = $layer->insertRecord(array());
    
   // System::GetDB()->debug=true;
    $layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.records&id=" . $layer->id, 8);
    print redirect("vector.recordedit1&id={$layer->id}&gid={$record['gid']}");
}
?>
