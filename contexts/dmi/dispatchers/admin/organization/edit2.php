<?php
/**
 * The form for changing your password.
 * @package Dispatchers
 */
use enums\AccountTypes;
/**
  */
function _config_edit2() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_edit2($template, $args) {
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
	$_REQUEST["owner"] = $person->id;
}else{
	$_REQUEST["owner"] = $_REQUEST["contact"];
}

$org = $args['world']->getOrganizationById($_REQUEST["id"]);
$allfields = $org->getAllFieldsAsArray();
unset($allfields["id"]);
$updates = Array();
foreach($allfields as $key => $value){
	if(isset($_REQUEST[$key])){
		if((string)$_REQUEST[$key] != (string)$value){
			$updates[$key] = $_REQUEST[$key];
		}
	}
}
if(!empty($updates)) $org->Update($updates);
print redirect('admin.organization.edit1&id='.$org->id);
}?>
