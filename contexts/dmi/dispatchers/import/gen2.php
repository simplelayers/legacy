<?php
/**
 * Process the importgen1 form, and import the uploaded GEN data.
 * @package Dispatchers
 */
/**
  */
function _config_gen2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_gen2($template, $args) {
$user = $args['user'];
$world = $args['world'];

// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to import this format.');
   return print redirect("layer.list");
}*/

// if they're already over quota, or if their account doesn't allow this, then bail
if ($user->diskUsageRemaining() <= 0) {
   $error = 'Your account is already at the maximum allowed storage.\nPlease delete some layers and try again.';
   print javascriptalert($error);
   return print redirect('layer.list');
}

// print a "busy" image to keep their eyes amused
busy_image('Your file is being imported. Please wait.');

// ensure that the target name is unique, by picking "random" suffixes until one doesn't match
$name = $_REQUEST['name'];
while ($user->getLayerByName($name)) {
   $name = $_REQUEST['name'] . ' ' . substr(md5(microtime()),0,5);
}

// grab the GEN file, and run gen2shp on it to make a shapefile
// mental note: gen2shp chokes if the newlines aren't perfect \n style, so fix those beforehand
// mental note: gen2shp adds the .shp automagically, so we add it afterward
$type = $_REQUEST['type']; if (!in_array($type,array('points','lines','polygons'))) $type = 'points';
$tempfile = $world->config['tempdir'] .'/'. md5(microtime().mt_rand()) . '.gen';
$shapefile = $world->config['tempdir'] .'/'. md5(microtime().mt_rand());
move_uploaded_file($_FILES['source']['tmp_name'],$tempfile);
file_put_contents($tempfile,str_replace("\r\n","\n",file_get_contents($tempfile)));
shell_exec("gen2shp {$shapefile} {$type} < {$tempfile}");
$shapefile .= '.shp';

// create the new layer entry, and use shp2pgsql() to import the data from the shapefile
$layer = $user->createLayer($name,LayerTypes::VECTOR);
ping("importing...<br/>\n");
shp2pgsql($world,$shapefile,$layer->url);
ping("optimizing...<br/>\n");
$layer->optimize();
$layer->fixDBPermissions();
$reportEntry = Report::MakeEntry(REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
$report = new Report($args['world'],$reportEntry );
$report->commit();

// send them to the editing page for their new layer
return print redirect("layer.edit1&id={$layer->id}");
}?>
