<?php

/**
 * Process the importcsv1 form, and import the uploaded CSV data.
 * @package Dispatchers
 */
/**
 */
function _config_csv2()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_csv2($template, $args)
{
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
    
    // this function is used to correct the upload's column names into proper names
    function fixcolname($name)
    {
        $name = preg_replace('/\W/', '_', strtolower(trim($name))); // make it lowercase and clean
        if (! $name)
            $name = 'f_' . substr(md5(microtime()), 0, 5); // no name? make one up
        return $name;
    }
    
    // create a temporary directory
    // If the uploaded file ends in .zip, unpack it into that directory.
    // Otherwise, move the uploaded file (which we assume to be csv) into that directory.
    // Either way, we get a directory with CSV files.
    $directory = $world->config['tempdir'] . '/' . md5(microtime() . mt_rand());
    
    if (substr(strtolower($_FILES['source']['name']), - 4) == '.zip') {
        shell_exec(escapeshellcmd("unzip -j -o {$_FILES['source']['tmp_name']} -d {$directory}"));
    } else {
        $filename = basename(str_replace('\\', '/', $_FILES['source']['name']));
        move_uploaded_file($_FILES['source']['tmp_name'], "$directory/$filename");
    }
    $files_to_process = array_filter(glob("$directory/*"), 'is_file');
    
    // / Now go through them and give 'em the business...
    $import_ok = array();
    $import_err = array();
    foreach ($files_to_process as $thisfile) {
        // correct the newlines, by replacing any sequence of CRLF with a proper \n
        $str = file_get_contents($thisfile);
        $str = preg_replace("/[\r\n\015]+/", "\r\n", $str);
        file_put_contents($thisfile, $str);
        
        // open the file for handling
        $file = fopen($thisfile, 'r');
        $thisfilename = basename($thisfile, '.csv');
        $thisfilename = str_replace('.*', '', $thisfilename);
        ping("Processing $thisfilename ... <br/>");
        
        // grab the first line and see whether it takes commas or tabs
        $line = fgets($file);
        rewind($file);
        if (strpos($line, "\t"))
            $delimiter = "\t";
        elseif (strpos($line, ","))
            $delimiter = ',';
            
            // grab the first row and analyze it for column names
        $columns = array_map('fixcolname', fgetcsv($file, 0, $delimiter));
        
        // create the layer entry, and "manually" create the point geometry column
        ping("Creating table");
        $name = $user->uniqueLayerName($_REQUEST['name'] . ' ' . $thisfilename);
        $layer = $user->createLayer($name, LayerTypes::VECTOR);
        $world->db->Execute("CREATE TABLE {$layer->url} (gid serial)");
        $world->db->Execute("SELECT AddGeometryColumn('','{$layer->url}','the_geom',4326,'POINT',2)");
        ping(".");
        // create the columns named in the upload file, attempting to guess their data type
        for ($i = 0; $i < sizeof($columns); $i ++) {
            rewind($file);
            fgets($file);
            $colname = $columns[$i];
            $type = 'INTEGER';
            while (true) {
                $row = fgetcsv($file, 0, $delimiter);
                if (! $row)
                    break;
                if (! preg_match('/^\-?\d*\.?\d*+$/', $row[$i])) {
                    $type = 'TEXT';
                    break;
                } elseif (strpos($row[$i], '.') !== false)
                    $type = 'FLOAT';
            }
            $world->db->Execute("ALTER TABLE {$layer->url} ADD COLUMN \"$colname\" $type");
            ping(".");
        }
        ping("done<br/>\n");
        
        // go through the raw rows, and generate SQL to import each
        rewind($file);
        fgets($file);
        ping("Importing");
        $rs = $world->db->Execute("SELECT * FROM {$layer->url} WHERE gid=0");
        $i = 0;
        while (true) {
            // fetch a CSV row, and make a assocarray using the column names; then use GetInsertSQL() to do the work
            // note that at this point we are not inserting spatial data, only raw latitude and longitude
            $row = fgetcsv($file, 0, $delimiter);
            if (! $row)
                break;
            $content = array();
            for ($q = 0; $q < sizeof($columns); $q ++)
                $content[$columns[$q]] = $row[$q];
            $sql = $world->db->GetInsertSQL($rs, $content);
            $world->db->Execute($sql);
            $i ++;
            if ($i % 50 == 0)
                ping(".");
        }
        ping("done<br/>\n");
        
        // go through and set the geometry from the latitude and longitude fields in one run
        // since the projection is given as a PROJ or WKT string, and not a EPSG code, we have to create a custom SRID
        ping("Generating spatial information");
        $srid = 10000000 + mt_rand(10, 10000000);
        $world->db->Execute("INSERT INTO spatial_ref_sys (srid,auth_name,auth_srid,proj4text) VALUES (?,?,?,?)", array(
            $srid,
            'CUSTOM',
            $srid,
            $_REQUEST['projection']
        ));
        print(".");
        if (in_array('latitude', $columns) and in_array('longitude', $columns)) {
            $sql = "UPDATE {$layer->url} SET the_geom=Transform(ST_GeometryFromText('POINT('||longitude||' '||latitude||')',$srid),4326)";
        } else {
            $sql = "UPDATE {$layer->url} SET the_geom=ST_GeometryFromText('POINT(0 0)',4326)";
        }
        ping(".");
        $world->db->Execute($sql);
        ping(".");
        $world->db->Execute("DELETE FROM spatial_ref_sys WHERE srid=?", array(
            $srid
        ));
        ping("done<br/>\n");
        
        // and create the indexes
        ping("Indexing.");
        $world->db->Execute("CREATE INDEX {$layer->url}_index_the_geom ON $layer->url USING GIST (the_geom)");
        ping(".");
        $world->db->Execute("CREATE INDEX {$layer->url}_index_oid ON $layer->url (oid)");
        ping(".");
        $world->db->Execute("ALTER TABLE {$layer->url} ADD PRIMARY KEY (gid)");
        ping("done<br/>\n");
        ping("optimizing...<br/>\n");
        $layer->optimize();
        $layer->fixDBPermissions();
        
        // add it to the lisst of successful layers
        array_push($import_ok, $thisfilename);
        ping("done with $thisfilename<br/>\n");
        $reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
        $report = new Report($args['world'], $reportEntry);
        $report->commit();
    }
    
    // send them to their layer list
    if ($import_ok) {
        $message = "The following layers were imported successfully:\n" . implode("\n", $import_ok) . "\n";
        print javascriptalert($message);
    }
    if ($import_err) {
        $message = "The following layers WERE NOT imported:\n" . implode("\n", $import_err) . "\n";
        print javascriptalert($message);
    }
    return print redirect('layer.list');
}
?>
