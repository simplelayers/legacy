<?php
/**
 * Process the importraster1 form, to process the uploaded image and make a layer from it.
 * @package Dispatchers
 */
/**
  */
function _config_raster2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_raster2($template, $args) {
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

// print a busy image to keep their eyes amused
busy_image('Your raster file is being imported. This can take several minutes. Please wait.');

// creating the Layer itself is easy! :)
ping("Creating layer entry");
$_REQUEST['name'] = $user->uniqueLayerName($_REQUEST['name']);
$layer = $user->createLayer($_REQUEST['name'],LayerTypes::RASTER);

// move the four files into a temporary location. This also creates a .prj file if one was not supplied.
ping("Handling uploaded file<br/>\n");
$extension = substr($_FILES['source']['name'],strrpos($_FILES['source']['name'],'.')+1);
$tempid = md5(microtime() . mt_rand() );
$target     = $layer->url;
$temp_image = "{$world->config['tempdir']}/{$tempid}.{$extension}";
$temp_world = "{$world->config['tempdir']}/{$tempid}.wld";
$temp_prj   = "{$world->config['tempdir']}/{$tempid}.prj";
move_uploaded_file($_FILES['source']['tmp_name'],$temp_image);
move_uploaded_file($_FILES['worldfile']['tmp_name'],$temp_world);
file_put_contents($temp_prj,$_REQUEST['projection']);

// call gdalwarp to reproject the image into latlong and GeoTIFF format, in its final resting place
ping("<br/>\nReprojecting<br/>\n");
$command = escapeshellcmd("gdalwarp -r bilinear -s_srs \"ESRI::{$temp_prj}\" -t_srs \"EPSG:4326\" {$temp_image} {$target}");
passthru($command);

// call gdaladdo to add overviews on the image in-place
ping("<br/>\nAdding overviews<br/>\n");
$command = escapeshellcmd("gdaladdo -r average {$target} 2 4 8 16 32 64 128 256 512 1024");
passthru($command);
$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
$report = new Report($args['world'],$reportEntry);
$report->commit();

// send them to the editing view
return print redirect("layer.edit1&id={$layer->id}");
}?>
