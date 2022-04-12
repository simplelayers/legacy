<?php
use utils\ParamUtil;
/**
  * Viewer: Given a project ID, generate a legend for it.
  * @package ViewerDispatchers
  * Parameters:
  *
  * project -- The unique ID# of the project.
  *
  * layers -- A comma-joined list of layer-IDs, e.g. 12,34,567,89
  *
  * bgcolor -- The color to use for the legend's baackground, in HTML format (e.g. #000000).
  *            Optional, defaults to #FFFFFF (white)
  *
  * fgcolor -- The color to use for drawing the text of the legend, in HTML format (e.g. #000000).
  *            Optional, defaults to #000000 (black)
  *
  * width -- The width of the legend image, in pixels. Optional; if omitted, it will be automagically generated with no promises about how good it'll look.
  *
  * height -- The minimum height of the legend image, in pixels. Optional; if omitted, it will be automagically generated with no promises about how good it'll look.
  *
  * Return:
  *
  * If the user does not have at least AccessLevels::READ access to the project, then the string "&status=NO&" is returned.
  * Otherwise, a JPEG image will be output, containing the legend image.
  *
  */

function _config_legend() {
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

function _headers_legend() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'json';
	switch($_REQUEST['format'] ) {
		case "json":	
		case "ajax":
			#header('Content-Type: application/json');
			break;
		case "xml":
			#header('Content-Type: text/xml');
			break;
	}	
}

/**
  */


#function _dispatch_generatelegend($request, $world, $user, $template, $project, $embedded, $permission) {
function _dispatch_legend($template, $args) {
	
	$user = SimpleSession::Get()->GetUser();
	/* @var $world World */
	$world = System::Get();
	
	
	/* @var $wapi WAPI */
	$wapi = $world->wapi;
	$ini = System::GetIni();

	$project = $wapi->RequireProject(RequestUtil::Get('project'));
	
	
	$layer = $wapi->RequireALayer();
	$mapper = $world->getMapper();
	$defaultProj4 = $world->projections->defaultProj4;
	$projector = new Projector_MapScript();
	$mapper->init(true, $projector->mapObj);
	$mapper->extent = array(-180,-90,180,90);
	$mapper->addLayer($layer,1.0,0);
	$mapper->quantize = true;

	
	$mapper->map  = $mapper->_generate_mapfile(true);
	$width = ParamUtil::Get($args,'width',60);
	$height = ParamUtil::Get($args,'height',21);
	
	
	$template->assign('layer',$layer);
	$info = array();
	$entries = $layer->colorscheme->getAllEntries();
	$ctr = 0;
	foreach ($mapper->map->getlayersdrawingorder() as $i) {
	         
			$layer    = $mapper->map->getLayer($i);
			
			for($i=0; $i<$layer->numclasses; $i++) {
			    
				$icon = sprintf("%s.png",  md5(microtime().mt_rand()) );
				$iconPath =  sprintf("%s/%s", $ini->tempdir,$icon);
				
				
				$class = $layer->GetClass($i);
			     
				$iconImg = $class->createLegendIcon($width,$height);
				
				$iconImg->saveImage($iconPath);
				
				//$icon = str_replace("/maps/","./",$icon);
				$iconURL = "./?do=wapi.asset.get&asset=$icon&format=png&token=".SimpleSession::Get()->GetID();
				$infoEntry =  array('icon'=>$iconURL,'class'=>$entries[$i],'class_name'=>$class->name);
				$info[] = $infoEntry;
		          $ctr++;	
			}	
	}
	
	$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'xml';
	if($format=='xml') {
		$template->assign('info',$info);
		$template->display('wapi/layer/legend.tpl');
	} else {
		$json = array('legend'=>array());
		foreach($info as $entry) {
			$item = array();
			$item['icon'] = $entry['icon'];
			$class = $entry['class'];
			$item['label'] = $entry->description;
			$item['tooltip'] = $class->criteria1." ".$class->criteria2." ".$class->criteria3;
			$item['classname'] = $entry['class_name'];
			$json['legend'][] = $item;
		}
		echo json_encode($json);
	}

}?>
