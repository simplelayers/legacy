<?php
/**
 * Process the upgrade1 form, to run the credit card and upgrade the account.
 * @package Dispatchers
 */
use enums\AccountTypes;
/**
  */
function _config_upgrade2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_upgrade2($template, $args) {
$world = $args['world'];
$user = $args['user'];

global $ACCOUNTTYPES;

if (!$ACCOUNTTYPES[$_REQUEST['newlevel']]) {
   print javascriptalert('Please specify the account type you want to upgrade to.');
   return print redirect('account.upgrade1');
}
if ($_REQUEST['newlevel'] <= $user->accountype) {
   print javascriptalert('You cannot downgrade your account.');
   return print redirect('account.upgrade1');
}
if ($user->accountype == AccountTypes::MAX) {
   print javascriptalert('Your account is already at the highest level.');
   return print redirect('account.accounttype');
}
$days = $user->daysUntilExpiration();
if ($days < 0) {
   print javascriptalert('Your account is expired.\\nYou will need to add extend your membership before upgrading.');
   return print redirect('account.addtime1');
}


// verify that everything's in order on this end
$error = false;
if (!$_REQUEST['newlevel'])       { $error = 'You need to select your new account level.'; }
if (!$_REQUEST['cc_cardholder'])  { $error = 'You need to specify the cardholder name.'; }
if (!$_REQUEST['cc_number'])      { $error = 'You need to enter your card number.'; }
if (!$_REQUEST['Date_Month'])     { $error = 'You need to specify the expiration date for your card.'; }
if (!$_REQUEST['Date_Year'])      { $error = 'You need to specify the expiration date for your card.'; }
if ($error) { print javascriptalert($error); return print redirect('account.upgrade1'); }

// calculate the price of the upgrade
$price = $user->priceUpgradeAccount($_REQUEST['newlevel']);
$description = "\$$price Account {$user->username} upgrading to {$ACCOUNTTYPES[$_REQUEST['newlevel']]}";

// run the card
/*$result = run_creditcard($world->config['creditcardkey'],$price,$description,$_REQUEST['cc_cardholder'],$_REQUEST['cc_number'],$_REQUEST['Date_Month'],$_REQUEST['Date_Year']);
if (!$result) {
   print javascriptalert('Your credit card did not go through. Please try again.');
   return print redirect('account.upgrade1');
}*/

// do it
$user->accounttype = $_REQUEST['newlevel'];
$world->logAccountActivity($user->username, 'upgrade', $description);

// done!
print javascriptalert('Your account has been upgraded.');
print redirect('account.type');

}?>
