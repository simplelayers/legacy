<?php
use enums\AccountTypes;
/**
 * Processor for giving a Layer to someone else. The forms for accessing this
 * are in the layer/edit*.tpl templates, where the owner is viewing/editing the layer's properties.
 * @package Dispatchers
 */
/**
  */
function _config_giveaway() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_giveaway($template, $args) {
$user = $args['user'];
$world = $args['world'];


// are they allowed to be doing this at all?
/*if ($user->accounttype < AccountTypes::GOLD) {
   return print redirect('layer.list');
}*/

// load the layer and verify that they own it
$layer = $user->getLayerById($_REQUEST['layerid']);
if (!$layer) { print javascriptalert('You do not own that Layer.'); return print redirect('layer.list'); }

// make sure the layer really can be given away
if ($layer->type == LayerTypes::RELATIONAL) { print javascriptalert('This type of Layer cannnot be given away.'); return print redirect('layer.list'); }

// load the recipient and ensure that they're on the buddylist
$recipient = $world->getPersonById($_REQUEST['recipientid']);
if (!$user->buddylist->isOnListById($recipient->id)) { print javascriptalert('The specified recipient is not on your contact list.'); return print redirect('layer.list'); }
if ($recipient->accounttype < AccountTypes::GOLD) { print javascriptalert('The recipient cannot accept this layer.'); return print redirect('layer.list'); }

// blank out sharing and other such preferences
//$layer->setGlobalPermission(AccessLevels::NONE);
//$layer->sharelevel = AccessLevels::NONE;
$layer->name       = $recipient->uniqueLayerName($layer->name,$layer);

// finally, set the layer's new ownership; this requires raw SQL as this operation is forbidden by the API
$world->db->Execute('UPDATE layers SET owner=? WHERE id=?', array($recipient->id,$layer->id) );

// correct the DB's idea of permissions
$layer->fixDBPermissions();
$layer->touch();

// all done
print javascriptalert('You have given away ownership of the layer.');
print redirect('layer.list');
}?>
