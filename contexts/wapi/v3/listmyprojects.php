<?php
/**
 * Fetch a list of one's own projects.
 *
 * Parameters:
 *
 * (none)
 *
 * Return:
 *
 * XML representing the list of data layers, or else an error.
 * {@example docs/examples/wapilistmyprojects.txt}
 * {@example docs/examples/wapi_error.txt}
 *
 * @package WebAPI
 */
/**
  * @ignore
  */
function _config_listmyprojects() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_listmyprojects() {
	header('Content-type: text/xml');
}
function _dispatch_listmyprojects($template, $args) {
$world = $args['world'];
$user = $args['user'];

$projects = $user->listProjects();
$template->assign('projects', $projects);

$template->display('wapi/listmyprojects.tpl');
} ?>
