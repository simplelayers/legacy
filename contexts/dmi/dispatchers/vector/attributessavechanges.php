<?php

/**
 * Process the vectorattributes form, and drop the specified column.
 * @package Dispatchers
 */

/**
 */
function _config_attributessavechanges() {
    $config = Array();
    // Start config
    #$config["header"] = false;
    #$config["footer"] = false;
    // Stop config
    return $config;
}

function _dispatch_attributessavechanges($template, $args) {
 
    $world = System::Get();
    $user = SimpleSession::Get()->GetUser();


// load the layer and verify their access
    $layer = Layer::GetLayer($_REQUEST['id']);

    if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
        print javascriptalert('You do not have permission to edit that Layer.');
        return print redirect('layer.list');
    }

// drop the specified column(s) and send them back to the attributes page
    $data = RequestUtil::GetJSONParam('data');

    $layerInfo = Array();
    $i = 0;
    $dropped = array();
    $hadUnknownType = false;
    foreach ($data as $row) {
        if ($row->name == '') {
            if ($row->display !== '') {
                $row->name = $row->display;
            }
        }
        
        if ($row->name == "")
            continue;
        if ($row->startName == '_newColumnName_') {
            if (!$row->drop) {

                $colname = $layer->addAttribute($row->name, $row->type);
                if ($colname)
                    $row->name = $colname;
            }
        } else {
            if ($row->drop) {
                $layer->dropAttribute($row->name);
                continue;
                continue;
            }
            if ($row->name != $row->startName) {
                $layer->renameAttribute($row->startName, $row->name);
            }
            if(is_null($row->type)) {
                $hadUnknownType = true;
            }
            if(!isset($row->type)) {
                $row->type = $attInfo[$colname] = $type;
            }
            if (isset($row->type)) {

                if ($row->type != $row->startType) {
                    $type = $row->type;
                    if ($type == 'url')
                        $type = 'cg_url';
                    $layer->ChangeFieldType($row->name, $type);
                }
            }
        }
         $attInfo = $layer->getAttributesVerbose(false);
   
        $info = array('name' => strtolower($row->name), 'type'=>$row->type, 'display' => $row->display, 'visible' => ($row->visible ? true : false), 'searchable' => ($row->searchable ? true : false), 'z' => $i--);
        #if(is_null($info['type'])) { $info['type'] = $row->startType; }
        if(is_null($info['type'])) { $info['type'] = $attInfo[$row->name]['type'];  }
        $layerInfo[] = $info;
        
    }
    #if($hadUnknownType === true) {
        #$layer->field_info = null;
        #$layer->field_info = $layer->getAttributesVerbose(false);
    #} else {
        $layer->field_info = $layerInfo;
    #}
    $layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.attributes&id=" . $layer->id, 8);
    die();
}

?>
