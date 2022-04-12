<?php
/**
  * Administration: process the adminconfigidentification1 form.
  * @package Dispatchers
  */
/**
  */
function _config_configidentification2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_configidentification2($template, $args) {
$world = $args['world'];

// go through the tags and set them from the values -- simple
$tags = array('title','admin_name','admin_email','creditcardkey','alternateloginpage');
foreach ($tags as $key) $world->setConfig($key,$_REQUEST[$key]);

// done
print javascriptalert('Changes saved.');
return print redirect('admin.configidentification1');

}?>