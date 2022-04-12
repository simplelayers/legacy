<?php
/**
 * A list of the vector layer's columns/attributes, and widgets for adding/deleting columns.
 * @package Dispatchers
 */
/**
  */
function _config_listcolumns() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_listcolumns() {
	header('Content-type: text/xml');
}
function _dispatch_listcolumns($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = Layer::GetLayer($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::READ) {
   die(print 'You do not have permission to view that Layer.');
}
$template->assign('layer',$layer);

// get the list of columns/attributes that already exist in the layer,
// and the column types that can be created
global $DATATYPES;
$template->assign('columntypes', $DATATYPES );
$columns = $layer->getAttributes();

$template->assign('columns',$columns);

// and the template, as always
header('Content-type: text/xml');
$template->display('wapi/listcolumns.tpl');

}?>
