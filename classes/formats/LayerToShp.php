<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace formats;

use AccessLevels;
use Convert;
use Exception;
use SimpleSession;
use System;
use utils\ParamUtil;
use WAPI;

/**
 * Description of LayerToShp
 *
 * @author Arthur Clifford <artclifford@me.com>
 */
class LayerToShp {

    public static function Export($args) {
    
        $user = SimpleSession::Get()->GetUser();

        $ini = System::GetIni();
        $tempDir = $ini->tempdir;
        $wapi = System::GetWapi();
        $l = $wapi->RequireALayer_v5(null, $args);
        if(is_a($l, ProjectLayer::class)) {
            
            $layer = $l->layer;
        } else {
            $layer = $l;
        }

        $args['layer'] = $layer->id;
        $srs = $wapi->RequireProjection($args);

        $permissions = $layer->getPermissionById($user->id);
        
        $reporting = $layer->getRptLvlById($user->id);
        
        if($reporting < \ReportingLevels::GEOEXPORT) {
            // if (!$layer or $permissions < AccessLevels::COPY) {
            throw new Exception('No permission:You do not have sufficient permission to download this layer');
        }

        // prep the temporary directory and zipfile
        $random = md5(microtime() . mt_rand());
        $tempDir .= $random . '';
        mkdir("$tempDir");
        
        $layername = preg_replace('/\W/', '_', trim($layer->name));
        $filename = "$layername";
        $zipfile = "${tempDir}/{$layername}.zip";
        $features = ParamUtil::Get($args,'gids');
        $gidList = explode(',',$features);
        
        $gids = ParamUtil::SanitizeInts($gidList, ',');
        $fields = ParamUtil::Get($args, 'fields', '*');
        if ($fields === '*') {
            $fieldNames = array_keys($layer->getAttributes(false, FALSE));
        } else {
            $fieldNames = explode(',', $fields);
        }
        $atts = [];
        foreach ($fieldNames as $field) {
            if (in_array($field, ['the_geom', 'gid'])) {
                continue;
            }
            $atts[] = "\"{$field}\"";
        }

        /* if ($layer->type == LayerTypes::VECTOR or $layer->type == LayerTypes::RELATIONAL) {
          // use the handy-dandy pgsql2shp wrapper to dump the table to a shapefile
          $files = pgsql2shp($world, $layer->url, $layername);
          $tempDir = dirname($files[0]);
          } */
        $table = $layer->url;

        $ini = System::GetIni();
        // generate the filenames we'll be using
        $random = md5(microtime() . $table . mt_rand());
        $tempDir = $ini->tempdir . "{$random}/";

        mkdir("{$tempDir}");

        $view = "temp_{$table}_{$random}";
        $shpfile = "{$tempDir}$filename.shp";
        $shxfile = "{$tempDir}$filename.shx";
        $dbffile = "{$tempDir}$filename.dbf";
        $prjfile = "{$tempDir}$filename.prj";
        $metafile = "{$tempDir}$filename.xml";
        // create a stock PRJ file
        file_put_contents($prjfile, $srs);
        $metadata = $layer->metadata;
        if($metadata) {
            $converter = new Convert();
            ob_start();
            $converter->WritePhpToXml($metadata);
            $xml = ob_get_clean();
            file_put_contents($metafile,$xml);
        }
        

        // run pgsql2shp to dump the table to a shapefile

        $columns = implode(',', $atts);
        $columns.=',"gid"';

        if (!is_null($srs)) {
            $columns .= ",ST_Transform(the_geom, '$srs') as the_geom";
        } else {
            $columns .= ",the_geom";
        }
        
        $query = "SELECT $columns from {$table}";
        
        if ($gids !== '') {
            $query .= " where gid IN($gids)";
        }
        $shellQuery = escapeshellarg($query);
        $command = "pgsql2shp -f \"{$shpfile}\" -u {$ini->pg_admin_user} -h {$ini->pg_host} {$ini->pg_sl_db} $shellQuery";
        
        $res = shell_exec($command);

        // hold onto a list of the files
        $files = [$shpfile, $shxfile, $dbffile, $prjfile];
        if($metadata) {
            $files[] = $metafile;
        }
        $tempDir = dirname($files[0]);

        // zip up the files
        $files = array_map('escapeshellarg', $files);

        $cmd = ( "zip -1 -j $zipfile " . implode(' ', $files) );
        $zipParts = explode('/', $zipfile);
        $filename = array_pop($zipParts);
        $res = shell_exec($cmd);

        if (!file_exists($zipfile)) {
            throw new Exception('Export problem:Unable to create zipfile - '.$zipfile);            
        }
        
        $size = filesize($zipfile);
        // and transmit the zipfile
        #ob_end_flush();

        WAPI::SendDownloadHeaders($filename, $size,'application/zip');
        
        #var_dump($zipfile);
        #die();
        readfile($zipfile);
        // b_end_flush();
        unlink("{$tempDir}/*");
        unlink("{$tempDir}");
    }

}
