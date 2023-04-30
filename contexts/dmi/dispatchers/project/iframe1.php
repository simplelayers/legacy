<?php

use utils\PageUtil;
use auth\Context;
System::RequireColorPicker();
/**
 * The HTML page for a utility to generate HTML iframe paragraphs.
 * @package Dispatchers
 */
/**
  */
function _config_iframe1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function getLayerInfo() {

}

function _dispatch_iframe1($template, $args,$org,$pageArgs) {
    $user = $args['user'];
    $world = $args['world'];
    $template->assign('appsPath',\APPS);
    $ini = System::GetIni();
    // are they allowed to be doing this at all?
    /*if ($user->accounttype < AccountTypes::GOLD) {
       print javascriptalert('You must upgrade your account in order to embed your Map in other websites.');
       return print redirect("project.edit1&id={$_REQUEST['id']}");
    }*/
    if ($user->community) {
    	print javascriptalert('You cannot share maps with a community account.');
    	return print redirect('layer.list');
    }
    
    // load the project and verify their access
    $project = $world->getProjectById(RequestUtil::Get('id',NULL));
    
    /*if (!$project or $project->owner->id != $user->id) {
       print javascriptalert('Maps can only be embeded by their owner.');
       return print redirect('project.list');
    }*/
    if( !$project->allowlpa and ($project->getPermissionById('public') < AccessLevels::READ) ) {
    	print javascriptalert("The map must available with Limited Public Access (LPA). Set the public permissions to \"Public\" or \"Unlisted\"");
    	return print redirect("project.permissions&id=".$project->id);
    }
    $projectLayers = $project->getSearchableLayers();
    $projectLayerFields = array();
    
    foreach($projectLayers as $projectLayer) {
    	
    	$layer = $projectLayer->layer;
    	if($layer) {
    		$atts = array_keys($layer->getAttributes());
    		if(($key = array_search('gid', $atts)) !== false) {
    		    unset($atts[$key]);
    		}
    		$projectLayerFields[$layer->id] = $atts;
    		
    	}
    }
    $template->assign('projectLayerFields',json_encode($projectLayerFields));
    $template->assign('layerlist', $projectLayers );
    
    
    $template->assign('project',$project);
    
    
    /*// add the tool list and feature list to the template...
    global $VIEWERFEATURES;
    $template->assign('featurecodes',$VIEWERFEATURES);
    global $VIEWERTOOLS;
    $template->assign('toolcodes',$VIEWERTOOLS);*/
    
    // convert the VIEWERFEATURE_DEFAULT and VIEWERTOOL_DEFAULT sums into arrays of values
    $default_features = array();
    /*foreach ($VIEWERFEATURES as $i=>$n) if (VIEWERFEATURE_DEFAULT & $i) array_push($default_features,$i);
    $template->assign('featureselected',$default_features);
    $default_tools = array();
    foreach ($VIEWERTOOLS as $i=>$n) if (VIEWERTOOL_DEFAULT & $i) array_push($default_tools,$i);
    $template->assign('toolselected',$default_tools);
    */
    $template->assign('worldurl',WEBROOT);//$world->config['url']);
    
    $pageArgs['pageSubnav'] = 'maps';
    $pageArgs['pageTitle'] = 'Maps - Embed Map '.$project->name;
    
    $pageArgs['mapId'] = $project->id;
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    
    $bgColor = (isset($entry->fill_color) ? $entry->fill_color : 'trans');
    //Color Picker
    $template->assign('mapId',$project->id);
    $template->assign('colorpicker_background', color_picker('theform','backgroundColor','',$bgColor,true,'setTimeout("updateEmbedCode()", 100);') );
    // and draw it...
    
    $template->display('project/iframe1.tpl');

} ?>
