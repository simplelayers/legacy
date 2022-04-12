<?php
/**
  * Administration: process the adminconfigsignups1 form.
  * @package Dispatchers
  */
/**
  */
function _config_configsignups2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_configsignups2($template, $args) {
$world = $args['world'];

// go through the tags and set them from the values -- simple
$tags = array('signup_thankyoumessage','signup_header_message','signup_discount_message');
foreach ($tags as $key) $world->setConfig($key,$_REQUEST[$key]);

// done
print javascriptalert('Changes saved.');
return print redirect('admin.configsignups1');

}?>
