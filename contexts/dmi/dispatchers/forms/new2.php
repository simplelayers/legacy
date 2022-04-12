<?
function _config_new2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_new2($template, $args) {
	$form = $args["world"]->createForm($args["user"]->id, $_REQUEST["id"]);
	
	print redirect('forms.edit&id='.$form->id);
}
?>