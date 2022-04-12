<?php
/**
 * The "Forgot Your Password"s subsystem -- verify their request, and print the prompt for their new password.
 * @package Dispatchers
 */
/**
  */
function _config_forgotpassword3() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	$config['css_url'] = "login.css";
	// Stop config
	return $config;
}

function _dispatch_forgotpassword3($template, $args) {
	
	
$world = System::Get();
$userId = RequestUtil::Get('id');

$user=$world->getPersonById($userId);

$hash = RequestUtil::Get('hash',null);
$userHash = $user->resetpassword;

if (!$user) {
	print javascriptalert('Your account could not be found. Sorry.');
	#return print redirect('account.forgotpassword1');
}


if (is_null($hash) || !$userHash) {
	print javascriptalert('No hash found.');
	#return print redirect('account.forgotpassword1');
}


if($userHash !== $hash){
	print javascriptalert('Reset request may be outdated.');
	return print redirect('account.forgotpassword1');
}
// looking good, now hand off to the password prompt
$template->assign('hash',$_REQUEST['hash']);
$template->assign('uid',$user->id);

$template->display('account/forgotpassword3.tpl');


}?>