<?php

use utils\PageUtil;
use model\Permissions;

/**
 * The form for editing a projects' permissions.
 * 
 * @package Dispatchers
 */

/**
 */
function _config_permissions() {
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_permissions($template, $args, $org, $pageArgs) {
    $world = $args['world'];
    $user = $args['user'];

    $layer = $world->getLayerById($_REQUEST['id']);



    if (!$layer or ($layer->owner->id != $user->id) and!($pageArgs['pageActor'] == 'admin')) {
        print javascriptalert('Layers can only be shared by their owner.');
        return print redirect('layer.list');
    }

    if (isset($_REQUEST["changes"])) {
        $changes = json_decode($_REQUEST["changes"], true);
        
        if ($changes) {
            
            if (isset($changes['people'])) {
                if (isset($changes['people']['permissions'])) {
                    foreach ($changes['people']['permissions'] as $userId => $level) {
                        if($userId === (int)\System::GetPublicUser(true)) {
                            $layer->sharelevel = $level;
                        }
                        $layer->setContactPermissionById($userId, $level);
                        $layer = $world->getLayerById($_REQUEST['id']);
                    }
                }
                if (isset($changes['people']['reporting'])) {
                    foreach ($changes['people']['reporting'] as $userId => $level) {
                        if($userId === (int)\System::GetPublicUser(true)) {
                            $layer->reporting_level = (double)$level;
                        }
                        $layer->setContactRptLvlById($userId, $level);
                        $layer = $world->getLayerById($_REQUEST['id']);
                    }
                }
            }
            
            if (isset($changes['groups'])) {
                if (isset($changes['groups']['permissions'])) {
                    foreach ($changes['groups']['permissions'] as $groupId => $level) {
                        $layer->setGroupPermissionById($groupId, $level);
                    }
                }
                if (isset($changes['groups']['reporting'])) {
                    foreach ($changes['groups']['reporting'] as $groupId => $level) {
                        $layer->setGroupRptLvlById($groupId, $level);
                    }
                }
            }

            if (isset($changes["public"])) {
                if (isset($changes['public']['permissions'])) {
                    $layer->sharelevel = $changes['public']['permissions'];
                    $layer->setContactPermissionById(\System::GetPublicUser(true), $changes['public']['permissions']);
                    $layer = $world->getLayerById($_REQUEST['id']);
                }
                if(isset($changes['public']['reporting'])) {
                    $layer->reporting_level = $changes['public']['reporting'];                    
                    $layer->setContactRptLvlById(\System::GetPublicUser(true), $changes['public']['reporting']);
                    $layer = $world->getLayerById($_REQUEST['id']);
                }
               
            }
        }
    }
    
    
    $template->assign('layer', $layer);
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['pageTitle'] = 'Permissions for layer: ' . $layer->name;
    $pageArgs['layerId'] = $layer->id;

    $hasExternalPerm = Permissions::HasPerm($pageArgs['permissions'], ':Layers:Sharing:External:', Permissions::VIEW);
    $template->assign('needRptLvl',true);
    $template->assign('hasExternalPerm', $hasExternalPerm);

    $template->assign('isEditor', $pageArgs['isEditor']);
    PageUtil::SetPageArgs($pageArgs, $template);

    // and hand it off for rendering
    $template->display('layer/permissions.tpl');
}

?>
