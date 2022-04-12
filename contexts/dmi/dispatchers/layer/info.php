<?php
use subnav\LayerSubnav;
use utils\PageUtil;

/**
 * Print info about a layer: name, description, tags, etc.
 * @package Dispatchers
 */
/**
  */
function _config_info() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_info($template, $args,$org,$pageArgs) {
$user = $args['user'];
$world = System::Get();
$ini = System::GetIni();

$pageArgs['pageSubnav'] = 'data';



$layer = $world->getLayerById($_REQUEST['id']);
$pageArgs['layerId'] = $layer->id;
PageUtil::SetPageArgs($pageArgs, $template);

if (!$layer or !$layer->getPermissionById($user->id)) {
   print javascriptalert('That layer was not found, or is unlisted.');
   return print redirect('layers.search');
}
$pageArgs = PageUtil::MixinLayerArgs($template);
$lastModified = explode(':',$layer->last_modified);
array_pop($lastModified);
$lastModified = implode(':',$lastModified);

$pageArgs['pageTitle'] = "Data - Viewing Layer {$layer->name} owned by <a href=\"".BASEURL."?do=contact.info&contactId={$layer->owner->id}\">{$layer->owner->realname}</a> - <i>My access: ".ucfirst($pageArgs['layerAccess'])."</i> <div style='float:right;display:inline-block;margin-right: 15px'>Last Modified: $lastModified</div><span style='float:right;width:10px'></span>";
//$pageArgs['pageTitle'] = 'Data - Editing '.ucfirst(strtolower($layer->geomtypestring)).' Layer:'.$layer->name;
PageUtil::SetPageArgs($pageArgs, $template);
$template->assign('layer',$layer);

// if they're not the layerowner, they get some additional options, e.g. toggling bookmarks and seeing the owner's name
$owner = $layer->owner;
$template->assign('isowner',($owner->id == $user->id));

// how many records are in the table?
if ($layer->type == LayerTypes::VECTOR){
	$template->assign('recordcount',$layer->getRecordCount());
}else{
	$template->assign('recordcount','');
}

// the thumbnail size, and whether to even show it
$template->assign('thumbnail',$layer->type!=LayerTypes::WMS);
$template->assign('thumbwidth',$ini->thumbnail_width);
$template->assign('thumbheight',$ini->thumbnail_height);

// "activate" the tag list into hyperlinks
$template->assign('taglinks', activate_tags($layer->tags,'.?do=layer.search&search=') );
$subnav = new LayerSubnav();
$subnav->makeDefault($layer, $user);
$template->assign('subnav',$subnav->fetch());
// all set!
$template->display('layer/info.tpl');

}?>