<?php

use utils\PageUtil;
use model\Permissions;

/**
 * The form for editing a project's details, e.g. name, description, tags, layer list.
 * @package Dispatchers
 */
/**
  */
function _config_edit1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_edit1($template, $args,$org,$pageArgs) {
    $user = $args['user'];
    $world = $args['world'];
    $ini = System::GetIni();
    
    if (!Permissions::HasPerm($pageArgs['permissions'],':MapsDetails:',Permissions::EDIT)) {
        if (Permissions::HasPerm($pageArgs['permissions'],':MapsDetails:',Permissions::VIEW)) {
            return print redirect('project.info&id='.$_REQUEST['id']);
        } else {
            print javascriptalert('You do not have permissions to edit map details.');
            return print redirect('project.list');
        }
    }
    
    // load the project and verify their access
    $project = $world->getProjectById($_REQUEST['id']);
    if (!$project or $project->getPermissionById($user->id) < AccessLevels::EDIT) {
       print javascriptalert('You do not have permission to edit that Map.');
       return print redirect('project.list');
    }
    $template->assign('project',$project);
    
    // are they allowed to be doing this at all?
    /*if ($project->owner->id != $user->id and $user->accounttype < AccountTypes::GOLD) {
        print javascriptalert('You must upgrade your account to edit others\' Maps.');
        return print redirect("layer.list");
    }*/
    
    // if they're not the owner, they get some additional options, e.g. toggling bookmarks and seeing the owner's name
    $owner = $project->owner;
    
    
    $pageArgs['pageSubnav'] = 'maps';
    $pageArgs['mapId'] = $project->id;
    $pageArgs['pageTitle'] = 'Editing Map - '.$project->name;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    
    // the default bbox for the project, pre-parsed
    $template->assign('bbox', explode(',',$project->bbox) );
    
    // get the IDs of layers already in the project, in comma-joined format for JavaScript
    //$template->assign('already', $project->getLayerIds() );
    $template->assign('maxlayersinproject',$ini->max_project_layers);
    
    // whew! wasn't that fun? wait til you see the JavaScript in projectedit1.tpl
    $template->assign('geomTypes',GeomTypes::GetEnum()->ToJSObj('geomTypes'));
    
    $template->assign('id', $project->id);
    $template->assign('isowner', ($project->owner->id  == $user->id));
    $template->display('project/edit1.tpl');
}?>