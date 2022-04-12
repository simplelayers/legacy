<?php
/**
 * The Signup page -- the form.
 * @package Dispatchers
 */
use enums\AccountTypes;

function _config_signup1() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_signup1($template, $args) {
$world = $args['world'];

// generate and assign a CAPTCHA image
$template->assign('captchaimageurl',$world->generateCaptcha());

// assign some cosmetic elements: the contact info, prices for account types, ...
$template->assign('contact_email',$world->config['admin_email']);
$template->assign('signup_header_message',$world->config['signup_header_message']);
$template->assign('signup_discount_message',$world->config['signup_discount_message']);
global $ACCOUNTTYPES;
$accountprices = array();
foreach (array_keys($ACCOUNTTYPES) as $level) {
   $price = $world->config['accountprice_'.$level];
   $price = (float) $price ? "\$$price / year" : 'Free';
   $accountprices[$level] = $price;
}
$template->assign('accountprices',$accountprices);

// if they were at the signup page but got sent back here (e.g. account already in use)
// then load their info from that previous page into the template, and remove it from the session
foreach ($_SESSION as $k=>$v) if (substr($k,0,7)=='signup_') { $_REQUEST[$k]=$v; unset($_SESSION[$k]); }
if (!@$_REQUEST['signup_accounttype']) $_REQUEST['signup_accounttype'] = AccountTypes::MIN;

/// and render it
$template->display('demo/signup1.tpl');
}?>