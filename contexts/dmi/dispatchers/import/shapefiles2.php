<?php
/**
 * Process the importshapefiles1 form, examining the zipfile and importing any shapefiles into new vector layers.
 * @package Dispatchers
 */
/**
  */
function _config_shapefiles2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_shapefiles2($template, $args) {
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
busy_image('Your shapefiles are being imported. Please wait.');
$ini = System::GetIni();
// create a temporary directory and extract the files into it
$directory = $ini->tempdir . md5(microtime().mt_rand());
mkdir($directory);
ping("Unzipping under $directory...");
$command = escapeshellcmd("unzip -j -o {$_FILES['source']['tmp_name']} -d {$directory}");
shell_exec($command);
ping("done<br/>\n");

// go through the directory and unzip any ZIP files
chdir($directory);
foreach (glob('*.[Zz][Ii][Pp]') as $zip) {
   ping("Unzipping internal ZIP $zip");
   $command = escapeshellcmd("unzip -j -o {$zip} -d {$directory}");
   shell_exec($command);
   unlink($zip);
}

// fetch a list of shapefiles and go through them
$import_ok  = array();
$import_err = array();
$shapefiles = array_merge(glob("$directory/*.[sS][hH][pP]"));
foreach ($shapefiles as $shapefile) {
   // sanitize the name, then make sure it's unique by adding a random string if necessary
   $desiredname  = $_REQUEST['basename'] .' '. basename($shapefile,'.shp');
   $name = $user->uniqueLayerName($desiredname);
   ping("Processing $name ... <br/>");

   // create the Layer object we'll be populating
   $layer = $user->createLayer($name,LayerTypes::VECTOR);
   $layer->colorscheme->setSchemeToSingle();
   $table = $layer->url;

   // Figure out the projection and put it into a format which OGR can understand
   // If there's no .PRJ file but the projection starts with a [ then it's ESRI WKT, which belongs in a .PRJ file
   // If that's not the case (thus, the PRJ file still being empty/nonexistent) then the projection must be a PROJ4 string for the command line
   $prjfile = preg_replace('/\.shp$/','.prj',$shapefile); $srs = "";
   if (!@file_get_contents($prjfile) and strpos($_REQUEST['projection'],'[')!==false) file_put_contents($prjfile,$_REQUEST['projection']);
   if (!@file_get_contents($prjfile)) $srs = '-s_srs ' . escapeshellarg($_REQUEST['projection']);

   // now use ogr2ogr to reproject the data
   $tempfile = $directory .'/'. md5(microtime().mt_rand()) . '.shp';
   $command = escapeshellcmd("ogr2ogr -t_srs EPSG:4326 $srs $tempfile \"$shapefile\"");
   shell_exec($command);
   $shapefile = $tempfile;

   // and do the import
   shp2pgsql($world,$shapefile,$table);

   // If the layer has no features, there was probably a problem, so we use the recordcount to check whether import was successful.
   // If the import was not successful, the layer is deleted.
   // This could give a false alarm in the (very rare?!) case where someone intentionally uploads a shapefile with a structure but no records
   $status = (bool) $layer->getRecordCount();
   if ($status) { array_push($import_ok,$name); } else { array_push($import_err,$name); $layer->delete(); }
   ping("done with $name<br/>\n");
   $layer->fixDBPermissions();
   $layer->setDBOwnerToOwner();
   $reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
	$report = new Report($args['world'],$reportEntry);
	$report->commit();
}

// done, print 2 JS alerts showing them what worked and what failed, then send them to their list of layers
// This is also effective at notifying them when layers had to be renamed to avoiod a naming conflict
if ($import_ok)  { $message = "The following layers were imported successfully:\n" . implode("\n",$import_ok) . "\n"; print javascriptalert($message); }
if ($import_err) { $message = "The following layers WERE NOT imported:\n" . implode("\n",$import_err) . "\n"; print javascriptalert($message); }
return print redirect('layer.list');

}?>
