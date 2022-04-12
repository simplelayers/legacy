<?php
/**
 * Process the password1 form, to set your account password.
 * @package Dispatchers
 */
/**
  */
function _config_password2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_password2($template, $args) {
$user = $args['user'];
$world = $args['world'];

if (@$_REQUEST['newpassword1'] != @$_REQUEST['newpassword2']) {
   print javascriptalert('The new passwords you entered did not match.\\nPlease try again.');
   return print redirect('account.password1');
}

if (!$world->verifyPassword($user->username,@$_REQUEST['oldpassword'])) {
   print javascriptalert('The current (old) password you entered was incorrect.\\nPlease try again.');
   return print redirect('account.password1');
}

// if we got this far, the passwords match and so does the old one. so go for it
$user->password = $_REQUEST['newpassword2'];
print javascriptalert('Your password has been changed.');
print redirect('account.type');

}?>
