<?php
use utils\AccountUtil;
/**
 * The "Forgot Your Password"s subsystem -- after entering their username, send them an email with the link to step 3.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_forgotpassword2() {
	$config = Array ();
	// Start config
	$config ["sendUser"] = false;
	$config ["authUser"] = 0;
	// Stop config
	return $config;
}
function _dispatch_forgotpassword2($template, $args) {
	$username = RequestUtil::Get ( 'username', null );

		// fetch the person and look for problems
	$user = System::Get ()->getPersonByUsername ( RequestUtil::Get ( 'username' ) );
	
	$error = false;
	if (! $_REQUEST ['username'])
		$error = 'Please specify your username.';
	if (! $user)
		$error = 'Your account could not be found. Please try again.';
	if (! $user->email)
		$error = 'Your account does not have an email address listed.\\nPlease contact us to change your password.';
	if ($error) {
		print javascriptalert ( $error );
		return print redirect ( 'account.forgotpassword1' );
	}
	
	AccountUtil::ForgotPassword ( RequestUtil::Get ( 'username' ) );
	
	print javascriptalert ( 'An email has been sent.\\nWhen you receive the email, follow the link in it to continue changing your password.' );
	print redirect ( 'mainmenu' );
}
?>
