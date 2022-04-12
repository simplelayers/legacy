<?php
/**
 * Download the specified layer's raster image in a zipfile; only for raster layers.
 * @package Dispatchers
 */
/**
  */
function _config_raster() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_raster($template, $args) {
$world = System::Get();
$user = SimpleSession::Get()->GetUser();
$ini = System::GetIni();

// load the layer and verify their access; note that the file download headers have already been sent
// by the controller core (index.php) so all we can do is output the file content
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::COPY) {
   return print 'You do not have permission to download that layer.';
}
if ($layer->type != LayerTypes::RASTER) {
   return print 'The layer you specified was not a raster (image) layer.';
}

// create a copy of the GeoTIFF, into a plain TIFF+TFW
$imagedir = $ini->tempdir .'/'. md5(microtime().mt_rand());
mkdir($imagedir);
$imagefile1 = "{$imagedir}/{$layer->name}.tif";
$imagefile2 = "{$imagedir}/{$layer->name}.tfw";
$command = escapeshellcmd("gdal_translate -co TFW=YES \"{$layer->url}\" \"{$imagefile1}\"");
shell_exec($command);

// make a zipfile of the file, then spit it out.
$zipfilename = preg_replace('/\W/','_',$layer->name).'.zip';
$zipfile = $ini->tempdir .'/'. md5(microtime().mt_rand()). '.zip';
$command = escapeshellcmd("zip -j -0 -m \"$zipfile\" \"{$imagefile1}\" \"{$imagefile2}\"");
shell_exec($command);
print_download_http_headers($zipfilename);
header('Content-Length: ' . filesize($zipfile));
ob_end_flush();
readfile($zipfile);

unlink($zipfile);
unlink($imagefile1);
unlink($imagefile2);
}?>