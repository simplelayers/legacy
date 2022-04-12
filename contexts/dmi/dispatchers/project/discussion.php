<?php
use utils\PageUtil;
/**
 * Displays a projects discussion thread.
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

function _dispatch_discussion($template, $args,$org,$pageArgs) {
	$user = $args['user'];
	$world = System::Get();
	$ini = System::GetIni();
	
	$project = $world->getprojectById($_REQUEST['id']);
	if(isset($_REQUEST["post"])){
		$return = $project->newReply($user, $_REQUEST["parent"],$_REQUEST["post"]);
		print redirect("project.discussion&id=".$project->id."#".$return);
	}elseif(isset($_REQUEST["del"])){
		$reply = $project->getReply($_REQUEST['del']);
		$reply = $reply[0];
		if($reply["owner"] == $user->id or $user->admin or $project->owner->id == $user->id){
			if($reply["id"] == 0){
				print redirect("project.discussion&id=".$project->id);
			}else{
				$project->deleteReply($_REQUEST['del']);
				print redirect("project.discussion&id=".$project->id);
			}
		}
	}
	$template->assign('project',$project);
	$template->assign('world',$world);
	$pageArgs['pageSubnav'] = 'maps';
	$pageArgs['mapid'] = $project->id;
	PageUtil::SetPageArgs($pageArgs, $template);
	PageUtil::MixinMapArgs($template);
	$template->display('project/discussion.tpl');
}?>