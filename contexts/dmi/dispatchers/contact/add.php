<?php
use utils\ParamUtil;
/**
 * Add a person to your buddylist; This is called from the peopleinfo page for that person.
 * @package Dispatchers
 */
/**
  */
function _config_add() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_add($template, $args) {
/* @var $user Person */
$user = $args['user'];
$contactId = ParamUtil::Get($args,'contactId',ParamUtil::Get($args,'id'));
$contactName = ParamUtil::Get($args,'contactName',ParamUtil::Get($args,'name'));

if(!is_null($contactId)) {
    $contact = System::Get()->getPersonById($contactId);    
} 
if(!is_null($contactName)) {
    $contact = System::Get()->getPersonByUsername($contactName);
}


if(!$contact) {
    print javascriptalert('Contact not recognized');
    print redirect('contact.list');
}

$user->buddylist->addPerson($contact);
if(ParamUtil::Get($args,'noreply')) {
    die();
}
print redirect("contact.info&contactId={$contact->id}");

}?>