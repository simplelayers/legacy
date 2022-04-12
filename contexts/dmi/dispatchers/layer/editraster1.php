<?php
use utils\PageUtil;
use model\Permissions;

/**
 * Page for editing a raster Layer.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_editraster1()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_editraster1($template, $args, $org, $pageArgs)
{
	$user = SimpleSession::Get()->GetUser();
	$world = System::Get();
	
	if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:',Permissions::VIEW)) {
	    javascriptalert('You are not allowed to view layer details.');
	    redirect('layer.list');
	}
    $ini = System::GetIni();
    
    $layerId = RequestUtil::Get('id');
    $layer = $world->getLayerById($layerId);
    
    $pageArgs['layerId'] = $layerId;
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['pageTitle'] = 'Data - Editing Layer ' . $layer->name;
    PageUtil::SetPageArgs($pageArgs, $template);
    $pageArgs = PageUtil::MixinLayerArgs($template);
    $lastModified = explode(':',$layer->last_modified);
    array_pop($lastModified);
    $lastModified = implode(':',$lastModified);
    
    $pageArgs['pageTitle'] = "Data - Editing {$layer->name} owned by <a href=\"".BASEURL."?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: ".ucfirst($pageArgs['layerAccess'])."</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
    
    PageUtil::SetPageArgs($pageArgs, $template);
    
    // load the layer and verify their access
    
    $template->assign('layer', $layer);
    
    $permission = $layer->getPermissionById($user->id);
    
    // are they allowed to be doing this at all?
    if ($permission < AccessLevels::EDIT) {
        print javascriptalert('You do not have permission to edit this layer.');
        return print redirect("layer.list");
    }
    
    // some other constants that are pertinent here
    global $PROJECTIONS;
    $template->assign('projectionlist', $PROJECTIONS);
    $template->assign('maxfilesize', (int) ini_get('upload_max_filesize'));
    
    // the thumbnail size
    $template->assign('thumbwidth', $ini->thumbnail_width);
    $template->assign('thumbheight', $ini->thumbnail_height);
    
    // a simple bool to indicate whether the layer belongs to us
    // this could be done within the template, but this is cleaner
    $template->assign('isowner', $layer->owner->id == $user->id);
    
    // an assocarray of the owner's friends; used for giving the Layer away to someone else
    $friends = array();
    if ($layer->owner->id == $user->id) {
        foreach ($user->buddylist->getFlatListOfPeople() as $f)
            $friends[$f->id] = "{$f->username} -- {$f->realname}";
    }
    
    $template->assign('friends', $friends);
    $template->assign('hideEmail', true);
    $template->assign('hideBook', true);
    $template->assign('isowner', ($layer->owner->id == $user->id));
    // and the template, as always
    $template->assign('radio', true);
    
    $template->display('layer/editraster1.tpl');
}
?>
