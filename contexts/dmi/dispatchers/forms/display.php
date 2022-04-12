<?
function _config_display() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_display($template, $args) {
	$form = $args["world"]->getForm($_REQUEST["id"]);
	$template->assign("form", $form);
	$template->display('forms/display.tpl');
}
?>