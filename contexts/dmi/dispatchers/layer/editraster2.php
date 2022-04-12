<?php
/**
 * Process the layereditraster1 form, to save their changes to the layer information.
 * @package Dispatchers
 */
/**
  */
function _config_editraster2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_editraster2($template, $args) {
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

// print a busy image to keep their eyes amused
if (@$_FILES['source']['name']) busy_image('Your raster file is being imported. This may take a few minutes. Please wait.');

// handle the simple attributes
$layer->name        = $user->uniqueLayerName($_REQUEST['name'],$layer);
$layer->description = $_REQUEST['description'];
$layer->tags    = $_REQUEST['tags'];
$layerid = $_REQUEST['id'];
// if they uploaded a new file for this layer, go ahead and process it
if (@$_FILES['source']['name']) {
   // first, delete the existing file(s) that are about to be replaced
   unlink($layer->url);

   // move the four files into a temporary location. This also creates a .prj file if one was not supplied.
   ping("Handling uploaded file<br/>\n");
   $extension = substr($_FILES['source']['name'],strrpos($_FILES['source']['name'],'.')+1);
   $tempid = md5(microtime() . mt_rand() );
   $temp_image = "{$world->config['tempdir']}/{$tempid}.{$extension}";
   $temp_world = "{$world->config['tempdir']}/{$tempid}.wld";
   $temp_prj   = "{$world->config['tempdir']}/{$tempid}.prj";
   move_uploaded_file($_FILES['source']['tmp_name'],$temp_image);
   move_uploaded_file($_FILES['worldfile']['tmp_name'],$temp_world);
   if (!$_REQUEST['projection']) $_REQUEST['projection'] = 'init=epsg:4326';
   file_put_contents($temp_prj,$_REQUEST['projection']);

   // ultimately, we'll have a GeoTIFF with no worldfile or prjfile
   $target = $layer->url;

   // call gdalwarp to reproject the image into latlong and GeoTIFF format, in its final resting place
   ping("<br/>\nReprojecting<br/>\n");
   $projection = @preg_replace('/^\s*init=/','',$_REQUEST['projection']);
   $command = "gdalwarp -r bilinear -s_srs \"ESRI::{$temp_prj}\" -t_srs 'epsg:4326' {$temp_image} {$target}";
   passthru($command);

   // call gdaladdo to add overviews on the image in-place
   ping("<br/>\nAdding overviews<br/>\n");
   $command = escapeshellcmd("gdaladdo -r average {$target} 2 4 8 16 32 64 128 256 512 1024");
   passthru($command);
}

if(isset($_REQUEST["contact"])){
	$recipient = $world->getPersonById($_REQUEST["contact"]);
	if($recipient) $layer->setOwner($recipient->id);
}

// done -- keep them on the details page or send them to their layerbookmark list, depending
// on whether they own the layer they just edited
$layer->owner->notify($user->id, "edited layer:", $layer->name, $layer->id, "./?do=layer.info&id=".$layer->id, 5);

print redirect($layer->owner->id == $user->id ? 'layer.editraster1&id='.$layerid : 'layer.bookmarks');
}?>
