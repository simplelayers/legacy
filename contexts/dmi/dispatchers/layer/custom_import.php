<?php

use utils\PageUtil;
use auth\Context;
use model\Permissions;
use subnav\LayerSubnav;
use subnav\SubnavFactory;
use utils\ParamUtil;

/**
 * The HTML page for a utility to generate HTML iframe paragraphs.
 * @package Dispatchers
 */
/**
 */
function _config_iframe1()
{
    $config = array();
    $config['isModule'];
    // Start config
    // Stop config
    return $config;
}

function getLayerInfo() {}

function _dispatch_custom_import($template, $args, $org, $pageArgs)
{

    $world = System::Get();
    $userId = ParamUtil::Get($args, 'user');
    $user = ($args['user'] instanceof  Person) ? $args['user'] : Person::Get($userId);

    $pageArgs['appsPath'] = APPS;
    $ini = System::GetIni();

    if ($user->community) {
        return print redirect('layer.list');
    }
    $layerId = ParamUtil::Get($args, 'layer');
    $layer = Layer::GetLayer($layerId);
    // load the project and verify their access
    $type = ParamUtil::Get($args, 'type');
    $pageArgs['layerId'] = $layer->id;
    $pageArgs['pageSubnav'] = 'data';

    $subnav = SubnavFactory::GetNav(SubnavFactory::SUBNAV_LAYER,$pageArgs);
 
    $subnav->makeDefault($layer, $user);
    $template->assign('subnav', $subnav->fetch());




    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinLayerArgs($template);
    $pageArgs['customType'] = $type;
    $perm = $layer->getPermissionById($user->id);
    if ($perm < Permissions::EDIT) {
        print javascriptalert('You do not have permission to configure this layer.');
        return print redirect('layers.list');
    }
    $pageArgs['perm'] = $perm;
    #$template->assign('worldurl',WEBROOT);//$world->config['url']);


    #$pageArgs['pageTitle'] = 'Maps - Embed Map '.$project->name;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinLayerArgs($template);

    $template->display('layer/custom_import.tpl');
}
