<?php

use utils\PageUtil;
use auth\Context;
use utils\ParamUtil;

LoadNS('auth\SandBoxKey');

use function auth\CreateSandboxKey;

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

function _dispatch_edit_arcgis($template, $args, $org, $pageArgs)
{
  $user = $args['user'];
  $template->assign('appsPath', \APPS);
  $wapi = System::GetWapi();
  $ini = System::GetIni();
  // are they allowed to be doing this at all?
  /*if ($user->accounttype < AccountTypes::GOLD) {
       print javascriptalert('You must upgrade your account in order to embed your Map in other websites.');
       return print redirect("project.edit1&id={$_REQUEST['id']}");
    }*/

  $layer = $wapi->RequireLayer();
  $template->assign('worldurl', WEBROOT); //$world->config['url']);

  $pageArgs['pageSubnav'] = 'data';
  $pageArgs['pageTitle'] = 'Layer - Import ArcGIS Service Layer ' . $layer->name;

  $pageArgs['layerId'] = $layer->id;
  $pageArgs['isArcGIS']  = "true";
  PageUtil::SetPageArgs($pageArgs, $template);
  PageUtil::MixinLayerArgs($template);
  $pageArgs = PageUtil::GetPageArgs($template);
  $pageArgs['layerId'] = $layer->id;
  $pageArgs['layer_info']= BASEURL . '?do=layer.edit1&id='.$layer->id;
  PageUtil::SetPageArgs($pageArgs, $template);
  $template->assign('layerId', $layer->id);
  $url = ParamUtil::Get($_SERVER, 'HTTP_REFERER', $_SERVER['SCRIPT_URI'].'?'.$_SERVER['QUERY_STRING']);
 
  $urlParts = explode('?', $url);
  $keyURL = array_shift($urlParts);
  $template->assign('appURL', $url);
  if ($url) {
    $pageKey = CreateSandboxKey($keyURL, SimpleSession::Get()->GetID());
    $template->assign('pageKey', $pageKey);
  }
  $template->display('layer/edit_arcgis.tpl');
}
