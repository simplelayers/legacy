<?php
use subnav\OrganizationSubnav;
use auth\Context;
use model\Permissions;
use utils\PageUtil;
use utils\ParamUtil;
function _config_info() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_info($template, $args, $org, $pageArgs) {
	$pageArgs['pageSubnav'] = 'org';
	
	
	$user = $args ['user'];
	$world = $args ['world'];
	$session = SimpleSession::Get ();
	$sessionUser = $session->GetUser ();
	
	$org = !is_null(ParamUtil::Get($args,'orgId')) ? Organization::GetOrg($args['orgId']) : $org ;
	$pageArgs['pageTitle'] = $org->name.' - Details';

	
	if (! $org) {
		print javascriptalert ( 'That organization was not found, or you are not a member.' );
		return print redirect ( 'organization.info' );
	}
	
	$context = Context::Get ();
	$pageOptions = array (
			'plans_create' => Permissions::HasPerm ( $pageArgs ['permissions'], ':SysAdmin:Plans:', Permissions::CREATE ), 
			'plans_edit' => Permissions::HasPerm ( $pageArgs ['permissions'], ':SysAdmin:Plans:', Permissions::EDIT )
	);
	$template->assign ( 'pageOptions', $pageOptions );
	$profileVisible = $user->canBeSeenById ( $sessionUser->id );
	$template->assign ( 'profileVisible', $profileVisible );
    	$template->assign('disclaimerURL',BASEURL.'wapi/organization/disclaimer/orgId:'.$org->id.'/action:get');
    	$template->assign('disclaimer',base64_decode($org->disclaimer));
	$template->assign ( 'org', $org );
	$template->assign ( 'taglinks', activate_tags ( $org->tags, './?do=organization.list&tag=' ) );
	$subnav = new OrganizationSubnav ();
	$subnav->makeDefault ( $user, '', $org );
	$template->assign ( 'subnav', $subnav->fetch () );
	$template->assign ( 'selector', 'true' );
	PageUtil::SetPageArgs($pageArgs, $template);
	$template->display ( 'organization/info.tpl' );
}
?>
