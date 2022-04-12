<?
function _config_edit() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_edit($template, $args) {
	$form = $args["world"]->getForm($_REQUEST["id"]);
	$form->Update(Array("fields"=>$_REQUEST["fields"], "name"=>$_REQUEST["name"]));
	echo "Success";
}
?>