<?php
/**
 * Process the importgps1 form, and import the uploaded GPS data.
 * @package Dispatchers
 */
/**
  */
function _config_gps2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_gps2($template, $args) {
$user = $args['user'];
$world = $args['world'];

// if they're already over quota, or if their account doesn't allow this, then bail
if ($user->diskUsageRemaining() <= 0) {
   $error = 'Your account is already at the maximum allowed storage.\nPlease delete some layers and try again.';
   print javascriptalert($error);
   return print redirect('layer.list');
}

// print a busy image to keep their eyes amused
busy_image('Your file is being imported. Please wait.');
$tempfile = $world->config['tempdir'] .'/'. md5(microtime().mt_rand()) . '.gpx';
move_uploaded_file($_FILES['source']['tmp_name'],$tempfile);

// if it's not a GPX, translate it to GPX
if ($_REQUEST['format'] == 'mps') {
   if ($_REQUEST['type'] == 'waypoint') $flag = '-w';
   if ($_REQUEST['type'] == 'track')    $flag = '-t';
   if ($_REQUEST['type'] == 'route')    $flag = '-r';
   $newtempfile = $world->config['tempdir'] .'/'. md5(microtime().mt_rand()) . '.gpx';
   $command = escapeshellcmd("gpsbabel $flag -i Mapsource -f \"$tempfile\" -o gpx -F \"$newtempfile\"");
   `$command`;
   $tempfile = $newtempfile;
}

// use ogr2ogr to translate the GPX into a shapefile
//if ($_REQUEST['type'] == 'waypoint') $sublayer = 'waypoints';
//if ($_REQUEST['type'] == 'track')    $sublayer = 'tracks';
//if ($_REQUEST['type'] == 'route')    $sublayer = 'routes';
if ($_REQUEST['type'] == 'waypoint') $sublayer = '-w';
if ($_REQUEST['type'] == 'track')    $sublayer = '-t';
if ($_REQUEST['type'] == 'route')    $sublayer = '-r';
$shapefile = sprintf("%s/%s.shp", $world->config['tempdir'], md5(microtime().mt_rand()) );
$command = sprintf("%s %s -o %s %s", 'gpx2shp', escapeshellarg($tempfile), escapeshellarg($shapefile), $sublayer );
shell_exec($command);

// create the new layer entry, and use shp2pgsql() to import the data from the shapefile
$_REQUEST['name'] = $user->uniqueLayerName($_REQUEST['name']);
$layer = $user->createLayer($_REQUEST['name'],LayerTypes::VECTOR);
ping("importing...<br/>\n");
shp2pgsql($world,$shapefile,$layer->url);
ping("optimizing...<br/>\n");
$layer->optimize();
$layer->fixDBPermissions();
$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
$report = new Report($args['world'], $reportEntry);
$report->commit();


// send them to the editing page for their new layer
return print redirect("layer.edit1&id={$layer->id}");
}?>
