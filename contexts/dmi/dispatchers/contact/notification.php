<?php
function _config_notification() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_notification($template, $args) {
$user = $args["user"];
$notify = $user->getNotification($_REQUEST["id"]);
$user->clearNotifications($_REQUEST["id"]);
if(isset($notify["subject_redirect"])){
	header("Location: ".$notify["subject_redirect"]);
}else{
	header("Location: ./?do=project.list");
}
}?>
