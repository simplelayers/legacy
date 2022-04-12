<?php
use utils\ParamUtil;

function _exec($args)
{

    $user = SimpleSession::Get()->GetUser();
    $args = WAPI::GetParams();
    $wapi = System::GetWapi();
    // load the layer and verify their access; note that the file download headers have already been sent
    // by the controller core (index.php) so all we can do is output the file content
    $layer = $wapi->RequireLayer();
    
    if (! $layer or $layer->getPermissionById($user->id) < AccessLevels::COPY) {
        return print 'You do not have permission to download that layer.';
    }
    
    // HTTP headers for downloading
    $filename = preg_replace('/\W/', '_', $layer->name) . '.csv';
    print_download_http_headers($filename);
    
    $csvfile = "php://output";
    $csvfh = fopen($csvfile, 'w');
    
    $visOnly = ParamUtil::GetBoolean($args, 'vis_only');
    $noGeom = ParamUtil::GetBoolean($args, 'no_geom');
    $useAliases = ParamUtil::GetBoolean($args, 'use_aliases');
    $format = ParamUtil::Get($args, 'geom_format', 'wkt');
    $isPoint = false;
    if ($layer->type & LayerTypes::VECTOR) {
        if ($layer->geomType == GeomTypes::POINT) {
            $noGeom = true;
            $isPoint;
        }
    }
    $columns = ParamUtil::GetList($args, ',', 'columns');
    
    $atts = $layer->getAttributesVerbose(false,false);
    
    if (count($columns) == 0) {
        foreach ($atts as $attName => $att) {
            if (($att['visible'] === false) && ($visOnly == true)) {
                continue;
            }
            $columns[] = $attName;
        }
    }
    
    if ($layer->type & (LayerTypes::VECTOR | LayerTypes::RELATIONAL | LayerTypes::RELATABLE)) {
        $geom = "";
        if (! $noGeom) {
            if ($isPoint) {
                list ($lat, $lon) = ParamUtil::GetValues($args, 'lat_field', 'lon_field');
                if ($lat && $lon) {
                    $geom = "ST_Y(the_geom) as $lat, ST_X(the_geom) as $lon";
                }
            }
            if (LayerTypes::IsFeatureSource($layer->type) && ($geom != "")) {
                switch (strtolower($format)) {
                    case 'wkt':
                        $geom = "ST_AsText(the_geom) as WKT";
                        break;
                    case 'wkb':
                        $geom = "ST_AsBinary(the_geom) as WKB";
                        break;
                    case 'geojson':
                        $geom = "ST_AsGeoJson(the_geom) as GeoJSON";
                        break;
                }
            }
        }
        if ($geom != "")
            $geom = ",$geom";
        
        $records = $world->db->Execute("SELECT $columns $geom FROM \"{$layer->url}\"");
    } else 
        if ($layer->type == LayerTypes::ODBC) {
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
                    list ($odbc, $odbcini, $freetdsconf) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                    $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                    $records = $db->Execute("SELECT * FROM {$odbcinfo->table}");
                    break;
            }
        } else {
            return print 'Not valid for this layer type.';
        }
    
    // fetch the first record and generate the field names list
    $colnames = array();
    foreach (array_keys($records->fields) as $colname) {
        if (! in_array($colname, array(
            'the_geom',
            'gid'
        ))) {
            $colnames[] = $colname;
        }
    }
    fputcsv($csvfh, $colnames);
    
    while (! $records->EOF) {
        $values = array();
        foreach ($colnames as $colname)
            $values[] = $records->fields[$colname];
        fputcsv($csvfh, $values);
        // fprintf($csvfh, "%s\n", implode("\t",$values) );
        $records->MoveNext();
    }
    fclose($csvfh);    
}
