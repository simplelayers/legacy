<?php
/**
 * Download the specified layer as a CSV; only for vector layers.
 * @package Dispatchers
 */
/**
  */
function _config_csv() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_csv($template, $args) {
$world = System::Get();
$user = SimpleSession::Get()->GetUser();
$ini = System::GetIni();

// load the layer and verify their access; note that the file download headers have already been sent
// by the controller core (index.php) so all we can do is output the file content
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::COPY) return print 'You do not have permission to download that layer.';

// HTTP headers for downloading
$filename = preg_replace('/\W/','_',$layer->name).'.csv';
print_download_http_headers($filename);

// ZIP file initialization
$random = md5(microtime().mt_rand());
$csvfile = "php://output";//sprintf("%s/%s/%s.csv", $ini->tempdir, $random, preg_replace('/\W/','_',$layer->name) );
//$zipfile = sprintf("%s/%s.zip", $ini->tempdir, $random);
//mkdir( sprintf("%s/%s", $ini->tempdir, $random ) );
$csvfh = fopen($csvfile,'w');

///// start generating CSV

if ($layer->type == LayerTypes::VECTOR or $layer->type == LayerTypes::RELATIONAL) {
   $records = $world->db->Execute("SELECT *,st_AsText(the_geom) AS WKT FROM \"{$layer->url}\"");
}
else if ($layer->type == LayerTypes::ODBC) {
   $odbcinfo = $layer->url;
   switch ($odbcinfo->driver) {
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
} else {
   return print 'Not valid for this layer type.';
}

// fetch the first record and generate the field names list
$colnames = array();
foreach (array_keys($records->fields) as $colname) if (!in_array($colname ,array('the_geom','gid'))) $colnames[] = $colname;
fputcsv($csvfh, $colnames );

while (!$records->EOF) {
   $values = array();
   foreach ($colnames as $colname) $values[] = $records->fields[$colname];
   fputcsv($csvfh, $values);
   //fprintf($csvfh, "%s\n", implode("\t",$values) );
   $records->MoveNext();
}

// done generating the KMl data; compress it into ZIP/KMZ format
fclose($csvfh);
//shell_exec("zip -9jm $zipfile $csvfile");


// all done; send the file contenmt
//ob_end_flush();
//readfile($zipfile);
//unlink($zipfile);
}?>
