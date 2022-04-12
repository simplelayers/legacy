<?php
use subnav\LayerSubnav;
use utils\PageUtil;
use model\Permissions;
use utils\ParamUtil;

/**
 * The form for editing a layer's properties: name, category, description, etc.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_editrelatable1()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_editrelatable1($template, $args, $org, $pageArgs)
{
    $user = SimpleSession::Get()->GetUser();
    $world = System::Get();
    
    if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::VIEW)) {
        javascriptalert('You are not allowed to view layer details.');
        redirect('layer.list');
    }
    
    // load the layer and verify their access
    $layer = $world->getLayerById($_REQUEST['id']);
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['layerId'] = $layer->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    $pageArgs = PageUtil::MixinLayerArgs($template);
    
    $lastModified = explode(':',$layer->last_modified);
    $lastModified = implode(':',$lastModified);
    
    $pageArgs['pageTitle'] = "Data - Editing Relatable Layer {$layer->name} owned by <a href=\"".BASEURL."?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: ".ucfirst($pageArgs['layerAccess'])."</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
    
    $permission = $layer->getPermissionById($user->id);
    $isAdmin = !is_null(ParamUtil::Get($pageArgs,'pageActor')=='admin');
    if(!$isAdmin) {
        if ($layer->owner->id != $user->id) {
            return print redirect('layer.info&id=' . $_REQUEST['id']);
        }
    }
    $template->assign('layer', $layer);
    
    // how many records are in the table?
    $template->assign('recordcount', $layer->getRecordCount());
    // the thumbnail size
    
    // the thumbnail size
    $template->assign('hideEmail', true);
    $template->assign('hideBook', true);
    $template->assign('isowner', ($layer->owner->id == $user->id));
    // now draw the HTML for it!
    PageUtil::SetPageArgs($pageArgs, $template);
    
    $template->display('layer/editrelatable1.tpl');
}
?>
