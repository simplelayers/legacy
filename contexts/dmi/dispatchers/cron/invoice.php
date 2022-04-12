<?php
function _config_invoice() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config["admin"] = false;
	// Stop config
	return $config;
}

function _headers_invoice() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'json';
	switch($_REQUEST['format'] ) {
		case "json":
			#header('Content-Type: text/javascript; charset=UTF-8'); 
			header('Content-Type: application/json');
			break;
		case "ajax":
		case "xml":
			header('Content-Type: text/xml');
			break;
	}	
}

function _dispatch_invoice($template, $args) {
	$world = $args['world'];
	$user = $args['user'];
	$orgs = $world->db->Execute("SELECT id FROM organizations WHERE paymentstartdate+(paymentspassed*paymentterm) < now()")->getRows();
	foreach($orgs as $org){
		$o = $world->getOrganizationById($org["id"]);
		$invoice = $o->makeInvoice();
	}
}
?>
