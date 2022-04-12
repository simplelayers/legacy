<?php
/**
 * The "Search Community" subsystem -- show info for a person.
 * @package Dispatchers
 */
/**
  */
function _config_peopleinfo() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_peopleinfo($template, $args) {
$world = $args['world'];

$person = $world->getPersonById($_REQUEST['id']);
if (!$person or !$person->canBeSeenById(false)) {
   print javascriptalert('That person was not found, or is unlisted.');
   return print redirect('demo.search');
}

$template->assign('taglinks', activate_tags($person->tags,'.?do=demo.searchpeople&search=') );
$template->assign('person',$person);
$template->display('demo/peopleinfo.tpl');

}?>
