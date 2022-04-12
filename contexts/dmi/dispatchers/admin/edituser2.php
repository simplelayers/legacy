<?php
/**
  * Administration: Process the adminedituser1 form, saving the changes to the user.
  * @package Dispatchers
  */
/**
  */
function _config_edituser2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_edituser2($template, $args) {
$world = $args['world'];

// if an expiration date was generated, convert it into the proper yyyy-mm-dd format
if ($_REQUEST['expiration_Month'] and $_REQUEST['expiration_Day'] and $_REQUEST['expiration_Year']) {
   $_REQUEST['account_expirationdate'] = sprintf('%04d-%02d-%02d', $_REQUEST['expiration_Year'], $_REQUEST['expiration_Month'], $_REQUEST['expiration_Day'] );
}
else {
   $_REQUEST['account_expirationdate'] = null;
}

// save their changes
$p = $world->getPersonById($_REQUEST['id']);
$p->accounttype    = $_REQUEST['account_accounttype'];
$p->realname       = $_REQUEST['account_realname'];
$p->email          = $_REQUEST['account_email'];
$p->description    = $_REQUEST['account_description'];
$p->comment1       = $_REQUEST['account_comment1'];
$p->comment2       = $_REQUEST['account_comment2'];
$p->expirationdate = $_REQUEST['account_expirationdate'];

if ($_REQUEST['account_password']) $p->password = $_REQUEST['account_password'];

print redirect('admin.userlist');
}?>