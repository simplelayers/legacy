<?php
/**
 * The form for changing your password.
 * @package Dispatchers
 */
use enums\AccountTypes;
/**
  */
function _config_new2() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_new2($template, $args) {
$error = false;
if($_REQUEST["contact"] === "new"){
	if (!preg_match('/^[a-z_0-9]{1,50}$/',$_REQUEST['account_username'])) {
	$error = 'The username you chose was invalid. Please choose another username.'; }
	if (!$_REQUEST['account_password']) {
	$error = 'You need to supply a password for the account.'; }
	if ($_REQUEST['account_username'] == WORLD_NAME) {
	$error = 'Users cannot have the same name as the World. Please pick another.'; }
	if ($args['world']->getPersonByUsername($_REQUEST['account_username'])) {
	$error = 'That username is already taken. Please pick another.'; }
}else{
	$_REQUEST["contact"] = (int)$_REQUEST["contact"];
}
if ($error) {
   print javascriptalert($error);
   return $template->display('admin/organization/new1.tpl');
}
if($_REQUEST["contact"] === "new"){
	$person = $args['world']->createPerson($_REQUEST['account_username'],$_REQUEST['account_password'],AccountTypes::PLATINUM,'Created by administrator.');
	$person->addyears(1);
	$_REQUEST["contact"] = $person->id;
}
$org = $args['world']->createOrganization($_REQUEST["contact"], $_REQUEST["name"], strtolower($_REQUEST["short"]));
$group = $args['world']->createGroup($_REQUEST["contact"],$_REQUEST["name"],'',$org->id);

print redirect('admin.organization.edit1&id='.$org->id);
}?>
