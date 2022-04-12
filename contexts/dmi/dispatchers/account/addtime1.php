<?php
/**
 * The form to extend your account's expiration; get credit card info, show options for adding years, ...
 * @package Dispatchers
 */
/**
  */
function _config_addtime1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_addtime1($template, $args) {
$world = $args['world'];
$user = $args['user'];

// what's the rice per year for basic service at their level? This isn't used for calculations,
// merely to determine whether it's free
$basecost = $world->config['accountprice_'.$user->accounttype];
$template->assign('free',$basecost ? 0 : 1);

// make up the list of options for adding time
$options = array();
for ($years=1; $years<=10; $years++) {
   $options[$years] = sprintf("Add %d years for \$%.2f", $years, $user->priceAddTime($years) );
}
$template->assign('options',$options);

// and to the template
$template->display('account/addtime1.tpl');

}?>
