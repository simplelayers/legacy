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
function _config_iframe1()
{
  $config = array();
  // Start config
  // Stop config
  return $config;
}

function getLayerInfo() {}

function _dispatch_import_arcgis($template, $args, $org, $pageArgs)
{
  $user = $args['user'];
  $template->assign('appsPath', \APPS);
  $wapi = System::GetWapi();
  $ini = System::GetIni();

  $template->assign('worldurl', WEBROOT); //$world->config['url']);

  $pageArgs['pageSubnav'] = 'data';
  $pageArgs['pageTitle'] = 'Layer - Import ArcGIS Service Layer ';

  $pageArgs['isArcGIS']  = "true";
  PageUtil::SetPageArgs($pageArgs, $template);
  PageUtil::MixinLayerArgs($template);
  PageUtil::SetPageArgs($pageArgs, $template);
  
  $template->display('layer/import_arcgis.tpl');
}
