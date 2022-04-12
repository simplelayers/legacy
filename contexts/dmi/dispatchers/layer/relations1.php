<?php
use utils\PageUtil;

/**
 * The form for editing a layer's properties: name, category, description, etc.
 * @package Dispatchers
 */
/**
  */
function _config_relations1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_relations1($template, $args,$org,$pageArgs) {
$user = $args['user'];
$world = $args['world'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
$permission = $layer->getPermissionById($user->id);
if ($layer->owner->id != $user->id) {
   print javascriptalert('Only the owner can edit this Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);

// fetch the existing layer and column choices
// this is stored in the 'url' field but is not accessible via $layer->url because of the ORM
$config = $world->db->Execute('SELECT url FROM layers WHERE id=?', array($layer->id) )->fields['url'];
$config = unserialize($config);
$template->assign('config',$config);

// get the list of all the user's vector layers; these are candidate tables for the view
$tables = array(''=>'');
foreach ($user->listLayers() as $l) {
    if ($l->type != LayerTypes::VECTOR) continue;
    $tables[$l->id] = $l->name;
}
$template->assign('tables',$tables);

// some blank arrays to start the column selectors; they populate themselves separately
$template->assign('columns1', array() );
$template->assign('columns2', array() );
$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] =$layer->id;
PageUtil::SetPageArgs($pageArgs, $template);
// now draw the HTML for it!
$template->display('layer/relations1.tpl');
}?>
