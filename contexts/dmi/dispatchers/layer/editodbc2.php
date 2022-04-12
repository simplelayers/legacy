<?php

/**
 * Process the layereditodbc1 form, to save their changes to the layer information.
 * @package Dispatchers
 */
/**
 */
function _config_editodbc2()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_editodbc2($template, $args)
{
    $user = SimpleSession::Get()->GetUser();
    $world = System::Get();
    
    if (! Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::VIEW)) {
        javascriptalert('You are not allowed to view layer details.');
        redirect('layer.list');
    }
    
    // load the layer and verify their access
    $layer = $world->getLayerById($_REQUEST['id']);
    if (! $layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
        print javascriptalert('You do not have permission to edit that Layer.');
        return print redirect('layer.list');
    }
    
    // are they allowed to be doing this at all?
    /*
     * if ($layer->owner->id != $user->id and $user->accounttype < AccountTypes::PLATINUM) {
     * print javascriptalert('You must have at least Platinum level access to use ODBC layers.');
     * return print redirect("layer.list");
     * }
     */
    
    // handle the simple attributes
    $layer->name = $user->uniqueLayerName($_REQUEST['name'], $layer);
    $layer->description = $_REQUEST['description'];
    $layer->tags = $_REQUEST['tags'];
    
    // /// sanitize and compile their new ODBC information, save it as their URL
    $ports = System::GetODBCPorts();
    $driver = $_POST['servertype'];
    if (! array_key_exists($driver, $ports))
        return print "Invalid driver $driver";
    $table = preg_replace('/[^\w\.]/', '', $_POST['table']);
    $latcolumn = preg_replace('/\W/', '', $_POST['latcolumn']);
    $loncolumn = preg_replace('/\W/', '', $_POST['loncolumn']);
    $odbchost = preg_replace('/[^\w\.\-]/', '', $_POST['odbchost']);
    $odbcport = (int) $_POST['odbcport'];
    $odbcbase = preg_replace('/[^\w\.\-]/', '', $_POST['odbcbase']);
    $odbcuser = preg_replace('/[\r\n]/', '', $_POST['odbcuser']);
    $odbcpass = preg_replace('/[\r\n]/', '', $_POST['odbcpass']);
    $odbcinfo = array(
        'driver' => $driver,
        'odbchost' => $odbchost,
        'odbcport' => $odbcport,
        'odbcuser' => $odbcuser,
        'odbcpass' => $odbcpass,
        'odbcbase' => $odbcbase,
        'table' => $table,
        'latcolumn' => $latcolumn,
        'loncolumn' => $loncolumn
    );
    $layerid = $_REQUEST['id'];
    // /// validate their connection
    
    // connect
    $odbcinfo = new stdClass();
    $odbcinfo->driver = $driver;
    $odbcinfo->odbchost = $odbchost;
    $odbcinfo->odbcport = $odbcport;
    $odbcinfo->odbcuser = $odbcuser;
    $odbcinfo->odbcpass = $odbcpass;
    $odbcinfo->odbcbase = $odbcbase;
    $odbcinfo->table = $table;
    $odbcinfo->latcolumn = $latcolumn;
    $odbcinfo->loncolumn = $loncolumn;
    list ($odbc, $dbcini, $freetdsconf) = $world->connectToODBC($odbcinfo);
    if (! $odbc)
        return print "Failed: Unable to connect. Check the server type, hostname, username, password, and database name";
    print "Connected to database.<br />\n";
    
    // see if there are any rows in the table
    $count = odbc_exec($odbc, "SELECT count(*) AS count FROM $table");
    if (! $count)
        return print "Failed: Connected to the database, but did not find table '$table'";
    $count = odbc_result($count, 'count');
    if (! $count)
        return print "Failed: Connected to the database, but table '$table' is apparently empty";
    print "Found $count records in table '$table'<br />\n";
    
    // make sure the latitude and longitude columns exist, by calculating their bbox!
    $bbox = odbc_exec($odbc, "SELECT min($latcolumn) AS s, max($latcolumn) AS n, min($loncolumn) AS w, max($loncolumn) AS e FROM $table");
    if (! $bbox)
        return print "Failed: Check the latitude and longitude columns";
    $n = odbc_result($bbox, 'n');
    $s = odbc_result($bbox, 's');
    $e = odbc_result($bbox, 'e');
    $w = odbc_result($bbox, 'w');
    print "Longitude range: $w to $e <br/>\n";
    print "Latitude range: $s to $n <br/>\n";
    
    // all set; close the ODBC connection
    print "Tests OK.<br/>\n";
    odbc_close($odbc);
    
    // fine, go ahead and save it
    $layer->url = json_encode($odbcinfo);
    
    // done -- keep them on the details page or send them to their layerbookmark list, depending
    // on whether they own the layer they just edited
    $layer->owner->notify($user->id, "edited layer:", $layer->name, $layer->id, "./?do=layer.info&id=" . $layer->id, 5);
    print redirect($layer->owner->id == $user->id ? 'layer.editodbc1&id=' . $layerid : 'layer.bookmarks');
}
?>
