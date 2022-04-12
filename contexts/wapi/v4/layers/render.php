<?php

use utils\ParamUtil;
use model\Permissions;
use utils\tiles\TileInfo;
use model\mapping\PixoSpatial;
use model\mapping\Renderer;
use reporting\Transaction;

/**
 * Viewer: Take a list of layers, and render all of them, to make a "screenshot" of the current map view.
 * Render a "screenshot" of the current map view: bounding box, layers, opacities, etc.
 *
 * Parameters:
 *
 * project -- The unique ID# of the Project.
 *
 * bbox -- A bounding box, in comma-separated format, e.g. "12.34,56.7,89.0,123.4"
 *
 * width -- The width of the image to generate.
 *
 * height -- The height of the image to generate.
 *
 * filename -- The filename that will be suggested for the downloaded image, minus the extension. The file extension will be added automatically.
 *
 * geotiff -- By default, the download is in JPEG format. However, if this parameter is set then the download will instead be a georeferenced TIFF (GeoTIFF) suitable for use with other GIS software.
 *
 * layers -- A comma-joined list of layer IDs. The layers are rendered in reverse order; the first layer listed is the topost layer, the last layer is the basemap.
 *
 * opacities -- A comma-joined list of opacities, corresponding to the list of layers. Each opacity ranges from 0 to 1.
 *
 * labels -- A comma-joined list of 1/0 corresponding to the list of layers. Each 1 or 0 indicates whether labels should be generated when the layer is rendered.
 *
 * An example of specifying a list of layers, opacities, and labels:
 * {@example docs/examples/viewerscreenshot.txt}
 *
 * Return:
 *
 * A binary stream, being an image in JPEG or GeoTIFF format. If access is denied, a param string will be returned that says "&status=NO&"
 *
 * @package ViewerDispatchers
 */

/**
 *
 * @ignore
 *
 */
function _exec() {

    // function _dispatch_rendermap($request,$world,$user,$template,$project,$embedded,$permission) {
    $wapi = System::GetWapi();
    $params = WAPI::GetParams();
    $session = SimpleSession::Get();
    $user = $session->GetUser();

    // Attempt to get project-layer or layer id.
    // ParamUtil::RequiresOne($params,'pLayerId','layerId');
    $layer = $wapi->RequireALayer(null, 'player_id', 'layer_id');

    // Get required parameters
    list ($width, $height) = ParamUtil::Requires($params, 'width', 'height');
    $bbox = ParamUtil::Get($params, 'bbox');

    if ($bbox == 'layer') {
        $bbox = $layer->getExtent();
    } elseif ($bbox == 'features') {
        $features = ParamUtil::RequiresOne($params, 'features');
        $bbox = array_values($layer->GetBounds($features));
    }

    $params = WAPI::SetParam('bbox', $bbox);

    $pixo = new PixoSpatial(array(
        - 180,
        - 90,
        180,
        90
            ), 200, 100);
    $pixo->Resize($width, $height);


    if ($bbox[0] == $bbox[2]) {
        $pixo->CenterROI($bbox[0], $bbox[1]);

        $pixo->FitToLevel(17);
    } else {
        $pixo->MoveToROI($bbox, 10);
    }

    $renderer = new Renderer($pixo);
    $baseLayer = $resultSetLayer = $unResultSetLayer = null;
    $addLayer = null;

    // perform additional permission checks and setup baseLayer, resultSetLayer, and unResultSetLayer.
    if (is_a($layer, 'ProjectLayer')) {

        $project = $layer->project;
        if (empty($_SESSION[$project->id . "lastNav"]) || $_SESSION[$project->id . "lastNavTime"] <= time() - 30)
            $_SESSION[$project->id . "lastNav"] = 0;
        list ($embeddable, $bpermission) = $project->checkBrowserPermission($user->id, $_REQUEST, $_SERVER);
        $permission = max($embeddable, $bpermission);
        // if(!$session->isEmbedded) $bpermission = $bpermission;

        if ($permission < Permissions::VIEW) {
            WAPI::SendBlankImage("Need view or better permission");
            die();
        }

        $isCollection = $layer->layer->type == LayerTypes::COLLECTION;

        $player = $layer;
        $plid = $player->id;
        $layer = $player->layer;
        $baseLayer = ProjectLayer::Get($plid);
        $resultSetLayer = ProjectLayer::Get($plid);
        $unResultSetLayer = ProjectLayer::Get($plid);
        $addLayer = $player;
    } else {
        $permission = $layer->getPermissionById($user->id);
        if ($permission < Permissions::VIEW) {
            WAPI::SendBlankImage('Need view or better privilege');
        }

        $isCollection = $layer->type == LayerTypes::COLLECTION;

        $baseLayer = $layer;
        $resultSet = $baseLayer;
        $baseLayer = Layer::GetLayer($layer->id, true);
        $resultSetLayer = Layer::GetLayer($layer->id, true);
        $unResultSetLayer = Layer::GetLayer($layer->id, true);
        $addLayer = $layer;
        $project = null;
    }

    // get optional parameters

    list ($uncolor, $gids, $unnormal) = ParamUtil::ListValues($params, 'uncolor', 'gids', 'unnormal');
    if ($gids == '')
        $gids = null;
    $params['gids'] = $gids;
    // Track usage where appropriate.
    $sys = System::Get();
    if ((is_null($uncolor) && is_null($gids)) || !is_null($unnormal)) {
        if (!is_null($project)) {
            
            Transaction::add($sys, $addLayer, $project, $user, $_SESSION[$project->id . "lastNav"]);
        }
    }
    if (isset($gids)) {
        $addId = is_a($addLayer, "ProjectLayer") ? $addLayer->layer->id : $addLayer->id;
        if ($addId != $resultSetLayer->id) {
            Transaction::add($sys, $resultSetLayer, $project, $user, $_SESSION[$project->id . "lastNav"]);
        }
    }
    // Determine if we're attempting to get a tile
    list ($x, $y, $z) = ParamUtil::ListValues($params, 'x', 'y', 'z');
    $isTile = false;
    if (!is_null($x) || !is_null($y) || !is_null($z)) {
        $isTile = true;
    }

    if ($isTile) {
        $renderer->RenderTile($layer, $x, $y, $z);
        return;
    }
    $mode = ParamUtil::Get($params, 'maptype', Mapper::$MODE_LATLON);

    if ($mode === Mapper::$MODE_WEB) {
        $renderer->RenderWeb($addLayer, $params, $baseLayer, $resultSetLayer, $unResultSetLayer);
    } else {
        $renderer->RenderLatLon($addLayer, $params, $baseLayer, $resultSetLayer, $unResultSetLayer);
    }
}

?>