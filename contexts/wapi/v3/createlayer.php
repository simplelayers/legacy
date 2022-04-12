<?php

/**
 * Create a new Layer, owned by you.
 *
 * Parameters:
 *
 * name -- The name for the new layer.
 *
 * type -- The numeric code for the layer type. See the $LAYERTYPES constants.
 *
 * description -- A text description for the layer. Optional.
 *
 * fields_text -- A comma-joined list of field names. The given fields will be created as text fields.
 *
 * fields_integer -- A comma-joined list of field names. The given fields will be created as integer fields.
 *
 * fields_float -- A comma-joined list of field names. The given fields will be created as float fields.
 *
 * fields_boolean -- A comma-joined list of field names. The given fields will be created as boolean fields.
 *
 * Returns:
 *
 * XML representing the OK/NO status of the request.
 * Note that the ok value, if it's OK, will be the ID# of the new Layer, not simply the word "yes"
 * {@example docs/examples/wapi_ok.txt}
 * {@example docs/examples/wapi_no.txt}
 *
 * @package WebAPI
 */
/**
 *
 * @ignore
 *
 */
function _config_createlayer()
{
    $config = Array();
    // Start config
    $config["header"] = false;
    $config["footer"] = false;
    $config["customHeaders"] = true;
    // Stop config
    return $config;
}

function _headers_createlayer()
{
    header('Content-type: text/xml');
}

function _dispatch_wapicreatelayer($template, $args)
{
    $world = $args['world'];
    $user = $args['user'];
    
    // if they tried to trick us with a bad geometry type, trick them by defaulting to point
    global $VECTORGEOMTYPES;
    if (! $VECTORGEOMTYPES[$_REQUEST['type']])
        $_REQUEST['type'] = GeomTypes::POINT;
    $geomname = strtoupper($VECTORGEOMTYPES[$_REQUEST['type']]);
    if ($geomname == 'LINE')
        $geomname = 'LINESTRING';
        
        // create the Layer
    $_REQUEST['name'] = $user->uniqueLayerName($_REQUEST['name']);
    $layer = $user->createLayer($_REQUEST['name'], (int) $_REQUEST['type']);
    $layer->description = $_REQUEST['description'];
    $layer->colorscheme->setSchemeToSingle();
    
    // create the DB storage for the Layer
    if ($_REQUEST['type'] == LayerTypes::VECTOR) {
        $world->db->Execute("CREATE TABLE {$layer->url} (gid serial)");
        $world->db->Execute("SELECT AddGeometryColumn('','{$layer->url}','the_geom',4326,'$geomname',2)");
    }
    
    // create the additional columns specified, after sanitizing them of course
    foreach (array(
        'fields_text',
        'fields_integer',
        'fields_float',
        'fields_boolean'
    ) as $k)
        $_REQUEST[$k] = preg_replace('/[^\w\,]/', '', $_REQUEST[$k]);
    foreach (@explode(',', $_REQUEST['fields_text']) as $colname)
        $layer->addAttribute($colname, DataTypes::TEXT);
    foreach (@explode(',', $_REQUEST['fields_integer']) as $colname)
        $layer->addAttribute($colname, DataTypes::INTEGER);
    foreach (@explode(',', $_REQUEST['fields_float']) as $colname)
        $layer->addAttribute($colname, DataTypes::FLOAT);
    foreach (@explode(',', $_REQUEST['fields_boolean']) as $colname)
        $layer->addAttribute($colname, DataTypes::BOOLEAN);
        
        // all set
    $template->assign('ok', $layer->id);
    $template->assign('message', "Layer created with ID# {$layer->id}.");
    $template->display('wapi/okno.tpl');
}
?>
