<?php
/**
 * The form for changing your password.
 * @package Dispatchers
 */
/**
  */
function _config_spoof() {
	$config = Array();
	// Start config
	$config["admin"] = false;
	// Stop config
	return $config;
}

function _dispatch_spoof($template, $args) {
	$user = $args['user'];
	$world = $args['world'];
	if(!$user->admin){
		if($_SESSION['spoof'] === false){
			return print redirect('project.list');
		}else{
			$_SESSION['spoof'] = false;
			return print redirect('admin.spoof');
		}
	}
	if(isset($_REQUEST["contact"])){
		if($_REQUEST["contact"] == "off"){
			$_SESSION['spoof'] = false;
			return print redirect('admin.spoof');
		}elseif($_REQUEST["contact"] == "specific"){
			$_SESSION['spoof'] = (int)$_REQUEST["specific"];
		}elseif($_REQUEST["contact"] == "username"){
			$id = $world->getUserIdFromUsername($_REQUEST["specificName"]);
			if($id !== false){
				$_SESSION['spoof'] = (int)$id;
			}else{
				print javascriptalert('The username was not found.');
			}
		}else{
			$_SESSION['spoof'] = (int)$_REQUEST["contact"];
		}
		return print redirect('project.list');
	}
	$template->assign('radio', true);
	$template->display('admin/spoof.tpl');

}?>
