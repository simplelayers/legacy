<?php
/**
  * Administration: process the adminconfigquotas1 form.
  * @package Dispatchers
  */
/**
  */
function _config_configquotas2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	// Stop config
	return $config;
}

function _dispatch_configquotas2($template, $args) {
$world = $args['world'];

// coerce a few values
$_REQUEST['diskquota']    =   (int) $_REQUEST['diskquota'];
$_REQUEST['storagepergb'] = (float) $_REQUEST['storagepergb'];

// go through the tags and set them from the values -- simple
global $ACCOUNTTYPES;
$tags = array('diskquota','storagepergb');
foreach (array_keys($ACCOUNTTYPES) as $i) array_push($tags,"accountprice_$i");
foreach ($tags as $key) $world->setConfig($key,$_REQUEST[$key]);

// done
print javascriptalert('Changes saved.');
return print redirect('admin.configquotas1');
}?>