<?php

/**
 * The form for creating a new relational DB data layer.
 * @package Dispatchers
 */
use utils\PageUtil;
use model\License;
use model\Permissions;
use views\LayerUserViews;

/**
 */
function _config_createrelational1() {
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_createrelational1($template, $args, $org, $pageArgs) {
    $user = SimpleSession::Get()->GetUser();
    $world = $args['world'];
    $layer = RequestUtil::Get('layer');
    if ($layer)
        $world->getLayerById($layer);

    $pageArgs['pageSubnav'] = 'data';
    $pageArgs['pageTitle'] = 'Data - New Relational Layer';
    if ($layer)
        $pageArgs['layerId'] = $layer->id;
    PageUtil::SetPageArgs($pageArgs, $template);

    if ($pageArgs['reachedLayerLimit'] == 'true') {
        print javascriptalert('Your organization has reached its limit of ' . $pageArgs['max_layers'] . ' layers.');
        return print redirect('layer.list');
    }
    if (!Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:', Permissions::CREATE)) {
        print javascriptalert('You do not have permission to create new layers.');
        return print redirect('layer.list');
    }

// are they allowed to be doing this at all?
    /* if ($user->accounttype < AccountTypes::GOLD) {
      print javascriptalert('You must upgrade your account in order to create relational layers.');
      return print redirect('project.list');
      } */
    if ($user->community && count($user->listLayers()) >= 3) {
        print javascriptalert('You cannot create more than 3 layers with a community account.');
        return print redirect('layer.list');
    }


    $config = array();

    $views = new LayerUserViews($user->id);

    if ($layer) {
// fetch the existing layer and column choices
// this is stored in the 'url' field but is not accessible via $layer->url because of the ORM
        $config = $layer->url; // $world->db->Execute('SELECT url FROM layers WHERE id=?', array($layer->id) )->fields['url'];
        $config = unserialize($config);
    } else {
        $config = array('table1' => '', 'table2' => '');
    }
    $template->assign('config', $config);
// get the list of all the user's vector layers; these are candidate tables for the view
    $tables = array('' => '');
    foreach ($user->listLayers() as $l) {
        /* @var $l \Layer */
        if ($l->type != LayerTypes::VECTOR)
            continue;

        if ($l->getPermissionById($user->id) < AccessLevels::COPY)
            continue;
        if (trim($l->name) == '')
            continue;
        $tables[$l->id] = $l->name;
    }
    foreach ($views->GetByOwner(-1, AccessLevels::COPY) as $l) {
        if ($l['type'] != LayerTypes::VECTOR)
            continue;
        if (trim($l['name']) == '')
            continue;
        $tables[$l['id']] = $l['name'] . ' owned by ' . $l['owner_name'];
    }
    $tables2 = array('' => '');
    foreach ($user->listLayers() as $l) {
        /* @var $l \Layer */
        if (!$l->getHasRecords())
            continue;
        if (trim($l->name) == '')
            continue;
        $tables2[$l->id] = $l->name;
    }

    $template->assign('tables', $tables);
    $template->assign('tables2', $tables2);
// some blank arrays to start the column selectors; they populate themselves separately
    $template->assign('columns1', array());
    $template->assign('columns2', array());

// ready!
    $template->display('layer/createrelational1.tpl');
}

?>
