<?php
require_once (dirname ( __FILE__ ) . "/../../classes/SearchCriteria.php");
require_once (dirname ( __FILE__ ) . "/../../classes/RequestUtil.class.php");

/**
 * Submit a SQL-style query against a vector data layer, returning a set of results.
 *
 * Parameters:
 *
 * layer -- The layer-ID to query, e.g. 1234.
 *
 * fields -- A comma-joined list of fields to retrieve, e.g. analytecode,result,qualifier
 *
 * where -- The SQL-style WHERE clause, e.g. address like '1234%'
 *
 * limit -- The limit, max number of records to retrieve. Generates the LIMIT clause. Optional.
 *
 * offset -- The offset, max number of records to retrieve. Generates the OFFSET clause. Optional.
 *
 * distinct -- If set to 1, a DISTINCT tag will be added so only unique rows are returned.
 *
 * sort -- The sorting field, or comma joined list of sorts. Used to generate the ORDER BY clause. Optional.
 *
 * format -- String, one of: xml, php. This specifies te output format for the records. Only xml is well supported.
 *
 * Return:
 *
 * XML representing the result set (and the SQL executed), or XML representing an error.
 * {@example docs/examples/wapigetfeatures.txt}
 * {@example docs/examples/wapi_error.txt}
 *
 * @package WebAPI
 */
/**
 *
 * @ignore
 *
 */
function _config_getfeatures_new() {
	$config = Array ();
	// Start config
	$config ["header"] = false;
	$config ["footer"] = false;
	$config ["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_getfeatures_new() {
	header ( 'Content-type: text/xml' );
}
function _dispatch_getfeatures_new($template, $args) {
    die('got here');
	$world = $args ['world'];
	$user = $args ['user'];
	
	// load the layer and verify their access
	$layer = $world->getLayerById ( $_REQUEST ['layer'] );
	if (! $layer) {
		$template->assign ( 'message', 'No such layer.' );
		return $template->display ( 'wapi/error.tpl' );
	}
	if ($layer->type != LayerTypes::VECTOR) {
		$template->assign ( 'message', 'This can only be done with vector layers.' );
		return $template->display ( 'wapi/error.tpl' );
	}
	$permission = $layer->getPermissionById ( $user->id );
	if ($permission < AccessLevels::EDIT) {
		$template->assign ( 'message', 'Permission denied.' );
		return $template->display ( 'wapierror.tpl' );
	}
	$template->assign ( 'layer', $layer );
	
	$criteria = new SearchCriteria ( $layer->url );
	
	$error = null;
	$distinct = RequestUtil::HasParam('distinct');
	$sql = $criteria->GetQuery($distinct);
	$results = $world->db->Execute ( $sql );
	
	if (! $results)
		$error = 'SQL error: ' . $world->db->ErrorMsg ();
	else {
		$fields = array_keys ( $results->fields );
		$rows = $results->getArray ();
	}
	
	// show it, depending on the format
	if (! isset ( $_REQUEST ['format'] ))
		$_REQUEST ['format'] = 'xml';
	switch ($_REQUEST ['format']) {
		case 'xml' :
			if ($error)
				return print $error;
			$template->assign ( 'sql', $sql );
			$template->assign ( 'fields', $fields );
			$template->assign ( 'results', $rows );
			$template->display ( 'wapi/getfeatures.tpl' );
			break;
		case 'php' :
			if ($error)
				return print $error;
			print serialize ( $results );
			break;
		default :
			return;
	}
}

?>