<?php
use utils\PageUtil;

/**
 * Process the importshapefiles1 form, examining the zipfile and importing any shapefiles into new vector layers.
 * @package Dispatchers
 */
/**
  */
function _config_metadata1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_metadata1($template, $args,$org,$pageArgs) {
	$user = $args['user'];
	$world = System::Get();
	$layer = $world->getLayerById($_REQUEST['id']);
	$pageArgs['pageSubnav'] = 'data';
	$pageArgs['layerId'] = $layer->id;
	$pageArgs['pageTitle'] = 'Metadata for layer '.$layer->name;
	PageUtil::SetPageArgs($pageArgs, $template);
	
	$template->assign('layer',$layer);
	$template->assign('user',$user);
	
	
	
	$edit = ($layer->getPermissionById($user->id)>=3);
	
	if($layer->metadata != "") $toPrint = printPretty($layer->metadata, $edit);
	else $toPrint = "";
	$template->assign('metadata',$toPrint);
	//$template->assign('metadataxml',array_to_xml($layer->metadata));
	$template->assign("hasMetadata", $layer->hasMetadata());
	$template->display('layer/metadata1.tpl');
}

function printPretty($parent, $edit=false){
	$data = "";
	foreach($parent as $name => $child){
		$data .= '<fieldset>';
		$data .= '<legend>';
		$data .= ''.$name.''.($edit ? '<img class="edit" src="media/icons/page_edit.png"/>' : '');
		$data .= '</legend>';
		if(is_array($child)){
			$data .= printPretty($child, $edit);
		}else{
			$data .= '<span>';
			$data .= $child;
			if($edit) $data .= ''.($edit ? '<img class="edit" src="media/icons/page_edit.png"/>' : '').'';
			$data .= '</span>';
		}
		if(is_array($child)){$data .= '<img class="add" src="media/icons/add.png"/>';}
		$data .= '</fieldset>';
	}
	return $data;
}
?>
