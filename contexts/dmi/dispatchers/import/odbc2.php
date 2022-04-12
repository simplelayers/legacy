<?php
/**
 * Process the importcsv1 form, and import the uploaded CSV data.
 * @package Dispatchers
 */
/**
  */
function _config_odbc2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_odbc2($template, $args) {
$user = $args['user'];
$world = $args['world'];

// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::PLATINUM) {
   print javascriptalert('You must have at least Platinum level access to use ODBC layers.'); 
   return print redirect("layer.list");
}*/

///// sanitize
$ports = System::GetODBCPorts();

$table     = preg_replace('/[^\w\.]/', '', $_POST['table']);
$latcolumn = preg_replace('/\W/', '', $_POST['latcolumn']);
$loncolumn = preg_replace('/\W/', '', $_POST['loncolumn']);
$driver    = $_POST['servertype']; if (!array_key_exists($driver,$ports)) return print "Invalid driver $driver";
$odbchost  = preg_replace('/[^\w\.\-]/', '' , $_POST['odbchost']);
$odbcport  = (int) $_POST['odbcport'];
$odbcbase  = preg_replace('/[^\w\.\-]/', '' , $_POST['odbcbase']);
$odbcuser  = preg_replace('/[\r\n]/', '', $_POST['odbcuser']);
$odbcpass  = preg_replace('/[\r\n]/', '', $_POST['odbcpass']);

///// validate their connection

// connect
$odbcinfo = new stdClass;
$odbcinfo->driver    = $driver;
$odbcinfo->odbchost  = $odbchost;
$odbcinfo->odbcport  = $odbcport;
$odbcinfo->odbcuser  = $odbcuser;
$odbcinfo->odbcpass  = $odbcpass;
$odbcinfo->odbcbase  = $odbcbase;
$odbcinfo->table     = $table;
$odbcinfo->latcolumn = $latcolumn;
$odbcinfo->loncolumn = $loncolumn;
list($odbc,$dbcini,$freetdsconf) = $world->connectToODBC($odbcinfo);
if (!$odbc) return print "Failed: Unable to connect. Check the server type, hostname, username, password, and database name";
print "Connected to database.<br />\n";

// see if there are any rows in the table
$count = odbc_exec($odbc, "SELECT count(*) AS count FROM $table");
if (!$count) return print "Failed: Connected to the database, but did not find table '$table'";
$count = odbc_result($count,'count');
if (!$count) return print "Failed: Connected to the database, but table '$table' is apparently empty";
print "Found $count records in table '$table'<br />\n";

// make sure the latitude and longitude columns exist, by calculating their bbox
$checkid = odbc_exec($odbc, "SELECT min(id) AS minid, max(id) AS maxid FROM $table");
if (!$checkid) return print "Failed: The 'id' column is mandatory.";
$minid = odbc_result($checkid,'minid');
$maxid = odbc_result($checkid,'maxid');
if (!$maxid) return print "Failed: The 'id' column is mandatory.";
print "ID# range: $minid to $maxid <br/>\n";

// make sure the latitude and longitude columns exist, by calculating their bbox
$bbox = odbc_exec($odbc, "SELECT min($latcolumn) AS s, max($latcolumn) AS n, min($loncolumn) AS w, max($loncolumn) AS e FROM $table");
if (!$bbox) return print "Failed: Check the latitude and longitude columns";
$n = odbc_result($bbox,'n');
$s = odbc_result($bbox,'s');
$e = odbc_result($bbox,'e');
$w = odbc_result($bbox,'w');
print "Longitude range: $w to $e <br/>\n";
print "Latitude range: $s to $n <br/>\n";

// all set; close the ODBC connection
print "Tests OK.<br/>\n";
odbc_close($odbc);

// ensure that the target name is unique, by picking "random" suffixes until one doesn't match
$name = $_REQUEST['name'];
while ($user->getLayerByName($name)) {
   $name = $_REQUEST['name'] . ' ' . substr(md5(microtime()),0,5);
}

// create the Layer, save its "url"
$layer = $user->createLayer($name,LayerTypes::ODBC);
$layer->url = json_encode($odbcinfo);
$layer->colorscheme->setSchemeToSingle();
$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
$report = new Report($args['world'],$reportEntry);
$report->commit();

// send them to their layer list
print javascriptalert("Looks good. Your new layer has been created.");
return print redirect('layer.list');
}?>
