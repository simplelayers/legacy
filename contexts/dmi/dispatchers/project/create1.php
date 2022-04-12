<?php

use utils\PageUtil;
/**
 * The form to create a new project.
 * @package Dispatchers
 */
/**
  */
function _config_create1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_create1($template, $args,$org,$pageArgs) {
	$user = $args['user'];
	$pageArgs['pageTitle'] = 'Create a new map';
	$pageArgs['pageSubnav'] = 'maps';
	PageUtil::SetPageArgs($pageArgs, $template);
	PageUtil::MixinMapArgs($template);
	// are they allowed to create projects?
	/*if ($user->accounttype < AccountTypes::GOLD) {
	    print javascriptalert('To create Maps, you must have at least Gold level access.');
	    return print redirect('project.list');
	}*/
	$template->display('project/create1.tpl');
}?>
