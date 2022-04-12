<?php
/**
 * The Signup page -- process the form.
 * @package Dispatchers
 */
/**
  */
function _config_signup2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_signup2($template, $args) {
$world = $args['world'];

global $ACCOUNTTYPES;

// make up the description for their CC bill and for our own records
$description = "New signup: {$_REQUEST['signup_username']} as level {$ACCOUNTTYPES[$_REQUEST['signup_accounttype']]}";
if ($_REQUEST['signup_referred']) $description .= ". Ref'd by: ".strip_tags($_REQUEST['signup_referred']);
if (@$_REQUEST['discount']) $description .= sprintf(" / Applying for \$%d discount.", $_REQUEST['discount'] );
$price = (float) $world->getAccountPrice($_REQUEST['signup_accounttype']);


///// go through a bunch of scenarios and try to find an error. then dump them back to the signup page
$error = false;
if (!$world->checkCaptcha(@$_REQUEST['captcha'])) {
   $error = 'Please enter the verification code in the image.'; }
if (!preg_match('/^[a-z_0-9]{1,50}$/',$_REQUEST['signup_username'])) {
   $error = 'The username you chose was invalid. Please choose another username.'; }
if ($_REQUEST['signup_username'] == WORLD_NAME) {
   $error = 'That username is invalid. Please pick another.'; }
if ($world->getPersonByUsername($_REQUEST['signup_username'])) {
   $error = 'That username is already taken. Please pick another.'; }
if (!$_REQUEST['signup_password']) {
   $error = 'You need to specify a password.'; }
if (!$ACCOUNTTYPES[@$_REQUEST['signup_accounttype']]) {
   $error = 'You need to select an account type.'; }
if ($price and !$_REQUEST['Date_Month']) {
   $error = 'You need to specify the expiration date for your card.'; }
if ($price and !$_REQUEST['Date_Year']) {
   $error = 'You need to specify the expiration date for your card.'; }
if ($price and !$_REQUEST['cc_number']) {
   $error = 'You need to enter a credit card number.'; }
if ($price and !$_REQUEST['cc_cardholder']) {
   $error = 'You need to specify the cardholder name.'; }
/*if ($price and !run_creditcard($world->config['creditcardkey'],$price,$description,$_REQUEST['cc_cardholder'],$_REQUEST['cc_number'],$_REQUEST['Date_Month'],$_REQUEST['Date_Year'])) {
   $error = 'Your credit card did not go through. Please try again.'; }*/
// if we hit an error, copy their signup info into a session store, then send them back to the signup page
// this is the only clean way to get them back to the signup page w/o duplicating code and causing problems
unset($_SESSION['captcha']);
if ($error) {
   foreach ($_REQUEST as $k=>$v) if (substr($k,0,7)=='signup_') $_SESSION[$k] = $v;
   print javascriptalert($error); return print redirect('demo.signup1');
}


///// excellent, no errors so we proceed

// print the busy image up here, to keep the user occupied during creation
busy_image('Creating your account. Please be patient.');

// create them and give them 1 year right off
$p = $world->createPerson($_REQUEST['signup_username'],$_REQUEST['signup_password'],$_REQUEST['signup_accounttype'],$description);
$p->addYears(1);
$p->email = $_REQUEST['signup_email'];

// send them an email thanking them
$email_subject = sprintf("[%s] Welcome", $world->config['title'] );
$recip = sprintf("%s <%s>", $_REQUEST['signup_username'], $_REQUEST['signup_email'] );
$email_sender  = sprintf("%s <%s>",$world->config['admin_name'],$world->config['admin_email']);
$email_message = $world->config['signup_thankyoumessage'];
mail($_REQUEST['signup_email'],$email_subject,$email_message,"To: $recip\r\nFrom: $email_sender");

// done
print javascriptalert('Your account has been created.\\nWelcome to the community.');
print redirect('login');
}?>