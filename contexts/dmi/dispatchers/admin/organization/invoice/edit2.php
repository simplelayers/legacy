<?php
/**
 * The form for changing your password.
 * @package Dispatchers
 */
/**
  */
function _config_edit2() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_edit2($template, $args) {
$error = false;
if ($error) {
   print javascriptalert($error);
   return $template->display('admin/organization/invoice/new1.tpl');
}
if(isset($_REQUEST["sent"]) and $_REQUEST["sent"] == "12/31/1969") $_REQUEST["sent"] = null;
if(isset($_REQUEST["paid"]) and $_REQUEST["paid"] == "12/31/1969") $_REQUEST["paid"] = null;
if(isset($_REQUEST["comment"])) $_REQUEST["comment"] = nl2br($_REQUEST["comment"]);
$invoice = $args['world']->getInvoiceById($_REQUEST["id"]);
$allfields = $invoice->getAllFieldsAsArray();
unset($allfields["id"]);
$updates = Array();
foreach($allfields as $key => $value){
	if(isset($_REQUEST[$key]) or $key == "paid" or $key == "sent"){
		if((string)$_REQUEST[$key] != (string)$value){
			$updates[$key] = $_REQUEST[$key];
		}
	}
}
if(!empty($updates)) $invoice->Update($updates);
print redirect('admin.organization.invoice.edit1&id='.$invoice->id);
}?>
