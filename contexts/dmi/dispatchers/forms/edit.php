<?
function _config_edit() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_edit($template, $args) {
	$form = $args["world"]->getForm($_REQUEST["id"]);
	$template->assign("form", $form);
	$attributes = $form->layer->getAttributesVerbose(false,true);
	if($form->fields != ""){
		$template->assign("fields", json_encode($form->fields));
	}else{
		$template->assign("fields", "false");
	}
	$template->assign("attributes", $attributes);
	$template->assign("jsonAttribute", json_encode($attributes));
	$template->display('forms/edit.tpl');
}
?>