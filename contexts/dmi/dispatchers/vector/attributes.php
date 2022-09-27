<?php

use utils\PageUtil;
use model\Permissions;
use utils\ParamUtil;
use utils\RelationUtil;

/**
 * A list of the vector layer's columns/attributes, and widgets for adding/deleting columns.
 * @package Dispatchers
 */

/**
 */
function _config_attributes() {
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_attributes($template, $args, $org, $pageArgs) {
    $world = $args['world'];
    $user = $args['user'];


// load the layer and verify their access
    $layer = Layer::GetLayer(ParamUtil::Get($args, 'id'));

    $layer->ValidateColumnNames();

    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['layerId'] = $layer->id;
    $pageArgs['pageTitle'] = 'Data - Attributes for ' . $layer->name;

    if (!Permissions::HasPerm($pageArgs['permissions'], ':Layers:Attributes:', Permissions::EDIT)) {
        print javascriptalert('Your  do not have permission to edit layer attributes..');
        return print redirect('layer.list');
    }

    PageUtil::SetPageArgs($pageArgs, $template);
    if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
        print javascriptalert('You do not have permission to edit that Layer.');
        return print redirect('layer.list');
    }

    if (ParamUtil::Get($args, 'reset', false)) {
        $layer->field_info = null;
        RelationUtil::ResetRelations($layer);
    }

    $template->assign('layer', $layer);
    $template->assign('isRelational', $layer->type == LayerTypes::RELATIONAL);
// get the list of columns/attributes that already exist in the layer,
// and the column types that can be created

    $types = DataTypes::GetTypes();

    $template->assign('columntypes', $types);

    $columns = $layer->field_info; // $layer->getAttributesVerbose(false,true);

    function conversionFilter($var) {
        $conversions = DataTypes::GetTypes();// array('text', 'url');
        if (in_array($var, $conversions))
            return true;
        return false;
    }
    $conversions = array_unique(array_values(DataTypes::GetAliases()));
    $conversions[] = 'url';
    
    // $conversions = DataTypes::GetAliases();// array("text", 'url');


    $template->assign('conversions', $conversions);
    $filterColumns = array_filter($columns, 'conversionFilter');
    unset($filterColumns['url']);
    $template->assign('alterColumns', $filterColumns);
    unset($columns['gid']);
    foreach ($columns as &$row) {
        if($row["type"] === 'string') {
            $row['type'] = 'text';
            $row['requires'] = 'text';
        }
        if (strpos($row["requires"], 'text') !== false) {
            $row["requires"] = 'text';
        }
    }
    
    $template->assign('columns', $columns);


// and the template, as always
    $template->display('vector/attributes.tpl');
}

?>
