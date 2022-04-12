<?php
function _config_join() {
	$config = Array();
	// Start config
	$config['css_url'] = "login.css";
	$config["authUser"] = 0; 
	// Stop config
	return $config;
}

function _dispatch_join($template, $args){
	$user = $args['user'];
	$world = $args['world'];
	
	$invite = $world->db->Execute("SELECT * FROM organizations_invites WHERE code = ?", $_REQUEST["code"]);
	$org = $world->getOrganizationById($invite->fields["org_id"]);
	if (!$org) {
	   print javascriptalert('That organization was not found, or is unlisted.');
	   return print redirect('organization.list');
	}
	if(isset($_REQUEST["join"])){
		$org->addMember($user->id, $invite->fields["seat"]);
		$org->deleteInvite($invite->fields["id"]);
		return print redirect('organization.list');
	}elseif(isset($_REQUEST["create"])){
		$error = "";
		if (!preg_match('/^[a-z_0-9]{1,50}$/',$_REQUEST['account_username']))
		   $error .= 'The username you chose was invalid. Please choose another username.<br/>';
		if (!$_REQUEST['account_password'])
		   $error .= 'You need to supply a password for the account.<br/>';
		if ($_REQUEST['account_username'] == WORLD_NAME)
		   $error .= 'That username is already taken. Please pick another.<br/>';
		if ($world->getPersonByUsername($_REQUEST['account_username']))
		   $error .= 'That username is already taken. Please pick another.<br/>';
		if ($error != "") {
		   $template->assign('error',$error);
		}else{
			$person = $world->createPerson($_REQUEST['account_username'],$_REQUEST['account_password'],'Created by '.$org->name.' invite.');
			$person->addyears(1);
			$person->email = $_REQUEST['account_email'];
			$org->addMember($person->id, $invite->fields["seat"]);
			$org->deleteInvite($invite->fields["id"]);
			return print redirect('organization.list');
		}
	}
	$template->assign('org',$org);
	$template->assign('email',($invite->fields["email"] ? $invite->fields["email"] : ""));
	$template->assign('code',$_REQUEST["code"]);
	$template->display('organization/join.tpl');
}?>