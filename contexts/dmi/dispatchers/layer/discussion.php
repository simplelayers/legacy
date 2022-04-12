<?php
use subnav\LayerSubnav;
/**
 * Print info about a person.
 * @package Dispatchers
 */
/**
  */
function _config_discussion() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_discussion($template, $args) {
$user = $args['user'];
$world = $args['world'];
$layer = $world->getLayerById($_REQUEST['id']);
if(isset($_REQUEST["post"])){
	$return = $layer->newReply($user, $_REQUEST["parent"],$_REQUEST["post"]);
	print redirect("layer.discussion&id=".$layer->id."#".$return);
}elseif(isset($_REQUEST["del"])){
	$reply = $layer->getReply($_REQUEST['del']);
	$reply = $reply[0];
	if($reply["owner"] == $user->id or $user->admin or $layer->owner->id == $user->id){
		if($reply["id"] == 0){
			print redirect("layer.discussion&id=".$layer->id);
		}else{
			$layer->deleteReply($_REQUEST['del']);
			print redirect("layer.discussion&id=".$layer->id);
		}
	}
}
$template->assign('layer',$layer);
$template->assign('world',$world);
$subnav = new LayerSubnav();
$subnav->makeDefault($layer, $user);
$template->assign('subnav',$subnav->fetch());
$template->display('layer/discussion.tpl');
}?>