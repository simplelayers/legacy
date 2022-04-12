<?php
/**
 * Delete the specified layer; this is called from the layeredit* forms
 * @package Dispatchers
 */
/**
  */
function _config_delete() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_delete($template, $args) {
$user = $args['user'];
$world = $args['world'];

// load the layer and verify their access
$layer = $world->getlayerById($_REQUEST['id']);
if (!$layer or $layer->owner->id != $user->id) {
   print javascriptalert('Layers can only be deleted by their owner.');
   return print redirect('layer.list');
}

// a manual query, but only for vector layers
// go over this user's relational layers and delete them as well IF they used this table
if ($layer->type == LayerTypes::VECTOR) {
    $relayers = $world->db->Execute("SELECT id,url FROM layers WHERE owner=? AND type=?", array($user->id,LayerTypes::RELATIONAL) )->getRows();
    foreach ($relayers as $relayer) {
        $info = unserialize($relayer['url']);
        if ($info['table1'] == $layer->id) $user->getLayerById($relayer['id'])->delete();
        if ($info['table2'] == $layer->id) $user->getLayerById($relayer['id'])->delete();
    }
}

// self destruct
$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_DELETE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
$report = new Report($args['world'],$reportEntry);
$layer->delete();
$report->commit();
// go home
print redirect('layer.list');

}?>
