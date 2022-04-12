<?php

/**
 * Delete the specified project; this is called from projectedit1
 * @package Dispatchers
 */
/**
  */
function _config_delete() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_delete($template, $args) {
$user = $args['user'];
$world = $args['world'];

// load the project and verify their access
$project = $world->getProjectById($_REQUEST['id']);
if (!$project or $project->owner->id != $user->id) {
   print javascriptalert('Maps can only be deleted by their owner.');
   return print redirect('project.list');
}

// lower-level accounts can't delete their only project
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You cannot delete your only Map.');
   return print redirect('project.list');
}*/

// self destruct, then go home
$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_DELETE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_MAP, $project->id, $project->name, $args['user']);
$report = new Report(System::Get(),$reportEntry);
$project->delete();
$report->commit();
print redirect('project.list');

}?>
