<?php

use formats\LayerToShp;

/**
 * Download the specified layer as a zipped shapefile; only for vector layers.
 * @package Dispatchers
 */

/**
 */
function _config_shp() {
    $config = Array();
    // Start config
    $config ["header"] = false;
    $config ["footer"] = false;
    // Stop config
    return $config;
}

function _dispatch_shp($template, $args) {
  
    $wapi = System::GetWapi();
    $args = $wapi->GetParams();
    $args['srs'] = 'layer';
    LayerToShp::Export($args);
    return;
    $world = System::Get();
    $user = SimpleSession::Get()->GetUser();
    $ini = System::GetIni();
    $tempDir = $ini->tempdir;


    // load the layer and verify their access; note that the file download headers have already been sent
    // by the controller core (index.php) so all we can do is output the file content
    $layer = $world->getLayerById($_REQUEST ['id']);

    // prep the temporary directory and zipfile
    $random = md5(microtime() . mt_rand());
    $tempDir .= $random . '';
    mkdir("$tempDir");
    $layername = preg_replace('/\W/', '_', trim($layer->name));
    $filename = "$layername.zip";
    $shpfile = "{$tempDir}/{$layername}.shp";
    $shxfile = "{$tempDir}/{$layername}.shx";
    $dbffile = "{$tempDir}/{$layername}.dbf";
    $zipfile = "${tempDir}/{$layername}.zip";
    $prjfile = "${tempDir}/{$layername}.prj";
    
    $metadata = $layer->metadata;
    $xmlFile = '';
    if ($metadata) {
        $converter = new Convert();
        ob_start();
        $converter->phpToXml($layer->metadata);
        $xmlFile = $tempDir . '/' . $layername . '.xml';
        file_put_contents($xmlFile, ob_get_clean());
    }
    if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::COPY)
        return print 'You do not have permission to download that layer.';

    if ($layer->type == LayerTypes::VECTOR or $layer->type == LayerTypes::RELATIONAL) {
        // use the handy-dandy pgsql2shp wrapper to dump the table to a shapefile
        $table = $layer->url;
        $tableTemp = $table . '_temp';
        $importInfo = $layer->import_info;
        $hadSrs = false;
        if ($importInfo) {
            $srs = $importInfo['info']['srs'];
            if ($srs) {
                file_put_contents($prjfile,$srs);
                $db = System::GetDB();
                $db->debug = true;
                $db->Execute("CREATE TEMP TABLE $tableTemp as select * from $table");
                $db->Execute("Update $tableTemp set the_geom = ST_Transform(the_geom,'$srs')");
                $files = pgsql2shp($world, $tableTemp, $layername, true, false);
                $hadSrs = true;
            }
        } else {
            $files = pgsql2shp($world, $layer->url, $layername);
        }
        var_dump($files);
        die();
        $tempDir = dirname($files[0]);
    } else if ($layer->type == LayerTypes::ODBC) {
        $odbcinfo = $layer->url;
        $loncolumn = $odbcinfo->loncolumn;
        $latcolumn = $odbcinfo->latcolumn;

        switch ($odbcinfo->driver) {
            case ODBCUtil::MYSQL :
                $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                $records = $db->Execute("SELECT * FROM `{$odbcinfo->table}`");
                break;
            case ODBCUtil::PGSQL :
                $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                $records = $db->Execute("SELECT * FROM \"{$odbcinfo->table}\"");
                break;
            case ODBCUtil::MSSQL :
                list ( $odbc, $odbcini, $freetdsconf ) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                $records = $db->Execute("SELECT * FROM {$odbcinfo->table}");
                break;
        }


        // get ready to create the shapefile
        dl("ext_shapelib.so");
        $fileStart = "{$tempDir}/{$layername}";
        /* if($layer->import_info) {
          if($layer->import_info['srs']) {
          file_put_contents($fileStart.'prj',$layer->import_info['srs'])
          }
          } */


        $files = array(
            $shpfile,
            $shxfile,
            $dbffile
        );
        $shapefields = array();
        foreach (array_keys($records->fields) as $colname) {
            $info = array(
                SHPFT_STRING,
                10000
            );
            if ($colname == $loncolumn or $colname == $latcolumn)
                $info = array(
                    SHPFT_DOUBLE,
                    20,
                    10
                );
            if ($colname == 'id' or $colname == 'gid')
                $info = array(
                    SHPFT_INTEGER,
                    10
                );
            $shapefields [$colname] = $info;
        }

        // create a shapefile, ierate over records adding points, close it
        $shp = shp_create($shpfile, SHPT_POINT, $shapefields);
        while (!$records->EOF) {
            $info = $records->fields;
            shp_add($shp, $info, array(
                array(
                    $info [$loncolumn],
                    $info [$latcolumn]
                )
            ));
            $records->MoveNext();
        }
        shp_close($shp);
    } else {
        return print 'Not valid for this layer type.';
    }


    // zip up the files
    $files = array_map('escapeshellarg', $files);

    $cmd = ( "zip -1 -j $zipfile " . implode(' ', $files) );
    $zipParts = explode('/', $zipfile);
    $filename = array_pop($zipParts);
    $res = shell_exec($cmd);

    if (!file_exists($zipfile)) {
        echo($filename . ' unable to create $zipfile');
        die(); //throw new Exception('Unable to create zip file:')
    }
    $filenames = scandir($tempDir);
    /* $filename ='';

      foreach($filenames as $filename) {

      if( stripos($filename,'.zip')) {
      break;
      }
      } */

    // and transmit the zipfile
    print_download_http_headers($filename);
    ob_end_flush();
    #var_dump($zipfile);
    #die();
    readfile($zipfile);
    // b_end_flush();
    // nlink("{$tempDir}/*");
    // nlink("{$tempDir}");
}

?>
