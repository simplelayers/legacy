<?php
/**
  * Administration: Adjust a user's disk quota, either adding or subtracting disk space.
  * @package Dispatchers
  */
/**
  */
function _config_adjustdisk() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_adjustdisk($template, $args) {
$world = $args['world'];

// fetch their old quota
$p = $world->getPersonById($_REQUEST['id']);
$quota = $p->diskquota;

// figure out the adjustment. don't let the allocation go below 0
if ($_REQUEST['adjust1'] == 'add') $new = $quota + (int) $_REQUEST['adjust2'];
if ($_REQUEST['adjust1'] == 'subtract') $new = $quota - (int) $_REQUEST['adjust2'];
if ($new < 0) $new = 0;

// and save it
$p->diskquota = $new;

print redirect("admin.showusage&id={$p->id}");

}?>
