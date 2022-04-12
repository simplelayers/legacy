<?php
/**
  * Administration: Delete the specified user, then go back to the listing.
  * @package Dispatchers
  */
/**
  */
function _config_deleteuser() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_deleteuser($template, $args) {
$world = $args['world'];

// delete, unless they don't exist or are an admin
$p = $world->getPersonById($_REQUEST['id']);
if (!$p or $p->admin) return print redirect('admin.userlist');
$p->delete('Deleted by administrator.');

// done
print javascriptalert('User account deleted.');
print redirect('admin.userlist');

}?>
