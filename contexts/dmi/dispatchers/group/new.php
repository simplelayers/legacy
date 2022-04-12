<?php
/**
 * Process the form from socialgroupadd1
 * @package Dispatchers
 */
/**
  */
function _config_new() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_new($template, $args) {
$sys = System::Get();
$user = SimpleSession::Get()->GetUser();

// create the new group
$group = $sys->createGroup($user->id,'','');

// all done
print redirect("group.info&groupId={$group->id}");
}?>
