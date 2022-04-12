<?php
/**
 * This simply redirects the browser to the proper layeredit*1 action; this makes for a consistent interface.
 * @package Dispatchers
 */
/**
  */
function _config_backup() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

// Keep this as a back up.
	function _dispatch_backup($template, $args) {
	$world= $args['world'];
	$user = $args['user'];

	$layer = $world->getLayerById($_REQUEST['id']);
	if (!$layer) {
	   print javascriptalert('The requested layer does not exist.');
	   return print redirect('layer.list');
	}
	if($layer->type != LayerTypes::VECTOR) return print redirect('layer.edit1&id='.$layer->id);
	switch(strtolower($_REQUEST['action'])){
		case "rollback":
			$layer->rollback();
			break;
		case "backup":
			$layer->backup();
			break;
	}
	return print redirect('layer.edit1&id='.$layer->id);
}
?>
