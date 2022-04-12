<?php
/**
 * The form for adding storage to your account; select the storage amount, enter credit card info, etc.
 * @package Dispatchers
 */
/**
  */
 function _config_addstorage1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_addstorage1($template, $args) {
$world = $args['world'];
$user = $args['user'];

// days < 0 should never ever happen, but handle it just in case, because a negative days
// would give very goofy results
$days = $user->daysUntilExpiration();
if ($days < 0) return print redirect('account.addtime1');


// make up the list of options for adding storage
global $STORAGEUPGRADES;
$pergb = $world->config['storagepergb'];
$options = array();
foreach ($STORAGEUPGRADES as $gb=>$description) {
   $price = $user->priceStorageUpgrade($gb);
   $peryear = $pergb * $gb;
   $description = sprintf("%s for \$%s (\$%.2f per year)", $description, $price, $peryear );
   $options[$gb] = $description;
}

// and the template
$template->assign('options',$options);
$template->display('account/addstorage1.tpl');

}?>
