<?php
/**
  * Administration: Process the adminadduser1 form, create the person, send them to the editing page.
  * @package Dispatchers
  */
use enums\AccountTypes;
/**
  */
function _config_adduser2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_adduser2($template, $args) {
$world = $args['world'];

// check for any problems
$error = false;
if (!preg_match('/^[a-z_0-9]{1,50}$/',$_REQUEST['account_username'])) {
   $error = 'The username you chose was invalid. Please choose another username.'; }
if (!$_REQUEST['account_password']) {
   $error = 'You need to supply a password for the account.'; }
if ($_REQUEST['account_username'] == WORLD_NAME) {
   $error = 'Users cannot have the same name as the World. Please pick another.'; }
if ($world->getPersonByUsername($_REQUEST['account_username'])) {
   $error = 'That username is already taken. Please pick another.'; }
if ($error) {
   print javascriptalert($error);
   return $template->display('admin/adduser1.tpl');
}

// print the busy image up here, to keep the user occupied during creation
busy_image('Creating the user account. This takes a while.');

// do it, and go to their editing view
$person = $world->createPerson($_REQUEST['account_username'],$_REQUEST['account_password'],AccountTypes::MIN,'Created by administrator.');
$person->addyears(1);
print redirect("admin.edituser1&id={$person->id}");
}?>
