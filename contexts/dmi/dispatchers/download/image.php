<?php
/**
 * Download the specified layer's raster image in a zipfile; only for raster layers.
 * @package Dispatchers
 */
/**
  */
function _config_image() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_image($template, $args) {
$world = System::get();
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

// create a copy of the raster content, in plain JPEG format
$tempdir = $ini->tempdir . '/' . md5(microtime().mt_rand());
$imagefile = "{$tempdir}/{$layer->id}.jpg";
mkdir($tempdir);
$cmd = "gdal_translate -of JPEG \"{$layer->url}\" \"{$imagefile}\"";
shell_exec($cmd);

// make a zipfile of the file, then spit it out.
$zipfile = $ini->tempdir .'/'. md5(microtime().mt_rand()). '.zip';
shell_exec( escapeshellcmd("zip -j -0 -m $zipfile $imagefile") );
$filename = preg_replace('/\W/','_',$layer->name).'.zip';
print_download_http_headers($filename);
header('Content-Length: ' . filesize($zipfile));
#ob_end_flush();
readfile($zipfile);
unlink($zipfile);
}?>