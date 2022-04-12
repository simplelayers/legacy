<?php

/**
 * This processes the form generated by project1, to create the project as requested.
 * @package Dispatchers
 */
/**
  */
function _config_create2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_create2($template, $args) {
$user = $args['user'];

/*if ($user->accounttype < AccountTypes::GOLD) {
    print javascriptalert('To create Maps, you must have at least Gold level access.');
    return print redirect('project.list');
}*/

// create the project and assign the attributes
$_REQUEST['name'] = $user->uniqueProjectName($_REQUEST['name']);
$project = $user->createProject($_REQUEST['name']);
$project->description = $_REQUEST['description'];
//$activity=null,$environment=null,$target=null,$target_id=null,$target_name=null,$actor=null,$actor_id=null,$actor_ip=null,$recipient=null,$recipient_id=null,$recipient_type=null) {

$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_MAP, $project->id, $project->name, $args['user']);
$report = new Report(System::Get(),$reportEntry);
$report->commit();
// and send them to the editing view
print redirect("project.edit1&id={$project->id}");

}?>
