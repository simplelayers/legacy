<?php
use utils\PageUtil;
use utils\ParamUtil;
use model\SeatAssignments;
use model\Plans;
use model\reporting\Reports;

/**
 * Print a list of your data layers, with links to edit them, set permissions, etc.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_list()
{
    $config = Array();
    // Start config
    $config["sendWorld"] = false;
    // Stop config
    return $config;
}

function _dispatch_list($template, $args, $org, $pageArgs)
{
    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['pageTitle'] = 'Data - Layer List';
    
    PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinLayerArgs($template);
    $pageArgs = PageUtil::MixinPlanArgs($template);
    
    $user = SimpleSession::Get()->GetUser();
    
    $limitState = $pageArgs['plan_limits']['layerLimitState'];
    $numLayers = $pageArgs['plan_limits']['layerCount'];
    $layerLimit = $pageArgs['plan_limits']['layerLimit'];
    
    $message = 'Using '.$numLayers.' of '.$layerLimit;
    if($limitState == 'limitstate_limited') {
        $message =  ($numLayers == $layerLimit ) ? "Using full allotment of $layerLimit layers" : "Over allotment of $layerLimit layers.";
    }
    
    $template->assign('limitState',$limitState);
    $template->assign('limitMessage',$message);
    
	$template->assign('layerTypeEnum', LayerTypes::GetEnum()->ToJSObj('layerTypes'));
    $template->assign('geomTypeEnum', GeomTypes::GetEnum()->ToJSObj('geomTypes'));
    
    if (! is_null(ParamUtil::Get($args, 'groupId'))) {
        $template->assign('default', 4); // group
        $template->assign('groupId', ParamUtil::Get($args, 'groupId'));
    }
    
    $template->assign('isAdmin', $pageArgs['pageActor'] == 'admin');
    $template->assign("select", false);
    $template->assign("dataSelectorString", "");
    
    $template->display('layer/list.tpl');
}
?>