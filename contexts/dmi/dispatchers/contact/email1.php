<?php
use utils\ParamUtil;
use utils\PageUtil;
/**
 * The form to email a person.
 * @package Dispatchers
 */
/**
 */
function _config_email1() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_email1($template, $args,$org,$pageArgs) {
    $contactId = ParamUtil::Get($args,'contactId');
    $person = System::Get()->getPersonById ( $contactId);
    $pageArgs['pageSubnav'] = 'community';
    $pageArgs['contactId'] = $contactId;
    $pageArgs['pageTitle'] = "Send an email to ".$person->realname;
    $pageArgs['contactName'] = $person->realname;
	$world = System::Get();
	$user = SimpleSession::Get()->GetUser();
	
	
	// does the target person even exist?
	if (! $person) {
		print javascriptalert ( 'Unable to locate that user.' );
		return print redirect ( 'mainmenu' );
	}
	$template->assign ( 'target', $person );
	
	// can we contact them?
	if (! $user->canSeeUserById( $contactId )) {
		print javascriptalert ( 'That user can only be contacted by people on their Friends list.' );
		return print redirect ( 'mainmenu' );
	}
	PageUtil::SetPageArgs($pageArgs, $template);
	$template->assign ( 'user', $user );
	$template->display ( 'contact/email1.tpl' );
}
?>
