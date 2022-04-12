<?php 

function _config_start() {
	$config = Array();
	// Start config
	$config["sendWorld"] = true;
	$config['header'] = false;
	// Stop config
	return $config;
}


function _dispatch_start($template, $args) {
	$world = $args['world'];
	$session = SimpleSession::Get();
	$userInfo = $session->GetUserInfo();
	
	//header("Content-Type: text/html");
	/*$requireToken = false;
	$requireAuthentication = false;
	$requireProject = (isset($_REQUEST['project']) &&( isset($_SESSION) || isset($_REQUEST['embedded'])) );
	*/
	//$preViewerDispatcher = true;
	//function _dispatch_start($request,$world,$user,$template,$project,$embedded,$permission) {
	$requireProject = (isset($_REQUEST['project']) &&( isset($_SESSION) || isset($_REQUEST['embedded'])) );
	if($requireProject) {
		$project = $world->getProjectById($_REQUEST['project']);
		if (!$project) {	
	  		print javascriptalert('The requested Map could not be found.','html');
   		}
	}
	
	$swfId = "SimpleLayers";
	
	$world->logProjectUsage((isset($_REQUEST['embedded']) ? "embeded" : $userInfo),$project,$_SERVER['REMOTE_ADDR']);
	if(isset($project)){ $template->assign('project', $project->id );
	$_SESSION[$project->id."lastNav"] = 0;}
	$title = explode(".",RequestUtil::Get('application'));
	$title = $title[0] . " -- Powered by Simple Layers";
	$template->assign('title',$title);
	$params = "";
	
	$request= $_GET;
	foreach( $request as $key=>$val ) {
		if( $key == "do" ) { continue; }
		if( $key=="format" ) { continue; }
		if( $key=="asset") { continue; }
		if( $key=="PHPSESSION" ){continue; }
		if( substr($key,0,2)=="__") continue;
		if( substr($key,0,13)=="showInherited") {continue;}
		if( $key=="style"){continue;}
		$params .= "&".$key."=".$val;
		//echo( $key."=".$val."<br/>");
	}
	
	$width = isset($request['width'] ) ? $request['width'] : '100%';
	$height = isset($request['height'])? $request['height'] : '100%';
	
	$url = 	("do=get&format=swf&asset=SimpleLayers.swf".$params);
	
	$template->assign('swf',"?$url");
	$name = isset($project) ? ": ".$project->name : "";
	$template->assign('title',"Map".$name);
	$template->assign('bgcolor','#cccccc');
	$template->assign('version_major','9');
	$template->assign('version_minor','0');
	$template->assign('version_revision','28');
	$template->assign('application','SimpleLayers.swf');
	$template->assign('width','100%');
	$template->assign('height','100%');
	
	$template->display("flexapp.tpl");
	$report = new Report($world, REPORT_ACTIVITY_OPEN, REPORT_ENVIRONMENT_VIEWER, REPORT_TARGET_MAP, $project->id, $project->name, $userInfo);
	$report->commit();
// done; show the HTML template
//$template->display('start.tpl');

}
?>

