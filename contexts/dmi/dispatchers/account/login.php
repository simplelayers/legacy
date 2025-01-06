<?php
use auth\Creds;
use auth\Context;
use utils\PageUtil;
use utils\ParamUtil;
/**
 * Print the login screen.
 *
 * @package Dispatchers
 */
/**
 */
function _config_login() {
	$config = Array ();
	// Start config
	#$config ["authUser"] = 0;
	$config ["header"] = true;
	$config ['css_url'] = "login.css";
	// Stop config
	return $config;
}

function _dispatch_login($template, $args) {
	
	$world = $args ['world'];
	$creds = Creds::GetFromRequest();
	$context = Context::Get($creds);
	
	if($context->authState == SimpleSession::STATE_SESS_OK) {
	  $goTo = ParamUtil::Get($args,'go_to',"");
	
	  if($goTo=="") $goTo = $context->GetStart(); 
	    print redirect($goTo);
		exit;
	}
	
	
	$session = SimpleSession::Get();
	$state = RequestUtil::Get('state','normal');

	$context->SetLoginMessages($state, $template);
	$args = PageUtil::GetPageArgs($template);
	
	// fetch the contact info for printing on the page
	$template->assign ( 'contact_name', $world->config ['admin_name'] );
	$template->assign ( 'contact_email', $world->config ['admin_email'] );
	
	$template->assign ( 'username', $creds->username);
	$template->assign ( 'user', null );
	$template->assign ( 'login', BASEURL.'account/login' );
	$template->assign('nav_area_class','hidden');
	#$template->assign( 'return_to',$return_to);
	// is there an alternate login page URL we should be using?
	#$template->assign ( 'loginurl', 'account.$world->config ['alternateloginpage'] );
	// template->assign('loggedIn',FALSE);
	// and off to the templateZ
	$template->display ( 'account/login.tpl' );
}
?>
