<?php
/**
 * Create a new Project, owned by you.
 *
 * Parameters:
 *
 * name -- The name for the new project.
 *
 * description -- A text description for the project. Optional.
 *
 * bbox -- The bounding box for the project when it first loads, e.g. -180,-180,180,180
 *         Note that the bbox must be square. This is optional, and defaults to -180,-180,-180,180
 *
 * private -- Boolean; 1 if the project has private status; 0 if the project does not (e.g. is public status)
 *            Optional; defaults to 1.
 *
 * allowlpa -- Boolean; 1 if the project should allow Limited Public Access (LPA) to non-logged-in people/browsers.
 *             Optional; defaults to 0.
 *
 * Returns:
 *
 * XML representing the OK/NO status of the request.
 * Note that the ok value, if it's OK, will be the ID# of the new Project, not simply the word "yes"
 * {@example docs/examples/wapi_ok.txt}
 * {@example docs/examples/wapi_no.txt}
 *
 * @package WebAPI
 */

/**
  * @ignore
  */
function _config_createproject() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_createproject() {
	header('Content-type: text/xml');
}
function _dispatch_wapicreateproject($template, $args) {
$world = $args['world'];
$user = $args['user'];

// are they allowed to create projects?
/*if ($user->accounttype < AccountTypes::GOLD) {
    $template->assign('ok','no');
    $template->assign('message',"Must have at least Gold level account to create projects.");
    $template->display('wapi/okno.tpl');
    return;
}*/

// create the project and set its permissions
$_REQUEST['name'] = $user->uniqueProjectName($_REQUEST['name']);
$project = $user->createProject($_REQUEST['name']);
$project->description = $_REQUEST['description'];
$project->bbox        = $_REQUEST['bbox'] ? $_REQUEST['bbox'] : '-180,-180,-180,180';
$project->private     = isset($_REQUEST['private']) ? $_REQUEST['private'] : 1;
$project->allowlpa    = isset($_REQUEST['allowlpa']) ? $_REQUEST['allowlpa'] : 0;

// acknowledge the creation of the new project
$template->assign('ok',$project->id);
$template->assign('message',"Map created with ID# {$project->id}.");
$template->display('wapi/okno.tpl');

} ?>
