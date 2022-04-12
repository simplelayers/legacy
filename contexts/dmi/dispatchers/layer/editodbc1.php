<?php

use utils\PageUtil;
use model\Permissions;
/**
 * The form for editing a layer's properties: name, category, description, etc.
 * @package Dispatchers
 */
/**
  */
function _config_editodbc1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_editodbc1($template, $args,$org,$pageArgs) {
$user = $args['user'];
$world = $args['world'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] = $layer->id;
PageUtil::SetPageArgs($pageArgs, $template);
$pageArgs = PageUtil::MixinLayerArgs($template);
$lastModified = explode(':',$layer->last_modified);
array_pop($lastModified);
$lastModified = implode(':',$lastModified);

$pageArgs['pageTitle'] = "Data - Editing ODBC Layer {$layer->name} owned by <a href=\"".BASEURL."?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: ".ucfirst($pageArgs['layerAccess'])."</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
PageUtil::SetPageArgs($pageArgs, $template);

$isOwner = ($layer->owner->id == $user->id);
$canChangeOwner = $isOwner && Permissions::HasPerm($pageArgs['permissions'],':Layers:Give:',Permissions::VIEW);
 
$template->assign('canChangeOwner', $canChangeOwner);


$permission = $layer->getPermissionById($user->id);
if (!$layer or $permission < AccessLevels::EDIT) {
   return print redirect('layer.info&id='.$_REQUEST['id']);
}
$template->assign('layer',$layer);

// are they allowed to be doing this at all? 
/*if ($layer->owner->id != $user->id and $user->accounttype < AccountTypes::PLATINUM) {
    print javascriptalert('You must have at least Platinum level access to use ODBC layers.');
    return print redirect("layer.list");
}*/

// the edit links depend on this
$template->assign('editable', $permission >= AccessLevels::EDIT );

// links for downloading
if ($permission >= AccessLevels::COPY) {
   $template->assign('downloadshp', true );
   $template->assign('downloadkml',true);
   $template->assign('downloadcsv',true);
   if ($layer->geomtype == GeomTypes::POINT or $layer->geomtype == GeomTypes::LINE) $template->assign('downloadgpx',true);
}


// an assocarray of the owner's friends; used for giving the Layer away to someone else
$friends = array();
if ($layer->owner->id == $user->id) {
   foreach ($user->buddylist->getFlatListOfPeople() as $f) $friends[$f->id] = "{$f->username} -- {$f->realname}";
}
$template->assign('friends',$friends);

$ports = System::GetODBCPorts();
// and the template, as always
$template->assign('odbcserveroptions', array_keys($ports) );
$template->assign('odbcserverports', $ports );
$template->assign('odbcservernames', implode(', ',array_keys($ports)) );
$template->assign('odbcinfo', $layer->url );

$template->assign('hideEmail',true);
$template->assign('hideBook',true);
$template->assign('isowner', ($layer->owner->id  == $user->id));
$template->display('layer/editodbc1.tpl');
}?>
