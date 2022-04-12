<?php
use utils\PageUtil;
use model\Permissions;
use model\License;

/**
 * View a Layer's statistics: who has it bookmarked, where it's used, etc.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_edit1()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_edit1($template, $args, $org, $pageArgs)
{
    $user = SimpleSession::Get()->GetUser();
    $world = System::Get();
    
    if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::VIEW)) {
        javascriptalert('You are not allowed to view layer details.');
        redirect('layer.list');
    }
    
    if (isset($_REQUEST['id'])) {
        $template->assign('id', $_REQUEST['id']);
        $layer = $world->getLayerById($_REQUEST['id']);
        if (! $layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
            return print redirect('layer.info&id=' . $_REQUEST['id']);
        }
    } else {
        if ($user->community && count($user->listLayers()) >= 3) {
            print javascriptalert('You cannot create more than 3 layers with a community account.');
            return print redirect('layer.list');
        }
        $template->assign('id', 'new');
        $layer = false;
    }
    
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['pageTitle'] = ($layer) ? 'Data - Editing Layer Collection ' . $layer->name : 'Data - New Layer Collection';
    
    if ($layer)
        $pageArgs['layerId'] = $layer->id;
    
    if ($pageArgs['reachedLayerLimit'] == 'true') {
        print javascriptalert('Your organization has reached its limit of ' . $pageArgs['max_layers'] . ' layers.');
        return print redirect('layer.list');
    }
    PageUtil::SetPageArgs($pageArgs, $template);
    $pageArgs = PageUtil::MixinLayerArgs($template);
    $lastModified = explode(':',$layer->last_modified);
    array_pop($lastModified);
    $lastModified = implode(':',$lastModified);
    
    $pageArgs['pageTitle'] = "Data - Editing Layer Collection {$layer->name} owned by <a href=\"".BASEURL."?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: ".ucfirst($pageArgs['layerAccess'])."</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
    
    if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::CREATE)) {
        print javascriptalert('You do not have permission to create new layers.');
        return print redirect('layer.list');
    }
    
    PageUtil::SetPageArgs($pageArgs, $template);
    $eGeomTypes = GeomTypes::GetVectorEnum();
    
    $template->assign('geomtypes', $eGeomTypes->ToJSObj('geomTypes'));
    $template->assign('layer',$layer);
    // now draw the HTML for it!
    $template->display('layer/collection/edit1.tpl');
}
?>
