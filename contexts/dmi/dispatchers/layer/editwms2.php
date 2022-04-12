<?php
/**
 * Process the layereditwms1 form, to save their changes to the layer information.
 * @package Dispatchers
 */
/**
  */
function _config_editwms2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_editwms2($template, $args) {
$user = $args['user'];
$world = $args['world'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// are they allowed to be doing this at all?
/*if ($layer->owner->id != $user->id and $user->accounttype < AccountTypes::GOLD) {
    print javascriptalert('You must upgrade your account to edit others\' Layers.');
    return print redirect("layer.list");
}*/

//get existing URL for comparison (for metadata update) before overwriting it in the next section.
$existingURL = pruneWMSurl($layer->url);

// handle the simple attributes
$layer->name        = $user->uniqueLayerName($_REQUEST['name'],$layer);
$layer->url         = $_REQUEST['url'];
$layer->description = $_REQUEST['description'];
$layer->tags    = $_REQUEST['tags'];
$layer->bbox        = $_REQUEST['bboxparam'];
$existMetadata 		= $layer->metadata;
$newURL				= pruneWMSurl($_REQUEST['wmsurl']);
$layerid = $_REQUEST['id'];
if (($existingURL<>$newURL)||(!$existMetadata)){
	// populate metadata with wms capabilities doc
//	print javascriptalert($newURL);	
	
	if (preg_match('/eyeq2\.geoeye\.com\/EyeQService\/wms\/site\/2618/i', $newURL)){
       $newURL= 'https://ttierney:5cartograph[@eyeq2.geoeye.com/EyeQService/wms/site/2618';
	}	 
	$getCapabilitiesURL=$newURL.'?SERVICE=WMS&REQUEST=GetCapabilities';
	
	if (preg_match('/services\.digitalglobe\.com/i',$getCapabilitiesURL)){
			         $getCapabilitiesURL=$getCapabilitiesURL . '&CONNECTID=58a1d1e1-7089-4238-acc3-1276ef9020b5&USERNAME=cloud&PASSWORD=DGcs253787';
					     }
	$convert=New Convert();
	$layer->metadata=$convert->xmlToPhp(file_get_contents($getCapabilitiesURL));
}
//else{print javascriptalert('no change');}

// a quick sanity check: look at the URL and ensure that it has all the required parameters (there aren't many, cuz most
// are removed from the URL and are provided by the calling context in the Mapper class)
$url = $layer->url;

//if (!preg_match('/layers=/i',$url)) print javascriptalert('NOTE: The WMS server is lacking a LAYERS parameter.\nYou may want to check that.');

if(isset($_REQUEST["contact"])){
	$recipient = $world->getPersonById($_REQUEST["contact"]);
	if($recipient) $layer->setOwner($recipient->id);
}
// done -- keep them on the details page or send them to their layerbookmark list, depending
// on whether they own the layer they just edited
$layer->owner->notify($user->id, "edited layer:", $layer->name, $layer->id, "./?do=layer.info&id=".$layer->id, 5);

print redirect($layer->owner->id == $user->id ? 'layer.editwms1&id='.$layerid : 'layer.bookmarks');

}?>
