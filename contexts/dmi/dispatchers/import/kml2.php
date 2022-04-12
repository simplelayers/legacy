<?php
/**
 * Process the importkmlfiles1 form, examining the zipfile and importing any kmlfiles into new vector layers.
 * @package Dispatchers
 */
use formats\KML;

/**
  */
function _config_kml2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_kml2($template, $args,$org,$pageArgs) {
$user = SimpleSession::Get()->GetUser();
$world = System::Get();



// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to import this format.');
   return print redirect("layer.list");
}*/

// if they're already over quota, or if their account doesn't allow this, then bail
/*if ($user->diskUsageRemaining() <= 0) {
   $error = 'Your account is already at the maximum allowed storage.\nPlease delete some layers and try again.';
   print javascriptalert($error);
   return print redirect('layer.list');
}*/

// print a busy image to keep their eyes amused
//busy_image('Your files are being imported. Please wait.');
try {
	echo "<pre>";
	$format = new KML();
	$format->Import($_REQUEST);
	$message = "Import successful\n"; print javascriptalert($message); 
		
} catch( Exception $e) {	
	$message = "There was a problem during import: \n" . $e->getMessage() . "\n"; print javascriptalert($message);
}
javascriptalert($message);
//return print redirect('layer.list');
}?>
