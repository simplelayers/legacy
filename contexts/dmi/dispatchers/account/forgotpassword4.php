<?php
/**
 * The "Forgot Your Password"s subsystem -- Having prompted for their password, go ahead and set it.
 * @package Dispatchers
 */
/**
  */
function _config_forgotpassword4() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_forgotpassword4($template, $args) {
$world = System::Get();
unset($_REQUEST['world']);
// check for problems with the password
if (!isset($_REQUEST['password1']) or !isset($_REQUEST['password2']) or !$_REQUEST['password1'] or $_REQUEST['password1'] != $_REQUEST['password2'] ){
   print javascriptalert('The passwords you entered did not match.\\nPlease enter your choice of a new password into both boxes and try again.');
   return print redirect('account.forgotpassword3&id='.$_REQUEST['uid'].'&hash='.$_REQUEST['hash']);
}

$p = $world->getPersonById((int)$_REQUEST['uid']);
$hash = $_REQUEST['hash'];
if (!$p) {
	print javascriptalert('Your account could not be found. Sorry.');
	return print redirect('account.forgotpassword1');
}
if (!$hash || !$p->resetpassword) {
	print javascriptalert('No hash found.');
	return print redirect('account.forgotpassword1');
}
if($p->resetpassword !== $hash){
	print javascriptalert('Wrong hash. It may be old.');
	return print redirect('account.forgotpassword1');
}
// go ahead and change the password, then delete the hash
$p->password = $_REQUEST['password1'];
$p->resetpassword = null;

// done
print javascriptalert('Your password has been changed.\\nYou may now login.');
print redirect('login');

}?>
