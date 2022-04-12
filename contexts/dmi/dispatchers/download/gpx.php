<?php

/**
 * Download the specified layer as a GPX.
 * @package Dispatchers
 */
/**
  */
function _config_gpx() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_gpx($template, $args) {
$world = System::Get();
$user = SimpleSession::Get()->GetUser();
$ini = System::GetIni();


// load the layer and verify their access; note that the file download headers have already been sent
// by the controller core (index.php) so all we can do is output the file content
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::COPY) return print 'You do not have permission to download that layer.';

// ensure that it's a point or a line, and save a flag as to which
if     ($layer->geomtype == GeomTypes::POINT) $point = true;
elseif ($layer->geomtype == GeomTypes::LINE)  $point = false;
else return print 'The layer you specified was not a point or line layer.';

// headers
$filename = preg_replace('/\W/','_',$layer->name).'.gpx';
print_download_http_headers($filename);

// tempfile
$random = md5(microtime().mt_rand());
$gpxfile = sprintf("%s/%s.gpx", $ini->tempdir, $random );
$shpfile = sprintf("%s/%s.shp", $ini->tempdir, $random );
$shxfile = sprintf("%s/%s.shx", $ini->tempdir, $random );
$dbffile = sprintf("%s/%s.dbf", $ini->tempdir, $random );

if ($layer->type == LayerTypes::VECTOR or $layer->type == LayerTypes::RELATIONAL) {
   // generate the GPX output using ogr2ogr from GDAL/OGR
	$command = sprintf("ogr2ogr -f \"GPX\" %s PG:\"user=pgsql dbname={$world->name} password=5l1pp3ry\!\" %s", escapeshellarg($gpxfile), $layer->url);
	shell_exec($command);
}
else if ($layer->type == LayerTypes::ODBC)
{
   $odbcinfo = $layer->url;
   $loncolumn = $odbcinfo->loncolumn;
   $latcolumn = $odbcinfo->latcolumn;
   switch ($odbcinfo->driver)
   {
      case ODBCUtil::MYSQL:
         $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
         $records = $db->Execute("SELECT * FROM `{$odbcinfo->table}`");
         break;
      case ODBCUtil::PGSQL:
         $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
         $records = $db->Execute("SELECT * FROM \"{$odbcinfo->table}\"");
         break;
      case ODBCUtil::MSSQL:
         list($odbc,$odbcini,$freetdsconf) = $this->world->connectToODBC($odbcinfo,'NOCONNECT');
         $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
         $records = $db->Execute("SELECT * FROM {$odbcinfo->table}");
         break;
   }

   // get ready to create the shapefile
   dl("ext_shapelib.so");
   $shapefields = array();
   foreach (array_keys($records->fields) as $colname)
   {
      $info = array(SHPFT_STRING, 1000);
      if ($colname == $loncolumn or $colname == $latcolumn) $info = array(SHPFT_DOUBLE, 20, 10);
      if ($colname == 'id' or $colname == 'gid') $info = array(SHPFT_INTEGER, 10);
      $shapefields[$colname] = $info;
   }
   
   // create a shapefile, ierate over records adding points, close it
   $shp = shp_create($shpfile, SHPT_POINT, $shapefields);
   while (!$records->EOF)
   {
      $info = $records->fields;
      shp_add($shp, $info, array( array($info[$loncolumn],$info[$latcolumn]) ) );
      $records->MoveNext();
   }
   shp_close($shp);

   // now convert the SHP to a GPX with gpsbabel
   $command = sprintf("gpsbabel -i shape -o gpx -f %s -F %s", escapeshellarg($shpfile), escapeshellarg($gpxfile) );
   shell_exec($command);
}
else
{
   return print 'Not valid for this layer type.';
}

// and spit it out
ob_end_clean();
readfile($gpxfile);
unlink($gpxfile);
}?>