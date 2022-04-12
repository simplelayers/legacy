<?php
/**
 * Delete one of your own projects.
 *
 * Parameters:
 *
 * id -- The project-ID to delete, e.g. 1234. Note that the user calling must own the project.
 *
 * Return:
 *
 * XML representing the status of the request.
 * {@example docs/examples/wapi_ok.txt}
 * {@example docs/examples/wapi_no.txt}
 *
 * @package WebAPI
 */
/**
  * @ignore
  */
function _config_deleteproject() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_deleteproject() {
	header('Content-type: text/xml');
}
function _dispatch_deleteproject($template, $args) {
$world = $args['world'];
$user = $args['user'];

// fetch the project; they must own it, which makes permission checking superfluous
$project = $user->getProjectById($_REQUEST['id']);
if (!$project) {
   $template->assign('ok','no');
   $template->assign('message','No such Map.');
   return $template->display('wapi/okno.tpl');
}

$project->delete();
$template->assign('ok','yes');
$template->assign('message','Map deleted.');
$template->display('wapi/okno.tpl');

} ?>
