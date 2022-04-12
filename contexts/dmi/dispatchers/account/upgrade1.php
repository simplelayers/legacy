<?php
/**
 * The form for upgrading your account.
 * @package Dispatchers
 */
use enums\AccountTypes;
/**
  */
function _config_upgrade1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_upgrade1($template, $args) {
$world = $args['world'];
$user = $args['user'];

if ($user->accountype == AccountTypes::MAX) {
   print javascriptalert('Your account is already at the highest level.');
   return print redirect('account.accounttype');
}

// days < 0 should never ever happen, but handle it just in case, because a negative days
// would give very goofy results
$days = $user->daysUntilExpiration();
if ($days < 0) return print redirect('account.addtime1');


global $ACCOUNTTYPES;
$options = array();
foreach ($ACCOUNTTYPES as $level=>$name) {
   if ($level <= $user->accounttype) continue;
   $price = $user->priceUpgradeAccount($level);
   $options[$level] = sprintf("Upgrade to %s for \$%s", $name, $price );
}

$template->assign('options',$options);
$template->assign('accounttype',$ACCOUNTTYPES[$user->accounttype]);
$template->assign('user', $user);
$template->display('account/upgrade1.tpl');

}?>