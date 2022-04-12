<?php

use enums\AccountTypes;
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
function _config_editvector1() {
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_editvector1($template, $args, $org, $pageArgs) {

    $user = SimpleSession::Get()->GetUser();
    $world = System::Get();

    if (!Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::VIEW)) {
        javascriptalert('You are not allowed to view layer details.');
        redirect('layer.list');
    }

    // load the layer and verify their access
    $layer = $world->getLayerById($_REQUEST['id']);
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['layerId'] = $layer->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    $pageArgs = PageUtil::MixinLayerArgs($template);

    if (!$layer) {
        print javascriptalert('Layer not found');
        return print redirect('layer.list');
    }
    $permission = $layer->getPermissionById($user->id);
    if (!$layer or $permission < AccessLevels::EDIT) {
        return print redirect('layer.info&id=' . $_REQUEST['id']);
    }


    $template->assign('hasEditPrivilege', Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::EDIT));
    $template->assign('hasGivePrivilege', (ParamUtil::Get($pageArgs, 'pageActor') == 'admin') || ParamUtil::Get($pageArgs, 'isOwner'));
    $template->assign('minscale', $layer->minscale);

    $template->assign('layer', $layer);
    if (isset($_REQUEST["tag"])) {
        $template->assign('tag', $_REQUEST["tag"]);
    } else {
        $template->assign('tag', false);
    }

    // are they allowed to be doing this at all?
    /*
     * if ($layer->owner->id != $user->id and $user->accounttype < AccountTypes::GOLD) {
     * print javascriptalert('You must upgrade your account to edit others\' Layers.');
     * return print redirect("layer.list");
     * }
     */

    // how many records are in the table?
    $template->assign('recordcount', $layer->getRecordCount());
    $attributes = Array();
    foreach ($layer->getAttributes() as $name => $type) {
        $attributes[] = Array(
            $name,
            $type
        );
    }
    

    $template->assign('attributes', $attributes);
    $template->assign('defaultCriteria', (!is_null($layer->default_criteria) ? explode(';', $layer->default_criteria) : null));
    $ini = System::GetIni();

    // the thumbnail size
    $template->assign('thumbwidth', $ini->thumbnail_width);
    $template->assign('thumbheight', $ini->thumbnail_height);

    $isOwner = ($layer->owner->id == $user->id);
    $canChangeOwner = $isOwner && Permissions::HasPerm($pageArgs['permissions'], ':Layers:Give:', Permissions::VIEW);

    $template->assign('canChangeOwner', $canChangeOwner);

    // an assocarray of the owner's friends; used for giving the Layer away to someone else
    $friends = array();
    if ($layer->owner->id == $user->id) {
        foreach ($user->buddylist->getFlatListOfPeople() as $f)
            $friends[$f->id] = "{$f->username} -- {$f->realname}";
    }

    $template->assign('friends', $friends);

    /*
     * $subnav = new LayerSubnav();
     *
     * $subnav->makeDefault($layer, $user);
     *
     * $template->assign('subnav',$subnav->fetch());
     */


    $type = GeomTypes::GetGeomType($layer->geomtype);
    $lastModified = explode(':', $layer->last_modified);
    array_pop($lastModified);
    $lastModified = implode(':', $lastModified);

    $pageArgs['pageTitle'] = "Data - Editing<img src=\"" . BASEURL . "media/empty.png\" width=\"16\" height=\"16\" style=\"vertical-align:middle;margin-left:15px; margin-right:8px;\" class=\"{$type}_ico\" alt=\"Layer type: {$type}\"></img> {$layer->name} owned by <a href=\"" . BASEURL . "?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: " . ucfirst($pageArgs['layerAccess']) . "</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
    $pageArgs['attributes'] = $attributes;
    $pageArgs['defaultCriteria'] = (!is_null($layer->default_criteria) ? explode(';', $layer->default_criteria) : null);
    $pageArgs['minscale'] = $layer->minscale;
    PageUtil::SetPageArgs($pageArgs, $template);
    $template->assign('isowner', $isOwner);
    // now draw the HTML for it!
    $template->assign('radio', true);
    $template->assign('hideEmail', true);
    $template->assign('hideBook', true);

    $template->assign('svcsURL', SVCS);
    $template->assign('token', SimpleSession::Get()->id);
    $template->display('layer/editvector1.tpl');
}

?>
