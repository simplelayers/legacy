<?php

use utils\LayerUtil;

/**
 * Download the specified layer as a KML; only for point layers.
 *
 * @package Dispatchers
 */

/**
 */
function _config_kml()
{
    $config = array();
    // Start config
    $config["header"] = false;
    $config["footer"] = false;
    // Stop config
    return $config;
}

function _dispatch_kml($template, $args)
{
    $world = System::Get();
    $user = SimpleSession::Get()->GetUser();
    $ini = System::GetIni();

    $srcGids = \utils\ParamUtil::Get($args, 'gids');
    $gidList = explode(',', trim($srcGids));
    unset($srcGids);
    $gids = intval(array_shift($gidList));
    while (count($gidList) > 0) {
        $gids .= intval(array_shift($gidList));
    }

    // load the layer and verify their access; note that the file download headers have already been sent
    // by the controller core (index.php) so all we can do is output the file content
    $layer = Layer::Get($_REQUEST['id']);
    /**
     * @var $colorscheme \ColorScheme
     */
    $colorscheme = $layer->colorscheme;
    $classes = $colorscheme->getAllEntries();
    if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::COPY)
        return print 'You do not have permission to download that layer.';

    // HTTP headers for downloading
    $filename = preg_replace('/\W/', '_', $layer->name) . '.kmz';


    // ZIP file initialization
    $random = md5(microtime() . mt_rand());
    $path = sprintf("%s%s/", $ini->tempdir, $random);
    mkdir($path);
    $kmlfile = sprintf("%s%s.kml", $path, $filename);
    $zipfile = sprintf("%s%s.zip", $path, $filename);
    $filesPath = sprintf("%sfiles/", $path);

    $atts = array_keys($layer->getAttributes(false, FALSE));
    $kmlfh = fopen($kmlfile, 'w');

    // DB connection and fetch
    if ($layer->type == LayerTypes::VECTOR or $layer->type == LayerTypes::RELATIONAL) {
        $where = (count($gidList) > 0) ? " where gid IN ($gids)" : '';
        $records = $world->db->Execute("SELECT " . implode(',', $atts) . ",ST_AsKML(the_geom) AS kml_geom FROM \"{$layer->url}\" $where");
    } elseif ($layer->type == LayerTypes::ODBC) {
        $odbcinfo = $layer->url;
        $loncolumn = $odbcinfo->loncolumn;
        $latcolumn = $odbcinfo->latcolumn;
        switch ($odbcinfo->driver) {
            case ODBCUtil::MYSQL:
                $db = NewADOConnection("mysql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                $records = $db->Execute("SELECT " . implode(',', $atts) . " FROM `{$odbcinfo->table}`");
                break;
            case ODBCUtil::PGSQL:
                $db = NewADOConnection("postgres://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@{$odbcinfo->odbchost}/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                $records = $db->Execute("SELECT " . implode(',', $atts) . " FROM \"{$odbcinfo->table}\"");
                break;
            case ODBCUtil::MSSQL:
                list($odbc, $odbcini, $freetdsconf) = $this->world->connectToODBC($odbcinfo, 'NOCONNECT');
                $db = NewADOConnection("mssql://{$odbcinfo->odbcuser}:{$odbcinfo->odbcpass}@dsn/{$odbcinfo->odbcbase}?port={$odbcinfo->odbcport}&fetchmode=" . ADODB_FETCH_ASSOC);
                $records = $db->Execute("SELECT " . implode(',', $atts) . " FROM {$odbcinfo->table}");
                break;
        }
    }

    $tooltip = "" . $layer->tooltip;
    $geomType = $layer->geom_type;

    if ($geomType == GeomTypes::POINT) {
        if (!file_exists($filesPath)) {

            mkdir($filesPath);
        }

        $files = LayerUtil::GenerateIcons($layer);
        foreach ($files as $file) {
            $fileInfo = explode('/', $file);
            $fileName = array_pop($fileInfo);
            copy($file, $filesPath . $fileName);
        }
    }

    // start generating KML; note that we iterate through the ADOdb recordset manually, to save memory
    fwrite($kmlfh, "\xEF\xBB\xBF");
    fwrite($kmlfh, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
    fwrite($kmlfh, "<kml xmlns=\"http://earth.google.com/kml/2.0\">\n");
    fwrite($kmlfh, "  <Folder>\n");
    fwrite($kmlfh, "    <name>{$layer->name}</name>\n");
    foreach ($classes as $class) {
        fwrite($kmlfh, '<Style id="sl_def_' . $class->id . "\" >\n");
        $fill = $class->fill_color;
        if ($fill == 'trans') {
            $fill = '00000000';
        } else {
            $fill = str_replace('#', '', $fill);
            $fill = str_pad($fill, 6, "0", STR_PAD_LEFT);
            $fill = ("FF" . substr($fill, 4, 2) . substr($fill, 2, 2)) . substr($fill, 0, 2);
        }
        $stroke = $class->stroke_color;

        if ($stroke == 'trans') {
            $stroke = '00000000';
        } else {
            $stroke = str_replace('#', '', $stroke);
            $stroke = str_pad($stroke, 6, "0", STR_PAD_LEFT);
            $stroke = ("FF" . substr($stroke, 4, 2) . substr($stroke, 2, 2)) . substr($stroke, 0, 2);
        }

        switch ($geomType) {
            case GeomTypes::LINE:
                fwrite($kmlfh, "\t<LineStyle>\n");
                fwrite($kmlfh, "\t\t<color>$stroke</color>\n");
                fwrite($kmlfh, "\t\t<width>" . $class->symbol_size . "</width>\n");
                fwrite($kmlfh, "\t</LineStyle>\n");
                break;
            case GeomTypes::POINT:
                fwrite($kmlfh, "\t<IconStyle>\n");
                fwrite($kmlfh, "\t\t<colorMode>normal</colorMode>\n");
                fwrite($kmlfh, "\t\t<scale>1</scale>\n");
                fwrite($kmlfh, "\t\t<Icon><href>files/{$class->id}.png</href></Icon>\n");
                fwrite($kmlfh, "\t</IconStyle>\n");
                break;
            case GeomTypes::POLYGON:
                fwrite($kmlfh, "\t<LineStyle>\n");
                fwrite($kmlfh, "\t\t<color>$stroke</color>\n");
                fwrite($kmlfh, "\t\t<width>" . $class->symbol_size . "</width>\n");
                fwrite($kmlfh, "\t</LineStyle>\n");
                fwrite($kmlfh, "\t<PolyStyle>\n");
                fwrite($kmlfh, "\t\t<color>$fill</color>\n");
                fwrite($kmlfh, "\t\t<colorMode>normal</colorMode>\n");
                fwrite($kmlfh, "\t</PolyStyle>\n");
                break;
        }
        fwrite($kmlfh, "</Style>\n");
    }
    
    while (!$records->EOF) {
        $record = $records->fields;
        $records->MoveNext();

        // if the record lacks a kml_geom attribute, this must have been from a non-spatial POINT source so we compose the KML ourselves
        if (!isset($record['kml_geom']) && ($layer->type == LayerTypes::ODBC))
            $record['kml_geom'] = sprintf("<Point><coordinates>%f,%f</coordinates></Point>", $record[$loncolumn], $record[$latcolumn]);

        // generate a HTML table containing the point's attributes
        $description = "" . $tooltip;
        $extendedData = "";
        $style = "";
        $id = '';
        foreach ($record as $k => $v) {
            if (stripos($v, 'a href')) {
                $v = str_replace('=_blank', '="_blank"', $v);
            }

            $description = str_replace('[' . $k . ']', $v, $description);
            if (in_array($k, array(
                'gid',
                'the_geom',
                'wkt_geom',
                'kml_geom'
            )))
                continue;

            $extendedData .= '<Data name="' . $k . '" ><value>' . $v . ' </value></Data>';
            // $description .= sprintf("<tr><th>%s</th><td>%s</td></tr>\n", $k, htmlentities($v) );
        }

        foreach ($classes as $c) {

            /* @var  $c ColorSchemeEntry */
            if ($c->RecordMatches($record)) {
                $id = $c->id;
                break;
            }
        }
        if ($id != '') {
            $style = "<styleUrl>#sl_def_" . $id . "</styleUrl>";
        }

        // $description .= "</table>\n";
        $desciption = htmlentities($description);
        $labelField = $layer->labelitem ? $layer->labelitem : 'name';
        $name = isset($record[$labelField]) ? $record[$labelField] : "Record # {$record['gid']}";

        fwrite($kmlfh, "    <Placemark>\n");
        fwrite($kmlfh, "       <name>{$name}</name>\n");
        fwrite($kmlfh, "       <description><![CDATA[ {$description}]]></description>\n");
        fwrite($kmlfh, "       <ExtendedData>\n$extendedData}\n</ExtendedData>");
        fwrite($kmlfh, "       {$record['kml_geom']}\n");
        fwrite($kmlfh, "       $style\n");
        fwrite($kmlfh, "    </Placemark>\n");
    }
    fwrite($kmlfh, "  </Folder>\n");
    fwrite($kmlfh, "</kml>");

    // done generating the KMl data; compress it into ZIP/KMZ format
    fclose($kmlfh);
    // readfile($kmlfile);
    chdir($path);
    $cmd = "zip -r $zipfile *";
    shell_exec($cmd);
    print_download_http_headers($filename);
    // all done; send the file contenmt
    readfile($zipfile);
    unlink($zipfile);
    chdir($path . '..');
    shell_exec("rm -rf $path");
}
