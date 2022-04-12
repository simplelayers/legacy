<?php
use subnav\LayerSubnav;
use utils\PageUtil;
use model\Permissions;

/**
 * The form for editing a layer's properties: name, category, description, etc.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_editrelational1()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_editrelational1($template, $args, $org, $pageArgs)
{
    $user = SimpleSession::Get()->GetUser();
    $world = System::Get();
    
    if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::VIEW)) {
        javascriptalert('You are not allowed to view layer details.');
        redirect('layer.list');
    }
    
    // load the layer and verify their access
    $layer = $world->getLayerById($_REQUEST['id']);
    if($layer->geom_type==null) $layer->setLayerGeomType();
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['layerId'] = $layer->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    $pageArgs = PageUtil::MixinLayerArgs($template);
    
    $lastModified = explode(':',$layer->last_modified);
    $lastModified = implode(':',$lastModified);
    
    $pageArgs['pageTitle'] = "Data - Editing Relational Layer {$layer->name} owned by <a href=\"".BASEURL."?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: ".ucfirst($pageArgs['layerAccess'])."</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
    
    $permission = $layer->getPermissionById($user->id);
    if ($layer->owner->id != $user->id) {
        return print redirect('layer.info&id=' . $_REQUEST['id']);
    }
    $template->assign('layer', $layer);
    
    // how many records are in the table?
    $template->assign('recordcount', $layer->getRecordCount());
    // the thumbnail size
    $template->assign('thumbwidth', $ini->thumbnail_width);
    $template->assign('thumbheight', $ini->thumbnail_height);
    
    // the thumbnail size
    $template->assign('thumbwidth',  $ini->thumbnail_width);
    $template->assign('thumbheight', $ini->thumbnail_height);
    $subnav = new LayerSubnav();
    $subnav->makeDefault($layer, $user);
    $template->assign('subnav', $subnav->fetch());
    $template->assign('hideEmail', true);
    $template->assign('hideBook', true);
    $template->assign('isowner', ($layer->owner->id == $user->id));
    // now draw the HTML for it!
    PageUtil::SetPageArgs($pageArgs, $template);
    
    $template->display('layer/editrelational1.tpl');
}
?>
