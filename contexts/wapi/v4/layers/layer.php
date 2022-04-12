<?php

use utils\ParamUtil;
use model\Permissions;
use utils\LayerUtil;
use utils\PageUtil;
use utils\ResponseUtil;

function _exec() {
    $args = WAPI::GetParams();
    $goto = ParamUtil::Get($args, 'goto');

    switch (ParamUtil::Get($args, 'action')) {
        case 'load':
            $wapi = \System::GetWapi();
            $user = SimpleSession::get()->GetUser();
            $target = ParamUtil::Get($args, 'target', 'player');

            $wrapper = array();
            $layerId = '';
            $playerId = '';
            $player = null;
            $layer = null;
            $target = ParamUtil::Get($args, 'target', 'player');

            $fromDefault = false;
            $isProjectLayer = false;
            $l = $wapi->RequireALayer();
            if (is_a($l, ProjectLayer::class)) {
                $layer = $l->layer;
                $player = $l;
                $isProjectLayer = true;
            } else {
                $player = null;
                $layer = $l;
                if ($l->name === null) {
                    throw new \Exception('Cannot load layer:Invalid layer id');
                }
                $isProjectLayer = false;
                if ($target !== 'player') {
                    if (ParamUtil::Has($args, 'plid')) {
                        $player = $wapi->RequireProjectLayer();
                    }

                    $fromDefault = !is_null($player);
                }
            }

            if (!is_null($player)) {
                $permission = $wapi->CheckProjectPermission($player->project, true);
                if ($permission < AccessLevels::EDIT) {
                    WAPI::SendSimpleResponse(null, $format, 'You do not have permission to view this map');
                    die();
                }
            }

            if ($layer->getPermissionById($user->id) < AccessLevels::READ) {
                WAPI::SendSimpleResponse(null, $format, 'You do not have permission to view this layer');
                die();
            }

            $target = $isProjectLayer ? $player : $layer;
            if ($isProjectLayer) {
                $playerId = $player->id;
                $layerId = $layer->id;
                $wrapper['project_layer'] = $layer;
                $formatter = new ProjectLayerFormatter(System::Get(), $user);
            } else {
                $layerId = $layer->id;
                $formatter = new LayerFormatter(System::Get(), $user);
            }

            $formatOptions = intval(ParamUtil::Get($args, 'format_options', $formatter->max));

            if ($fromDefault) {
                $formatter = new ProjectLayerFormatter(System::Get(), $user);
                $formatter->LoadFromDefaults($player, $layer, $formatOptions);
                $target = $player;
            }
            $body = (WAPI::FORMAT_JSON === $wapi->format) ? 'layer' : '';
            $responseUtil = new ResponseUtil(ParamUtil::Get($args, 'format'), 'layers/layer/load');

            $responseUtil->SetProps(array(
                'lid' => $layerId,
                'plid' => $playerId
            ));

            $responseUtil->StartResponse(true);
            $responseUtil->BeginBody($body);
            $formatOptions = intval(ParamUtil::Get($args, 'format_options', $formatter->max));

            switch ($wapi->format) {
                case WAPI::FORMAT_JSON:

                    $formatter->WriteJSON($target, $formatOptions);
                    break;
                case WAPI::FORMAT_XML:

                    $formatter->WriteXML($target, $formatOptions);
                    break;
            }
            $responseUtil->EndBody($body);
            $responseUtil->EndResponse('ok');
            break;
        case 'delete':

            $layerId = ParamUtil::RequiresOne($args, 'layerId');
            $layer = $layer = System::Get()->getLayerById($layerId);

            if ($layer) {
                $layer->delete();
                $message = 'Layer deleted';
            } else {
                $message = 'Layer does not exist.';
            }

            $layer = System::Get()->getLayerById($layerId);
            if ($layer) {
                $message = 'There was a problem deleting the layer';
            }

            if ($goto) {
                javascriptalert($message);
                if (substr($goto, 0, 2) == 'do')
                    return redirect('?' . $goto);
                return redirect($goto);
            } else {
                WAPI::SendSimpleResponse($message);
            }
            break;
        case 'classification_reset':
            $playerId = ParamUtil::RequiresOne($args, 'playerId');
            $player = ProjectLayer::Get($playerId);
            $class = $player->colorscheme;
            $class->clearScheme();
            WAPI::SendSimpleResponse('classification reset');
            break;
        case 'feature_wkt':

            $layer = System::GetWapi()->RequireLayerId(AccessLevels::EDIT, 'layerId', LayerTypes::VECTOR, true);
            $featureId = ParamUtil::Get($args, 'featureId');

            if (!is_null($featureId)) {
                $wkt = ParamUtil::Get($args, 'wkt');

                if (!is_null($wkt)) {
                    if (is_null($featureId)) {
                        $fatureId = $layer->insertRecord(array(
                            'wkt_geom' => $wkt
                        ));
                    }
                }
                $layer->updateRecordById($featureId, array('wkt_geom' => $wkt));
                $record = $layer->getRecordById($featureId);
                $wkt = $record['wkt_geom'];


                WAPI::SendSimpleResponse(array(
                    'featureId' => $featureId,
                    'wkt' => $wkt
                ));
            } else {
                $wkt = ParamUtil::Get($args, 'wkt');
                //System::GetDB(System::DB_ACCOUNT_SU)->debug = true;
                $record = $layer->insertRecord(array(
                    'wkt_geom' => $wkt
                ));
                #$record = $layer->getRecordById($gid);
                $wkt = $record['wkt_geom'];
                WAPI::SendSimpleResponse(array(
                    'featureId' => $record['gid'],
                    'wkt' => $wkt
                ));
            }
            break;
        case 'features_wkt':

            $layer = System::GetWapi()->RequireLayerId(AccessLevels::EDIT, 'layerId', LayerTypes::VECTOR, true);
            $wkts = ParamUtil::GetJSON($args, 'wkts');

            $results = array();
            foreach ($wkts as $wkt) {

                if (is_null($wkt))
                    continue;

                $gid = $layer->insertRecord(array(
                    'wkt_geom' => $wkt
                ));

                $results[] = array('featureId' => $gid, 'wkt' => $wkt);
            }
            WAPI::SendSimpleResponse(array('features' => $results));
            break;
        case 'duplicate':
            $user = SimpleSession::Get()->GetUser();
            $layer = System::GetWapi()->RequireLayerId(AccessLevels::EDIT, 'layerId', LayerTypes::VECTOR, true);
            $duplicate = $layer->SaveAs($layer->id, 'Copy of ' . $layer->name, $layer->owner->id, $user->id);
            PageUtil::RedirectTo('?do=layer.editvector1&id=' . $duplicate->id);
            break;
        case 'revert_to_original':

            $user = SimpleSession::Get()->GetUser();
            $layer = System::GetWapi()->RequireLayerId(AccessLevels::EDIT, 'layerId', LayerTypes::VECTOR, true);

            LayerUtil::RevertToOriginal($layer);
            javascriptalert('Revert action complete');
            PageUtil::RedirectTo('?do=layer.editvector1&id=' . $layer->id);
            break;
        case 'replace_original':
            $user = SimpleSession::Get()->GetUser();

            $layer = System::GetWapi()->RequireLayerId(AccessLevels::EDIT, 'layerId', LayerTypes::VECTOR, true);

            LayerUtil::ReplaceOriginal($layer);

            javascriptalert('Replace complete');
            PageUtil::RedirectTo('?do=layer.editvector1&id=' . $layer->id);
            break;
        case 'bookmark_set':
            $layer = System::GetWapi()->RequireLayerId(AccessLevels::EDIT, 'id', LayerTypes::VECTOR, true);
            $user = SimpleSession::Get()->GetUser();
            $user->addLayerBookmarkById($layer->id);

            WAPI::SendSimpleResponse(array('message' => 'layer bookmarked'));
            break;
        case 'bookmark_unset':
            $layer = System::GetWapi()->RequireLayerId(AccessLevels::EDIT, 'id', LayerTypes::VECTOR, true);
            $user = SimpleSession::Get()->GetUser();
            $user->removeLayerBookmarkById($layer->id);
            WAPI::SendSimpleResponse(array('message' => 'layer bookmark unset'));
            break;
    }
}
