<?php
function _config_get() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config['sendWorld'] = true;
	
	// Stop config
	return $config;
}

function _headers_get() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'xml';
	switch($_REQUEST['format'] ) {
		case "json":	
		case "ajax":
			header('Content-Type: application/json');
			break;
		case "xml":
		default:
			header('Content-Type: text/xml');
			break;
	}	
}
/**
  * @ignore
 */
function _dispatch_get($template, $args) {
	/* @var $world World */
	$world = System::Get();
	$user = SimpleSession::Get()->GetUser();
    $wapi = $world->wapi;	
	$isDefault = isset($_REQUEST['isDefault']);
	list($project,$permission) = array_values($wapi->RequireProject());
	
	$isDefault = RequestUtil::Get('isDefault')=='1';
	if(!$isDefault) {
	    $projectLayer= $wapi->RequireProjectLayer();
	    $template->assign('projectlayer',$projectLayer);
	}
	$template->assign('project',$project);
	
	$layer = $wapi->RequireLayer();

	$template->assign('layer',$layer);
// if they're requesting the Layer instead of the ProjectLayer, they need to be the owner

	//if ($isDefault and $layer->owner->id != $user->id) throw new Exception("Invalid access: Must be layer owner");
	if($layer->getPermissionById($user->id) < AccessLevels::READ) {
	    WAPI::HandleError(new Exception('Need read or better permission to perform this action'));
	}
	$showRules = !WAPI::GetFlagParam('norules',false);
	   
	if($wapi->format == WAPI::FORMAT_XML) {
		$wapi->ToTemplate($template);
		$template->assign('showrules',$showRules);
		$template->assign('scheme',$layer->colorscheme);
		$entries = $tpl = $symbol = $fill = $stroke = $size = $symbol = $column = null;
		if($isDefault) {
		    $entries = $layer->colorscheme->getAllEntries();
		    $tpl =  'wapi/layer/layercolorscheme.tpl';
		} else {
			$entries = $projectLayer->colorscheme->getAllEntries();
			$tpl = 'wapi/layer/projlayercolorscheme.tpl';
		}	
		if(count($entries)) {
		    $entry = $entries[0];
		    $symbol = $entry->symbol;
		    $fill = $entry->fill_color;
		    $size = $entry->symbol_size;
		    $stroke = $entry->stroke_color;
		    $column = $entry->colorschemecolumn;
		}

		$template->assign('symbol',$symbol);
		$template->assign('fill',$fill);
		$template->assign('size',$size);
		$template->assign('stroke',$stroke);
		$template->assign('column',$column);
		$template->assign('entries', $entries );
		$template->display($tpl);
		
	} else {
		$json = array();
		$json['classification'] = array();
		$json['classification']['pid'] = $project->id;
		$json['classification']['lid'] = $layer->id;
		$json['classification']['plid'] = $projectLayer->id;
		$json['classification']['type'] = $projectLayer->colorschemetype;
		$json['classification']['fill'] = strippound($projectLayer->colorschemefill);
		$json['classification']['stroke'] = strippound($projectLayer->colorschemestroke);
		$json['classification']['size'] = $projectLayer->colorschemsymbolsize;
		$json['classification']['symbol'] = $projectLayer->colorschemesymbol;
		$json['classification']['column'] = $projectLayer->colorschemecolumn;
	
		if($showRules) {
			$json['classification']['classes']=array();
			
			$entries =  $projectLayer->colorscheme->getAllEntries();
			foreach($entries as $entry) {
				$class = array();
				$class['crid'] = $entry->id;
				$class['description'] = $entry->description;
				$class['priority'] = $entry->priority;
				$class['fill'] = strippound($entry->fill);
				$class['stroke'] = $entry->stroke_color;
				$class['field'] = $entry->criteria1;
				$class['operator'] = $entry->criteria2;
				$class['value'] = $entry->criteria3;
				$class['size'] = $entry->size;
				$json['classification']['classes'][] = $class;				
			}
		}
		echo json_encode($json);
	}

}?>
