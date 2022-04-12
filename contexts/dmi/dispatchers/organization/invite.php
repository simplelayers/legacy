<?php
use subnav\OrganizationSubnav;
use utils\ParamUtil;
function _config_invite() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_invite($template, $args) {
	$session = SimpleSession::Get ();
	$user = $args ['user'];
	$world = $args ['world'];
	
	$org = ParamUtil::Get($session,'orgId',ParamUtil::Get($_REQUEST,'id',ParamUtil::Get($_REQUEST,'orgId')));
	$org = $world->getOrganizationById( $org );
	
	if (! $org) {
		print javascriptalert ( 'That organization was not found, or is unlisted.' );
		return print redirect ( 'organization.list' );
	}
	if ($org->owner->id != $args ['user']->id) {
		print javascriptalert ( 'You are not the owner of this organization.' );
		return print redirect ( 'organization.info&id=' . $org->id );
	}
	$template->assign ( 'org', $org );
	$template->assign ( 'invites', $org->getInvites () );
	if (! isset ( $_REQUEST ["action"] )) {
		$subnav = new OrganizationSubnav ();
		$subnav->makeDefault ( $user, '', $org );
		$template->assign ( 'subnav', $subnav->fetch () );
		$template->assign ( 'selector', 'true' );
		
		$template->display ( 'organization/invite.tpl' );
	} else {
		if ($_REQUEST ["action"] == "add")
			$org->newInvite ( $_REQUEST ["seat"] );
		if ($_REQUEST ["action"] == "delete")
			$org->deleteInvite ( $_REQUEST ["row"] );
		if ($_REQUEST ["action"] == "email")
			$org->emailInvite ( $_REQUEST ["row"], $_REQUEST ["email"] );
		return print redirect ( 'organization.invite&id=' . $org->id );
	}
}
?>