<?php
use enums\AccountTypes;
use utils\PageUtil;
use model\Permissions;
/**
 * The form for editing a layer's properties: name, category, description, etc.
 * @package Dispatchers
 */
/**
  */
function _config_editwms1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_editwms1($template, $args,$org,$pageArgs) {
	$user = SimpleSession::Get()->GetUser();
	$world = System::Get();

	if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:',Permissions::VIEW)) {
	    javascriptalert('You are not allowed to view layer details.');
	    redirect('layer.list');
	}
	
	// load the layer and verify their access
	$layer = $world->getLayerById($_REQUEST['id']);
	
	
	if(!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
		return print redirect('layer.info&id='.$_REQUEST['id']);
	}
	
	$pageArgs['pageSubnav'] = 'data';
	$pageArgs['layerId'] = $layer->id;
	PageUtil::SetPageArgs($pageArgs, $template);
	$pageArgs = PageUtil::MixinLayerArgs($template);
	$lastModified = explode(':',$layer->last_modified);
	array_pop($lastModified);
	$lastModified = implode(':',$lastModified);
	
	$pageArgs['pageTitle'] = "Data - Editing WMS Layer {$layer->name} owned by <a href=\"".BASEURL."?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: ".ucfirst($pageArgs['layerAccess'])."</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
	
	$template->assign('layer',$layer);

	// are they allowed to be doing this at all?
	/*if ($layer->owner->id != $user->id and $user->accounttype < AccountTypes::GOLD) {
		print javascriptalert('You must upgrade your account to edit others\' Layers.');
		return print redirect("layer.list");
	}*/

	// an assocarray of the owner's friends; used for giving the Layer away to someone else
	$friends = array();
	if($layer->owner->id == $user->id) {
		foreach ($user->buddylist->getFlatListOfPeople() as $f) $friends[$f->id] = "{$f->username} -- {$f->realname}";
	}
	$template->assign('friends',$friends);

	$isOwner = ($layer->owner->id  == $user->id);
	// the base URL for the WMS server, and the preselected layer for the WMS server
	$baseurl = pruneWMSurl($layer->url,true);
	preg_match('/LAYERS=([^&]+)/i', $layer->url, $baselayer);
	$baselayer = sizeof($baselayer) ? $baselayer[1] : '';
	$template->assign('baseurl',$baseurl);
	$template->assign('baselayer',$baselayer);
	$template->assign('isowner', $isOwner);
	// and the template, as always
	$template->assign('radio', true);
	$template->assign('hideEmail',true);
	$template->assign('canGiveAway',$user->accounttype >= AccountTypes::GOLD and $isOwner);
$template->assign('hideBook',true);
	$template->display('layer/editwms1.tpl');
}?>
